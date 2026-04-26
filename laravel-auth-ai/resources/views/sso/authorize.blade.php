@extends('layouts.auth')

@section('title', 'SSO Consent')

@section('sidebar_headline', 'Single Sign-On.')
@section('sidebar_sub', 'Aplikasi pihak ketiga meminta akses ke profil Anda.')

@section('auth_title', 'Izinkan Akses')
@section('auth_subtitle', "Aplikasi {$client->name} meminta izin.")

@section('auth_content')
<div style="text-align: center; margin-bottom: 20px;">
    <p style="font-size: 14px; color: var(--auth-text);">
        Aplikasi <strong>{{ $client->name }}</strong> ingin mengakses:
    </p>
    <ul style="list-style: none; padding: 0; margin-top: 15px; text-align: left; background: var(--auth-bg); padding: 15px; border-radius: 8px; border: 1px solid var(--auth-border);">
        <li style="margin-bottom: 8px; font-size: 13px; display: flex; align-items: center; gap: 8px;">
            <svg style="width: 16px; height: 16px; color: #10B981;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            Membaca Profil Dasar (Nama, Email)
        </li>
        <li style="margin-bottom: 8px; font-size: 13px; display: flex; align-items: center; gap: 8px;">
            <svg style="width: 16px; height: 16px; color: #10B981;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            Mengetahui Role & Hak Akses Anda
        </li>
        <li style="font-size: 13px; display: flex; align-items: center; gap: 8px;">
            <svg style="width: 16px; height: 16px; color: #10B981;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            Mengetahui Area Akses (SSO) Anda
        </li>
    </ul>
</div>

<div style="display: flex; gap: 10px; margin-top: 25px;">
    <!-- Form Approve -->
    <form method="POST" action="{{ route('passport.authorizations.approve') }}" style="flex: 1;">
        @csrf
        <input type="hidden" name="auth_token" value="{{ $authToken }}">
        <input type="hidden" name="state" value="{{ $request->state }}">
        <input type="hidden" name="client_id" value="{{ $client->id }}">
        <button type="submit" class="btn-primary" style="width: 100%;">Izinkan Akses</button>
    </form>

    <!-- Form Deny -->
    <form method="POST" action="{{ route('passport.authorizations.deny') }}" style="flex: 1;">
        @csrf
        @method('DELETE')
        <input type="hidden" name="state" value="{{ $request->state }}">
        <input type="hidden" name="client_id" value="{{ $client->id }}">
        <input type="hidden" name="redirect_uri" value="{{ $request->redirect_uri }}">
        <button type="submit" class="btn-primary" style="width: 100%; background: transparent; border: 1px solid var(--auth-border); color: var(--auth-text);">Batalkan</button>
    </form>
</div>
@endsection
