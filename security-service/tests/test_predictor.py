"""
Unit test untuk predictor service.

Test ini memverifikasi perilaku fallback dan penggabungan skor
tanpa memerlukan model .pkl yang sebenarnya.
"""

import pytest
from unittest.mock import MagicMock, patch
import numpy as np

from app.schemas.risk_input import RiskInputSchema
from app.services import predictor


def _make_input(**kwargs) -> RiskInputSchema:
    defaults = {
        "user_id":            1,
        "ip_risk_score":      0.1,
        "is_vpn":             0,
        "is_new_device":      0,
        "is_new_country":     0,
        "login_hour":         10,
        "failed_attempts":    0,
        "request_speed":      0.1,
        "device_trust_score": 0.9,
    }
    defaults.update(kwargs)
    return RiskInputSchema(**defaults)


class TestPredictorFallback:
    def test_returns_fallback_true_when_model_not_loaded(self):
        """Jika model tidak dimuat, is_fallback harus True."""
        with patch("app.services.predictor._model_loaded", False):
            result = predictor.predict(_make_input())
        assert result.is_fallback is True

    def test_fallback_uses_rule_based_only(self):
        """Dalam mode fallback, ai_score harus 0.0."""
        with patch("app.services.predictor._model_loaded", False):
            result = predictor.predict(_make_input())
        assert result.ai_score == 0.0

    def test_fallback_contains_ai_fallback_flag(self):
        """Respons fallback harus menyertakan flag 'ai_fallback_active'."""
        with patch("app.services.predictor._model_loaded", False):
            result = predictor.predict(_make_input())
        assert "ai_fallback_active" in result.reason_flags

    def test_fallback_never_auto_allows_high_risk(self):
        """Mode fallback tidak boleh ALLOW untuk sinyal risiko tinggi."""
        with patch("app.services.predictor._model_loaded", False):
            result = predictor.predict(_make_input(
                is_vpn=1,
                is_new_device=1,
                is_new_country=1,
                failed_attempts=5,
                login_hour=3,
                device_trust_score=0.0,
                ip_risk_score=0.9,
            ))
        assert result.decision in ("OTP", "BLOCK")


class TestPredictorWithMockedModel:
    def _make_mock_model(self, raw_score: float):
        """Buat mock model yang mengembalikan skor tertentu."""
        mock = MagicMock()
        mock.score_samples.return_value = np.array([raw_score])
        return mock

    def test_high_anomaly_score_leads_to_block(self):
        """
        Isolation Forest mengembalikan skor sangat negatif → anomali → BLOCK.
        raw_score = -0.7 → ai_score ≈ 70 → final ≈ 70*0.7 + rule*0.3 ≥ 60
        """
        mock_model = self._make_mock_model(-0.7)

        with patch("app.services.predictor._model_loaded", True), \
             patch("app.services.predictor._model_instance", mock_model), \
             patch("app.services.explainability.extract_ai_reasons", return_value=[]):
            result = predictor.predict(_make_input(
                is_new_device=1,
                is_vpn=1,
                login_hour=3,
            ))

        assert result.decision in ("OTP", "BLOCK")
        assert result.is_fallback is False

    def test_normal_score_leads_to_allow(self):
        """
        Isolation Forest mengembalikan skor mendekati 0 → normal → ALLOW.
        raw_score = -0.05 → ai_score ≈ 5 → final rendah → ALLOW
        """
        mock_model = self._make_mock_model(-0.05)

        with patch("app.services.predictor._model_loaded", True), \
             patch("app.services.predictor._model_instance", mock_model), \
             patch("app.services.explainability.extract_ai_reasons", return_value=[]):
            result = predictor.predict(_make_input())  # semua sinyal aman

        assert result.decision == "ALLOW"
        assert result.is_fallback is False

    def test_model_exception_triggers_fallback(self):
        """Jika model melempar exception, harus fallback ke rule-based."""
        mock_model = MagicMock()
        mock_model.score_samples.side_effect = RuntimeError("Model corrupt")

        with patch("app.services.predictor._model_loaded", True), \
             patch("app.services.predictor._model_instance", mock_model):
            result = predictor.predict(_make_input())

        assert result.is_fallback is True
        assert "ai_fallback_active" in result.reason_flags

    def test_response_scores_are_in_valid_range(self):
        """Semua skor dalam respons harus berada dalam rentang 0–100."""
        mock_model = self._make_mock_model(-0.3)

        with patch("app.services.predictor._model_loaded", True), \
             patch("app.services.predictor._model_instance", mock_model), \
             patch("app.services.explainability.extract_ai_reasons", return_value=[]):
            result = predictor.predict(_make_input())

        assert 0.0 <= result.risk_score <= 100.0
        assert 0.0 <= result.ai_score <= 100.0
        assert 0.0 <= result.rule_score <= 100.0


class TestPredictorOutputStructure:
    def test_all_required_fields_present(self):
        with patch("app.services.predictor._model_loaded", False):
            result = predictor.predict(_make_input())

        assert hasattr(result, "risk_score")
        assert hasattr(result, "decision")
        assert hasattr(result, "reason_flags")
        assert hasattr(result, "ai_score")
        assert hasattr(result, "rule_score")
        assert hasattr(result, "is_fallback")

    def test_reason_flags_is_list(self):
        with patch("app.services.predictor._model_loaded", False):
            result = predictor.predict(_make_input())

        assert isinstance(result.reason_flags, list)

    def test_decision_is_valid_string(self):
        with patch("app.services.predictor._model_loaded", False):
            result = predictor.predict(_make_input())

        assert result.decision in ("ALLOW", "OTP", "BLOCK")
