@extends('layouts.app')

@section('title', 'Verifikasi OTP')

@push('styles')
<style>
    body { background: var(--bg); }
    .auth-wrap {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }
    .auth-wrap::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image:
            linear-gradient(var(--border) 1px, transparent 1px),
            linear-gradient(90deg, var(--border) 1px, transparent 1px);
        background-size: 40px 40px;
        opacity: 0.3;
        mask-image: radial-gradient(ellipse 80% 80% at 50% 50%, black 40%, transparent 100%);
    }
    .auth-card {
        width: 100%;
        max-width: 420px;
        background: var(--bg2);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 40px;
        position: relative;
        z-index: 1;
        animation: fadeUp 0.4s ease;
    }
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .auth-header { text-align: center; margin-bottom: 32px; }
    .otp-icon {
        width: 56px; height: 56px;
        background: rgba(227,179,65,0.1);
        border: 1px solid rgba(227,179,65,0.3);
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 16px;
        font-size: 24px;
    }
    .auth-title {
        font-family: var(--mono);
        font-size: 18px;
        font-weight: 700;
        color: var(--text);
    }
    .auth-subtitle { font-size: 13px; color: var(--text3); margin-top: 6px; }
    .auth-email { color: var(--warn); font-family: var(--mono); font-size: 12px; }

    .otp-inputs {
        display: flex;
        gap: 10px;
        justify-content: center;
        margin: 24px 0;
    }
    .otp-digit {
        width: 50px; height: 56px;
        background: var(--bg);
        border: 1px solid var(--border);
        border-radius: 8px;
        text-align: center;
        font-size: 20px;
        font-family: var(--mono);
        font-weight: 700;
        color: var(--text);
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
        caret-color: var(--accent);
    }
    .otp-digit:focus {
        border-color: var(--warn);
        box-shadow: 0 0 0 3px rgba(227,179,65,0.1);
    }
    /* Hidden real input */
    .otp-hidden { position: absolute; opacity: 0; pointer-events: none; }

    .field-error {
        font-size: 12px;
        color: var(--danger);
        text-align: center;
        font-family: var(--mono);
        margin-bottom: 16px;
    }

    .btn-primary {
        width: 100%;
        padding: 11px;
        background: var(--warn);
        border: none;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        font-family: var(--sans);
        color: var(--bg);
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-primary:hover { filter: brightness(1.1); box-shadow: 0 4px 20px rgba(227,179,65,0.3); }

    .back-link {
        display: block;
        text-align: center;
        margin-top: 20px;
        font-size: 12px;
        color: var(--text3);
        font-family: var(--mono);
    }
    .back-link a { color: var(--text2); }
    .back-link a:hover { color: var(--text); }

    .timer {
        text-align: center;
        font-size: 12px;
        font-family: var(--mono);
        color: var(--text3);
        margin-top: 16px;
    }
    .timer span { color: var(--warn); }
</style>
@endpush

@section('content')
<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-header">
            <div class="otp-icon">🔐</div>
            <div class="auth-title">Verifikasi OTP</div>
            <div class="auth-subtitle">
                Kode dikirim ke<br>
                <span class="auth-email">{{ session('otp_email', 'email Anda') }}</span>
            </div>
        </div>

        @error('otp_code')
            <div class="alert alert-error" style="margin-bottom:16px">{{ $message }}</div>
        @enderror

        <form method="POST" action="{{ route('otp.verify.post') }}" id="otpForm">
            @csrf
            <input type="hidden" name="otp_code" id="otpHidden">

            <div class="otp-inputs">
                @for($i = 0; $i < 6; $i++)
                    <input
                        type="text"
                        class="otp-digit"
                        maxlength="1"
                        pattern="[0-9]"
                        inputmode="numeric"
                        data-index="{{ $i }}"
                        autocomplete="off"
                    >
                @endfor
            </div>

            <button type="submit" class="btn-primary" id="submitBtn" disabled>Verifikasi</button>
        </form>

        <div class="timer">
            Kode berlaku selama <span>{{ session('otp_expires_in', '5 menit') }}</span>
        </div>

        <div class="back-link">
            <a href="{{ route('login') }}">← Kembali ke halaman login</a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const digits = document.querySelectorAll('.otp-digit');
    const hidden = document.getElementById('otpHidden');
    const submitBtn = document.getElementById('submitBtn');

    function updateHidden() {
        const val = Array.from(digits).map(d => d.value).join('');
        hidden.value = val;
        submitBtn.disabled = val.length < 6;
    }

    digits.forEach((input, i) => {
        input.addEventListener('input', e => {
            const val = e.target.value.replace(/\D/g, '');
            e.target.value = val.slice(-1);
            if (val && i < 5) digits[i + 1].focus();
            updateHidden();
        });

        input.addEventListener('keydown', e => {
            if (e.key === 'Backspace' && !e.target.value && i > 0) {
                digits[i - 1].focus();
                digits[i - 1].value = '';
                updateHidden();
            }
        });

        // Handle paste
        input.addEventListener('paste', e => {
            e.preventDefault();
            const text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
            text.split('').slice(0, 6).forEach((char, j) => {
                if (digits[j]) digits[j].value = char;
            });
            digits[Math.min(text.length, 5)].focus();
            updateHidden();
        });
    });

    digits[0].focus();
</script>
@endpush
