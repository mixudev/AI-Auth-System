"""
Layanan prediksi utama yang menggabungkan model AI dan rule engine.

Skor akhir dihitung sebagai:
  FINAL_RISK = (AI_RISK × 0.7) + (RULE_RISK × 0.3)

Bobot ini dapat dikonfigurasi via environment variable.
Pemisahan antara AI score dan rule score memastikan:
1. Tim keamanan dapat melihat kontribusi masing-masing
2. Jika model AI mengalami drift, rule-based masih memberikan sinyal
3. Keputusan dapat diaudit bahkan tanpa memahami model ML
"""

from __future__ import annotations

import logging
import pickle
import threading
import time
from pathlib import Path

import numpy as np
from sklearn.ensemble import IsolationForest

from app.core.config import get_settings
from app.core.thresholds import RiskDecision, clamp_score, score_to_decision
from app.schemas.risk_input import RiskInputSchema, RiskOutputSchema
from app.services import rule_engine, explainability
from app.utils.normalizer import build_feature_vector

logger = logging.getLogger(__name__)

# Lock untuk melindungi akses model dari race condition saat hot reload
_model_lock = threading.Lock()
_model_instance: IsolationForest | None = None
_model_loaded: bool = False


def load_model() -> bool:
    """
    Muat model Isolation Forest dari disk ke memori.

    Dipanggil saat startup aplikasi. Mengembalikan True jika berhasil.
    Kegagalan loading tidak menghentikan aplikasi — rule-based fallback tetap aktif.
    """
    global _model_instance, _model_loaded

    settings  = get_settings()
    model_path = Path(settings.MODEL_PATH)

    if not model_path.exists():
        logger.warning(
            "File model tidak ditemukan di %s. "
            "Jalankan training/train_model.py terlebih dahulu. "
            "Sistem akan menggunakan rule-based fallback.",
            model_path,
        )
        return False

    try:
        with _model_lock:
            with open(model_path, "rb") as f:
                _model_instance = pickle.load(f)
            _model_loaded = True

        logger.info("Model Isolation Forest berhasil dimuat dari %s", model_path)
        return True

    except Exception as exc:
        logger.error("Gagal memuat model: %s", exc, exc_info=True)
        _model_loaded = False
        return False


def is_model_loaded() -> bool:
    return _model_loaded


def predict(data: RiskInputSchema) -> RiskOutputSchema:
    """
    Evaluasi risiko login menggunakan model AI + rule engine.

    Alur:
    1. Selalu jalankan rule engine (tidak bisa gagal secara fatal)
    2. Coba jalankan model AI dengan timeout
    3. Gabungkan kedua skor dengan bobot yang dikonfigurasi
    4. Tentukan keputusan berdasarkan skor akhir
    5. Kumpulkan reason flags dari kedua sumber
    """
    settings = get_settings()

    # -- Step 1: Evaluasi rule-based (selalu berjalan)
    rule_result = rule_engine.evaluate(data)

    # -- Step 2: Evaluasi model AI (dapat gagal)
    ai_score, ai_reasons, is_fallback = _run_ai_prediction(data, settings)

    # -- Step 3: Hitung skor akhir
    if is_fallback:
        # Jika AI gagal, gunakan 100% rule-based dengan bobot 1.0
        # untuk memastikan keputusan tetap konservatif
        final_score = clamp_score(rule_result.score)
        effective_ai_weight   = 0.0
        effective_rule_weight = 1.0
    else:
        final_score = (ai_score * settings.AI_RISK_WEIGHT) + (rule_result.score * settings.RULE_RISK_WEIGHT)

        # -- Step 4: Aplikasi Trust Override Permanen
        # Jika perangkat terpercaya (skor bonus negatif sudah dihitung di rule_engine),
        # kita pastikan total skor tidak memicu pemblokiran jika tidak ada ancaman nyata.
        if data.device_trust_score >= 1.0:
            # Jika hanya AI yang curiga (karena "anomaly shock"), kita paksa skor ke batas aman
            # tapi hanya jika rule engine tidak mendeteksi ancaman kritis lain (skor aturan negatif)
            if rule_result.score <= 0:
                final_score = min(final_score, settings.RISK_THRESHOLD_ALLOW - 1)

        final_score = clamp_score(final_score)

    # -- Step 5: Tentukan keputusan
    decision: RiskDecision = score_to_decision(final_score)

    # -- Step 5: Gabungkan reason flags dari AI dan rule engine
    all_reasons = list(set(ai_reasons + rule_result.reason_flags))

    # Tandai jika ini adalah hasil fallback
    if is_fallback:
        all_reasons.append("ai_fallback_active")

    logger.info(
        "Risk assessment selesai | user_id=%d | ai_score=%.1f | rule_score=%.1f "
        "| final_score=%.1f | decision=%s | fallback=%s",
        data.user_id,
        ai_score,
        rule_result.score,
        final_score,
        decision.value,
        is_fallback,
    )

    return RiskOutputSchema(
        risk_score=round(final_score, 2),
        decision=decision.value,
        reason_flags=sorted(all_reasons),
        ai_score=round(ai_score, 2),
        rule_score=round(rule_result.score, 2),
        is_fallback=is_fallback,
    )


def _run_ai_prediction(
    data: RiskInputSchema,
    settings,
) -> tuple[float, list[str], bool]:
    """
    Jalankan prediksi model Isolation Forest dengan timeout dan isolasi error.

    Mengembalikan tuple: (ai_score, ai_reasons, is_fallback)

    is_fallback = True jika:
    - Model belum dimuat
    - Terjadi exception saat prediksi
    - Prediksi melebihi timeout
    """
    if not _model_loaded or _model_instance is None:
        logger.warning("Model tidak tersedia, menggunakan fallback penuh.")
        return 0.0, [], True

    try:
        feature_vector = build_feature_vector(data)

        # Jalankan prediksi dengan batas waktu menggunakan thread
        result_container: list[float] = []
        error_container:  list[Exception] = []

        def _predict_worker() -> None:
            try:
                raw_score = _model_instance.score_samples(feature_vector)[0]
                result_container.append(raw_score)
            except Exception as exc:
                error_container.append(exc)

        worker = threading.Thread(target=_predict_worker, daemon=True)
        worker.start()
        worker.join(timeout=settings.INFERENCE_TIMEOUT_SECONDS)

        if worker.is_alive():
            # Prediksi melebihi timeout — gunakan fallback
            logger.warning(
                "Inference timeout (>%.1fs) untuk user_id=%d",
                settings.INFERENCE_TIMEOUT_SECONDS,
                data.user_id,
            )
            return 0.0, [], True

        if error_container:
            raise error_container[0]

        if not result_container:
            return 0.0, [], True

        raw_score = result_container[0]
        ai_score  = _isolation_score_to_risk(raw_score)

        # Ekstrak alasan dari model menggunakan perturbasi fitur
        ai_reasons = explainability.extract_ai_reasons(
            model=_model_instance,
            data=data,
            base_score=ai_score,
        )

        return ai_score, ai_reasons, False

    except Exception as exc:
        logger.error(
            "Prediksi model gagal untuk user_id=%d: %s",
            data.user_id,
            exc,
            exc_info=True,
        )
        return 0.0, [], True


def _isolation_score_to_risk(raw_score: float) -> float:
    """
    Konversi raw anomaly score Isolation Forest ke skala 0–100.

    score_samples() mengembalikan nilai negatif:
    - Mendekati 0    → normal
    - Mendekati -0.5 → anomali ringan
    - Di bawah -0.5  → anomali kuat

    Transformasi linear sederhana cukup untuk use case ini.
    """
    risk = (-raw_score) * 100.0
    return clamp_score(risk)
