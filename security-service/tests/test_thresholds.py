"""
Unit test untuk logika konversi skor ke keputusan.
"""

import pytest
from unittest.mock import patch

from app.core.thresholds import score_to_decision, clamp_score, RiskDecision


class TestScoreToDecision:
    def test_low_score_is_allow(self):
        assert score_to_decision(0.0)  == RiskDecision.ALLOW
        assert score_to_decision(10.0) == RiskDecision.ALLOW
        assert score_to_decision(29.9) == RiskDecision.ALLOW

    def test_medium_score_is_otp(self):
        assert score_to_decision(30.0) == RiskDecision.OTP
        assert score_to_decision(45.0) == RiskDecision.OTP
        assert score_to_decision(59.9) == RiskDecision.OTP

    def test_high_score_is_block(self):
        assert score_to_decision(60.0)  == RiskDecision.BLOCK
        assert score_to_decision(80.0)  == RiskDecision.BLOCK
        assert score_to_decision(100.0) == RiskDecision.BLOCK

    def test_boundary_allow_to_otp(self):
        # Batas tepat di 30 harus menjadi OTP, bukan ALLOW
        assert score_to_decision(30.0) == RiskDecision.OTP

    def test_boundary_otp_to_block(self):
        # Batas tepat di 60 harus menjadi BLOCK, bukan OTP
        assert score_to_decision(60.0) == RiskDecision.BLOCK


class TestClampScore:
    def test_normal_value_unchanged(self):
        assert clamp_score(50.0) == 50.0

    def test_below_zero_clamped(self):
        assert clamp_score(-10.0) == 0.0

    def test_above_hundred_clamped(self):
        assert clamp_score(110.0) == 100.0

    def test_exactly_zero(self):
        assert clamp_score(0.0) == 0.0

    def test_exactly_hundred(self):
        assert clamp_score(100.0) == 100.0
