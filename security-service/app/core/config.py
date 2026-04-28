"""
Konfigurasi terpusat untuk seluruh aplikasi.

Semua nilai sensitif dibaca dari environment variables.
Tidak ada hardcoded secret di source code.
"""

from functools import lru_cache
from pathlib import Path
from pydantic_settings import BaseSettings, SettingsConfigDict


BASE_DIR = Path(__file__).resolve().parent.parent.parent


class Settings(BaseSettings):
    model_config = SettingsConfigDict(
        env_file=BASE_DIR / ".env",
        env_file_encoding="utf-8",
        case_sensitive=False,
    )

    # ------------------------------------------------------------------
    # Identitas aplikasi
    # ------------------------------------------------------------------
    APP_NAME: str = "AI Risk Detection Service"
    APP_VERSION: str = "1.0.0"
    APP_ENV: str = "production"  # production | development

    # ------------------------------------------------------------------
    # Autentikasi API
    # Nilai ini harus disetel via environment variable di deployment nyata.
    # ------------------------------------------------------------------
    API_KEY: str

    # ------------------------------------------------------------------
    # Threshold keputusan risiko — dapat disesuaikan tanpa mengubah kode
    # ------------------------------------------------------------------
    RISK_THRESHOLD_ALLOW: int = 30   # 0–29  → ALLOW
    RISK_THRESHOLD_OTP: int = 60     # 30–59 → OTP
                                     # 60–100 → BLOCK

    # ------------------------------------------------------------------
    # Bobot hybrid: AI vs rule-based
    # Jumlah keduanya harus selalu = 1.0
    # ------------------------------------------------------------------
    AI_RISK_WEIGHT: float = 0.7
    RULE_RISK_WEIGHT: float = 0.3

    # ------------------------------------------------------------------
    # Path model Isolation Forest yang telah dilatih
    # ------------------------------------------------------------------
    MODEL_PATH: str = str(BASE_DIR / "app" / "models" / "isolation_forest.pkl")

    # ------------------------------------------------------------------
    # Rate limiting
    # ------------------------------------------------------------------
    RATE_LIMIT_REQUESTS: int = 60   # Maksimum request per window
    RATE_LIMIT_WINDOW: int = 60     # Window dalam detik

    # ------------------------------------------------------------------
    # Timeout inferensi model dalam detik
    # Melindungi sistem dari prediksi yang berjalan terlalu lama
    # ------------------------------------------------------------------
    INFERENCE_TIMEOUT_SECONDS: float = 2.0

    # ------------------------------------------------------------------
    # Logging
    # ------------------------------------------------------------------
    LOG_LEVEL: str = "INFO"
    LOG_DIR: str = str(BASE_DIR / "logs")

    # ------------------------------------------------------------------
    # Kontrol apakah detail error teknis dikembalikan ke klien
    # HARUS False di production
    # ------------------------------------------------------------------
    DEBUG_RESPONSES: bool = False


@lru_cache(maxsize=1)
def get_settings() -> Settings:
    """
    Kembalikan instance Settings sebagai singleton.

    lru_cache memastikan file .env hanya dibaca sekali selama
    aplikasi berjalan, bukan setiap kali ada request.
    """
    return Settings()
