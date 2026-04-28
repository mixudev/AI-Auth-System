"""
Unit test untuk rule engine.

Rule engine adalah komponen yang paling kritis untuk diuji karena:
1. Berfungsi sebagai fallback jika AI gagal
2. Berkontribusi 30% dari skor akhir
3. Menghasilkan reason flags yang terlihat oleh tim keamanan
"""

import pytest

from app.schemas.risk_input import RiskInputSchema
from app.services.rule_engine import evaluate


def _make_input(**kwargs) -> RiskInputSchema:
    """Buat input dengan nilai default aman, override dengan kwargs."""
    defaults = {
        "user_id":           1,
        "ip_risk_score":     0.0,
        "is_vpn":            0,
        "is_new_device":     0,
        "is_new_country":    0,
        "login_hour":        10,
        "failed_attempts":   0,
        "request_speed":     0.1,
        "device_trust_score": 0.9,
    }
    defaults.update(kwargs)
    return RiskInputSchema(**defaults)


class TestRuleEngineVpn:
    def test_vpn_triggers_flag(self):
        result = evaluate(_make_input(is_vpn=1))
        assert "vpn_usage" in result.reason_flags

    def test_no_vpn_no_flag(self):
        result = evaluate(_make_input(is_vpn=0))
        assert "vpn_usage" not in result.reason_flags

    def test_vpn_contributes_to_score(self):
        with_vpn    = evaluate(_make_input(is_vpn=1))
        without_vpn = evaluate(_make_input(is_vpn=0))
        assert with_vpn.score > without_vpn.score


class TestRuleEngineNewDevice:
    def test_new_device_triggers_flag(self):
        result = evaluate(_make_input(is_new_device=1))
        assert "new_device_detected" in result.reason_flags

    def test_known_device_no_flag(self):
        result = evaluate(_make_input(is_new_device=0))
        assert "new_device_detected" not in result.reason_flags


class TestRuleEngineFailedAttempts:
    def test_zero_attempts_no_flag(self):
        result = evaluate(_make_input(failed_attempts=0))
        assert not any("failed_attempts" in f for f in result.reason_flags)

    def test_multiple_attempts_trigger_flag(self):
        result = evaluate(_make_input(failed_attempts=3))
        assert any("failed_attempts" in f for f in result.reason_flags)

    def test_score_increases_with_attempts(self):
        low  = evaluate(_make_input(failed_attempts=1))
        high = evaluate(_make_input(failed_attempts=5))
        assert high.score > low.score


class TestRuleEngineOffHours:
    def test_normal_hour_no_flag(self):
        result = evaluate(_make_input(login_hour=10))
        assert "abnormal_login_hour" not in result.reason_flags

    def test_midnight_triggers_flag(self):
        result = evaluate(_make_input(login_hour=2))
        assert "abnormal_login_hour" in result.reason_flags

    def test_late_night_triggers_flag(self):
        result = evaluate(_make_input(login_hour=23))
        assert "abnormal_login_hour" in result.reason_flags


class TestRuleEngineScoreBounds:
    def test_all_safe_signals_low_score(self):
        result = evaluate(_make_input())
        assert result.score < 30.0

    def test_all_risky_signals_high_score(self):
        result = evaluate(_make_input(
            ip_risk_score=0.9,
            is_vpn=1,
            is_new_device=1,
            is_new_country=1,
            login_hour=3,
            failed_attempts=5,
            request_speed=0.9,
            device_trust_score=0.1,
        ))
        assert result.score >= 60.0

    def test_score_never_exceeds_100(self):
        result = evaluate(_make_input(
            ip_risk_score=1.0,
            is_vpn=1,
            is_new_device=1,
            is_new_country=1,
            failed_attempts=10,
            request_speed=1.0,
            device_trust_score=0.0,
            login_hour=3,
        ))
        assert result.score <= 100.0
