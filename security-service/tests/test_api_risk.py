"""
Integration test untuk endpoint POST /api/v1/risk-score.

Menggunakan TestClient FastAPI agar tidak memerlukan server yang berjalan.
Model AI di-mock agar test tidak bergantung pada file .pkl.
"""

import pytest
from unittest.mock import patch, MagicMock
from fastapi.testclient import TestClient

from app.main import app
from app.core.config import get_settings
from app.schemas.risk_input import RiskOutputSchema


VALID_PAYLOAD = {
    "user_id":           42,
    "ip_risk_score":     0.1,
    "is_vpn":            0,
    "is_new_device":     0,
    "is_new_country":    0,
    "login_hour":        10,
    "failed_attempts":   0,
    "request_speed":     0.1,
    "device_trust_score": 0.9,
}

VALID_HEADERS = {"X-API-Key": get_settings().API_KEY}


@pytest.fixture
def client():
    with TestClient(app) as c:
        yield c


class TestAuthentication:
    def test_missing_api_key_returns_401(self, client):
        response = client.post("/api/v1/risk-score", json=VALID_PAYLOAD)
        assert response.status_code == 401

    def test_wrong_api_key_returns_403(self, client):
        response = client.post(
            "/api/v1/risk-score",
            json=VALID_PAYLOAD,
            headers={"X-API-Key": "wrong-key"},
        )
        assert response.status_code == 403

    def test_valid_api_key_is_accepted(self, client):
        response = client.post(
            "/api/v1/risk-score",
            json=VALID_PAYLOAD,
            headers=VALID_HEADERS,
        )
        # Apapun hasilnya, tidak boleh 401 atau 403
        assert response.status_code not in (401, 403)


class TestInputValidation:
    def test_missing_required_field_returns_422(self, client):
        payload = {**VALID_PAYLOAD}
        del payload["user_id"]
        response = client.post("/api/v1/risk-score", json=payload, headers=VALID_HEADERS)
        assert response.status_code == 422

    def test_out_of_range_ip_risk_score_returns_422(self, client):
        payload = {**VALID_PAYLOAD, "ip_risk_score": 1.5}  # Melebihi 1.0
        response = client.post("/api/v1/risk-score", json=payload, headers=VALID_HEADERS)
        assert response.status_code == 422

    def test_invalid_login_hour_returns_422(self, client):
        payload = {**VALID_PAYLOAD, "login_hour": 25}  # Melebihi 23
        response = client.post("/api/v1/risk-score", json=payload, headers=VALID_HEADERS)
        assert response.status_code == 422


class TestRiskScoreResponse:
    def test_response_contains_required_fields(self, client):
        response = client.post("/api/v1/risk-score", json=VALID_PAYLOAD, headers=VALID_HEADERS)
        assert response.status_code == 200
        data = response.json()
        for field in ("risk_score", "decision", "reason_flags", "ai_score", "rule_score", "is_fallback"):
            assert field in data, f"Field '{field}' tidak ada dalam respons"

    def test_decision_is_valid_value(self, client):
        response = client.post("/api/v1/risk-score", json=VALID_PAYLOAD, headers=VALID_HEADERS)
        assert response.status_code == 200
        assert response.json()["decision"] in ("ALLOW", "OTP", "BLOCK")

    def test_risk_score_in_valid_range(self, client):
        response = client.post("/api/v1/risk-score", json=VALID_PAYLOAD, headers=VALID_HEADERS)
        assert response.status_code == 200
        score = response.json()["risk_score"]
        assert 0.0 <= score <= 100.0

    def test_high_risk_payload_returns_block_or_otp(self, client):
        high_risk_payload = {
            **VALID_PAYLOAD,
            "ip_risk_score":     0.9,
            "is_vpn":            1,
            "is_new_device":     1,
            "is_new_country":    1,
            "failed_attempts":   5,
            "login_hour":        3,
            "device_trust_score": 0.0,
        }
        response = client.post("/api/v1/risk-score", json=high_risk_payload, headers=VALID_HEADERS)
        assert response.status_code == 200
        assert response.json()["decision"] in ("OTP", "BLOCK")

    def test_fallback_mode_when_model_not_loaded(self, client):
        with patch("app.services.predictor._model_loaded", False):
            response = client.post("/api/v1/risk-score", json=VALID_PAYLOAD, headers=VALID_HEADERS)
        assert response.status_code == 200
        assert response.json()["is_fallback"] is True


class TestHealthEndpoint:
    def test_health_returns_200(self, client):
        response = client.get("/health")
        assert response.status_code == 200

    def test_health_does_not_require_api_key(self, client):
        response = client.get("/health")
        assert response.status_code == 200

    def test_health_response_schema(self, client):
        data = client.get("/health").json()
        assert "status" in data
        assert "model_loaded" in data
        assert "version" in data
        assert data["status"] == "ok"
