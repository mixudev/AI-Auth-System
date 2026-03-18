@extends('layouts.app')

@section('title', 'Login')

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

    /* Animated grid background */
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

    /* Glow orb */
    .auth-wrap::after {
        content: '';
        position: absolute;
        width: 600px; height: 600px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(0,212,170,0.06) 0%, transparent 70%);
        top: 50%; left: 50%;
        transform: translate(-50%, -50%);
        pointer-events: none;
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
    .auth-logo {
        width: 48px; height: 48px;
        background: var(--accent);
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-family: var(--mono);
        font-size: 18px;
        font-weight: 700;
        color: var(--bg);
        margin: 0 auto 16px;
        box-shadow: 0 0 30px rgba(0,212,170,0.3);
    }
    .auth-title {
        font-family: var(--mono);
        font-size: 20px;
        font-weight: 700;
        color: var(--text);
        letter-spacing: -0.02em;
    }
    .auth-subtitle {
        font-size: 13px;
        color: var(--text3);
        margin-top: 6px;
    }

    .form-group { margin-bottom: 18px; }
    .form-label {
        display: block;
        font-size: 11px;
        font-family: var(--mono);
        color: var(--text2);
        letter-spacing: 0.05em;
        text-transform: uppercase;
        margin-bottom: 8px;
    }
    .form-input {
        width: 100%;
        background: var(--bg);
        border: 1px solid var(--border);
        border-radius: 6px;
        padding: 10px 14px;
        font-size: 14px;
        font-family: var(--sans);
        color: var(--text);
        transition: border-color 0.2s, box-shadow 0.2s;
        outline: none;
    }
    .form-input:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(0,212,170,0.1);
    }
    .form-input::placeholder { color: var(--text3); }
    .form-input.is-error { border-color: var(--danger); }

    .field-error {
        font-size: 12px;
        color: var(--danger);
        margin-top: 6px;
        font-family: var(--mono);
    }

    .btn-primary {
        width: 100%;
        padding: 11px;
        background: var(--accent);
        border: none;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        font-family: var(--sans);
        color: var(--bg);
        cursor: pointer;
        transition: background 0.2s, transform 0.1s, box-shadow 0.2s;
        margin-top: 8px;
        letter-spacing: 0.01em;
    }
    .btn-primary:hover {
        background: var(--accent2);
        box-shadow: 0 4px 20px rgba(0,212,170,0.3);
    }
    .btn-primary:active { transform: scale(0.99); }

    .ai-badge {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        margin-top: 24px;
        font-size: 11px;
        font-family: var(--mono);
        color: var(--text3);
    }
    .ai-badge span {
        color: var(--accent);
    }
</style>
@endpush

@section('content')
<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">SA</div>
            <div class="auth-title">SECURE<span style="color:var(--accent)">AUTH</span></div>
            <div class="auth-subtitle">Masuk dengan perlindungan AI</div>
        </div>

        @if(session('info'))
            <div class="alert alert-info">{{ session('info') }}</div>
        @endif

        @if($errors->has('email') && !$errors->has('otp_code'))
            <div class="alert alert-error">{{ $errors->first('email') }}</div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Email</label>
                <input
                    type="email"
                    name="email"
                    class="form-input {{ $errors->has('email') ? 'is-error' : '' }}"
                    value="{{ old('email') }}"
                    placeholder="nama@domain.com"
                    autofocus
                    required
                >
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <input
                    type="password"
                    name="password"
                    class="form-input {{ $errors->has('password') ? 'is-error' : '' }}"
                    placeholder="••••••••"
                    required
                >
                @error('password')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn-primary">Masuk</button>
        </form>

        <div class="ai-badge">
            <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            Dilindungi oleh <span>AI Risk Engine</span>
        </div>
    </div>
</div>
@endsection
