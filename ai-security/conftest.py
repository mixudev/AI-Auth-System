"""
Konfigurasi pytest global.

Mengganti get_settings() dengan nilai test agar:
1. Test tidak bergantung pada file .env
2. API_KEY selalu diketahui sehingga header autentikasi dapat diisi
3. Threshold konsisten di semua test
"""

import pytest
from unittest.mock import patch

from app.core.config import Settings, get_settings


# Settings yang digunakan selama testing
_TEST_SETTINGS = Settings(
    API_KEY="test-api-key-for-pytest-only",
    APP_ENV="testing",
    RISK_THRESHOLD_ALLOW=30,
    RISK_THRESHOLD_OTP=60,
    AI_RISK_WEIGHT=0.7,
    RULE_RISK_WEIGHT=0.3,
    INFERENCE_TIMEOUT_SECONDS=2.0,
    LOG_LEVEL="WARNING",
    DEBUG_RESPONSES=False,
)


@pytest.fixture(autouse=True)
def override_settings():
    """
    Override get_settings() untuk seluruh test suite.

    autouse=True memastikan fixture ini diaktifkan di setiap test
    tanpa perlu dideklarasikan secara eksplisit.
    """
    with patch("app.core.config.get_settings", return_value=_TEST_SETTINGS):
        # Juga override di modul-modul yang sudah mengimport settings
        with patch("app.core.security.get_settings", return_value=_TEST_SETTINGS):
            with patch("app.core.thresholds.get_settings", return_value=_TEST_SETTINGS):
                with patch("app.services.predictor.get_settings", return_value=_TEST_SETTINGS):
                    yield
