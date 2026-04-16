@extends('layouts.app')

@section('title', '403 — Akses Ditolak')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,600;1,400;1,600&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<style>
    @keyframes float {
        0%, 100% { transform: translateY(0px) rotate(-3deg); }
        50%       { transform: translateY(-12px) rotate(3deg); }
    }
    @keyframes walk {
        0%   { transform: translateX(0) scaleX(1); }
        45%  { transform: translateX(60px) scaleX(1); }
        50%  { transform: translateX(60px) scaleX(-1); }
        95%  { transform: translateX(0) scaleX(-1); }
        100% { transform: translateX(0) scaleX(1); }
    }
    @keyframes blink {
        0%, 90%, 100% { transform: scaleY(1); }
        95%            { transform: scaleY(0.1); }
    }
    @keyframes shake {
        0%, 100% { transform: rotate(0deg); }
        20%       { transform: rotate(-8deg); }
        40%       { transform: rotate(8deg); }
        60%       { transform: rotate(-5deg); }
        80%       { transform: rotate(5deg); }
    }
    @keyframes fadeSlideUp {
        from { opacity: 0; transform: translateY(24px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes signSwing {
        0%, 100% { transform: rotate(-4deg); }
        50%       { transform: rotate(4deg); }
    }
    .float-anim    { animation: float 3.5s ease-in-out infinite; }
    .walk-anim     { animation: walk 4s ease-in-out infinite; }
    .blink-anim    { animation: blink 3s ease-in-out infinite; }
    .shake-anim    { animation: shake 0.6s ease-in-out; }
    .fade-up       { animation: fadeSlideUp 0.6s ease both; }
    .fade-up-1     { animation: fadeSlideUp 0.6s 0.1s ease both; }
    .fade-up-2     { animation: fadeSlideUp 0.6s 0.2s ease both; }
    .fade-up-3     { animation: fadeSlideUp 0.6s 0.35s ease both; }
    .sign-swing    { animation: signSwing 2.5s ease-in-out infinite; transform-origin: top center; }
</style>
@endpush

@section('content')

<div class="min-h-screen bg-stone-50 flex items-center justify-center px-4 py-12 relative overflow-hidden">

    {{-- Blobs --}}
    <div class="absolute -top-32 -right-32 w-96 h-96 bg-red-100 rounded-full opacity-40 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -left-24 w-80 h-80 bg-stone-200 rounded-full opacity-30 blur-3xl pointer-events-none"></div>

    <div class="relative w-full max-w-md">
        <div class="bg-white/90 backdrop-blur-xl border border-stone-100 rounded-3xl shadow-2xl p-10 text-center">

            {{-- Animation scene --}}
            <div class="relative h-36 mb-6 fade-up">

                {{-- Ground line --}}
                <div class="absolute bottom-0 left-8 right-8 h-px bg-stone-200"></div>

                {{-- Lock sign (swinging) --}}
                <div class="absolute right-12 bottom-0 flex flex-col items-center">
                    {{-- Pole --}}
                    <div class="w-0.5 h-16 bg-stone-300"></div>
                    {{-- Sign --}}
                    <div class="sign-swing absolute top-0 origin-top">
                        <div class="mt-1 bg-red-400 text-white rounded-lg px-2.5 py-1.5 shadow-md" style="width:52px;">
                            <svg class="w-5 h-5 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                            </svg>
                            <div class="text-center font-['JetBrains_Mono'] text-xs font-600 mt-0.5 leading-none">403</div>
                        </div>
                    </div>
                </div>

                {{-- Walking person --}}
                <div class="walk-anim absolute bottom-1 left-8" style="width:32px;">
                    <svg viewBox="0 0 32 48" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:32px;height:48px;">
                        {{-- Head --}}
                        <circle cx="16" cy="8" r="6" fill="#1c1917"/>
                        {{-- Eyes --}}
                        <g class="blink-anim" style="transform-origin: 16px 8px;">
                            <circle cx="13.5" cy="7.5" r="1.2" fill="white"/>
                            <circle cx="18.5" cy="7.5" r="1.2" fill="white"/>
                        </g>
                        {{-- Body --}}
                        <rect x="11" y="15" width="10" height="13" rx="3" fill="#44403c"/>
                        {{-- Left arm --}}
                        <line x1="11" y1="17" x2="5" y2="24" stroke="#44403c" stroke-width="3" stroke-linecap="round"/>
                        {{-- Right arm (waving) --}}
                        <line x1="21" y1="17" x2="27" y2="13" stroke="#44403c" stroke-width="3" stroke-linecap="round"/>
                        {{-- Left leg --}}
                        <line x1="13" y1="28" x2="10" y2="40" stroke="#1c1917" stroke-width="3" stroke-linecap="round"/>
                        {{-- Right leg --}}
                        <line x1="19" y1="28" x2="22" y2="40" stroke="#1c1917" stroke-width="3" stroke-linecap="round"/>
                    </svg>
                </div>

                {{-- Floating "?" bubble --}}
                <div class="float-anim absolute top-2 left-16 w-8 h-8 bg-amber-100 border border-amber-200 rounded-full flex items-center justify-center shadow-sm">
                    <span class="text-amber-500 font-bold text-sm font-['Lora']">?</span>
                </div>

            </div>

            {{-- Text --}}
            <h1 class="font-['Lora'] text-3xl font-semibold text-stone-800 tracking-tight leading-tight mb-3 fade-up-1">
                Ups, Anda <em class="italic text-red-400">tersesat</em>
            </h1>

            <p class="text-stone-400 text-sm leading-relaxed mb-8 fade-up-2 mx-auto max-w-[260px]">
                {{ $exception?->getMessage() ?: ($message ?? 'Anda tidak memiliki izin untuk mengakses halaman ini.') }}
            </p>

            {{-- Actions --}}
            <div class="space-y-3 fade-up-3">
                <a href="{{ url()->previous() !== url()->current() ? url()->previous() : '/' }}"
                   class="flex items-center justify-center gap-2 w-full bg-stone-800 hover:bg-stone-700 text-white font-semibold text-sm rounded-xl py-3.5 transition-all duration-200 shadow-lg shadow-stone-200">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali
                </a>

                <div class="grid grid-cols-2 gap-3">
                    <a href="{{ route('dashboard') }}"
                       class="flex items-center justify-center gap-1.5 border border-stone-200 text-stone-500 hover:bg-stone-50 hover:text-stone-700 font-medium text-xs rounded-xl py-3 transition-all duration-200">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Dashboard
                    </a>
                    <a href="{{ route('login') }}"
                       class="flex items-center justify-center gap-1.5 border border-stone-200 text-stone-500 hover:bg-stone-50 hover:text-stone-700 font-medium text-xs rounded-xl py-3 transition-all duration-200">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/>
                        </svg>
                        Login Ulang
                    </a>
                </div>
            </div>

        </div>

        <p class="text-center text-xs text-stone-300 font-['JetBrains_Mono'] mt-6 tracking-wide">
            {{ config('app.name') }} &mdash; Error 403
        </p>
    </div>
</div>

@endsection