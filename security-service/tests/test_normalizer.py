"""
Unit test untuk modul normalisasi fitur.

Transformasi di normalizer.py HARUS identik dengan feature_engineering.py.
Test ini memverifikasi bahwa output vektor fitur sesuai dengan yang diharapkan.
"""

import math
import numpy as np
import pytest

from app.schemas.risk_input import RiskInputSchema
from app.utils.normalizer import (
    build_feature_vector,
    get_feature_names,
    normalize_failed_attempts,
    normalize_login_hour,
)


def _make_input(**kwargs) -> RiskInputSchema:
    defaults = {
        "user_id":            1,
        "ip_risk_score":      0.2,
        "is_vpn":             0,
        "is_new_device":      1,
        "is_new_country":     0,
        "login_hour":         12,
        "failed_attempts":    2,
        "request_speed":      0.3,
        "device_trust_score": 0.7,
    }
    defaults.update(kwargs)
    return RiskInputSchema(**defaults)


class TestNormalizeLoginHour:
    def test_midnight_returns_zero_sin(self):
        sin_val, cos_val = normalize_login_hour(0)
        assert abs(sin_val) < 1e-9        # sin(0) = 0
        assert abs(cos_val - 1.0) < 1e-9  # cos(0) = 1

    def test_noon_returns_negative_cos(self):
        sin_val, cos_val = normalize_login_hour(12)
        # sin(π) ≈ 0, cos(π) = -1
        assert abs(sin_val) < 1e-9
        assert abs(cos_val + 1.0) < 1e-9

    def test_hour_6_returns_positive_sin(self):
        sin_val, cos_val = normalize_login_hour(6)
        # sin(π/2) = 1, cos(π/2) ≈ 0
        assert abs(sin_val - 1.0) < 1e-9
        assert abs(cos_val) < 1e-9

    def test_all_hours_produce_unit_circle_values(self):
        for hour in range(24):
            s, c = normalize_login_hour(hour)
            magnitude = math.sqrt(s**2 + c**2)
            assert abs(magnitude - 1.0) < 1e-9, f"Jam {hour} menghasilkan magnitude != 1"


class TestNormalizeFailedAttempts:
    def test_zero_attempts(self):
        assert normalize_failed_attempts(0) == 0.0

    def test_scale_point(self):
        # 5 percobaan (FAILED_ATTEMPTS_SCALE) harus menghasilkan 1.0
        assert normalize_failed_attempts(5) == 1.0

    def test_above_scale_capped_at_one(self):
        assert normalize_failed_attempts(10) == 1.0
        assert normalize_failed_attempts(100) == 1.0

    def test_partial_attempts(self):
        # 2 percobaan dari skala 5 = 0.4
        assert abs(normalize_failed_attempts(2) - 0.4) < 1e-9


class TestBuildFeatureVector:
    def test_output_shape_is_correct(self):
        data   = _make_input()
        vector = build_feature_vector(data)
        assert vector.shape == (1, len(get_feature_names()))

    def test_output_dtype_is_float64(self):
        data   = _make_input()
        vector = build_feature_vector(data)
        assert vector.dtype == np.float64

    def test_ip_risk_score_at_index_0(self):
        data   = _make_input(ip_risk_score=0.42)
        vector = build_feature_vector(data)
        assert abs(vector[0, 0] - 0.42) < 1e-9

    def test_is_vpn_at_index_1(self):
        data   = _make_input(is_vpn=1)
        vector = build_feature_vector(data)
        assert vector[0, 1] == 1.0

    def test_all_values_in_expected_range(self):
        # Semua nilai dalam vektor harus berada dalam rentang [-1, 1]
        # (nilai sin/cos dapat negatif)
        data   = _make_input()
        vector = build_feature_vector(data)
        assert np.all(vector >= -1.0), "Ada nilai di bawah -1"
        assert np.all(vector <= 1.0),  "Ada nilai di atas 1"

    def test_feature_count_matches_names(self):
        data  = _make_input()
        names = get_feature_names()
        vec   = build_feature_vector(data)
        assert vec.shape[1] == len(names)
