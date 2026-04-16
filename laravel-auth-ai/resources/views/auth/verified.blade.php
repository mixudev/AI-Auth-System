@extends('layouts.app')

@section('title', 'Email Terverifikasi')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,600;1,400;1,600&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
@endpush

@section('content')

<div class="min-h-screen bg-stone-50 flex items-center justify-center px-4 py-12 relative overflow-hidden">

    {{-- Decorative backgrounds --}}
    <div class="absolute -top-32 -right-32 w-96 h-96 bg-emerald-100 rounded-full opacity-40 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -left-24 w-80 h-80 bg-stone-200 rounded-full opacity-30 blur-3xl pointer-events-none"></div>

    <div class="relative w-full max-w-md">

        <div class="bg-white/90 backdrop-blur-xl border border-stone-100 rounded-3xl shadow-2xl p-10 text-center">

            {{-- Icon --}}
            <div class="w-20 h-20 bg-emerald-50 rounded-2xl flex items-center justify-center mx-auto mb-8 -rotate-3 transition-transform hover:rotate-0 duration-300">
                <svg class="w-10 h-10 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>

            <h1 class="font-['Lora'] text-3xl font-semibold text-stone-800 tracking-tight leading-tight mb-4">
                Email <em class="italic text-emerald-500">Terverifikasi</em>
            </h1>

            <p class="text-stone-400 text-sm leading-relaxed mb-10 mx-auto max-w-[280px]">
                Selamat! Alamat email Anda telah resmi terkonfirmasi.
            </p>

            {{-- Actions --}}
            <div class="space-y-4">
                @auth
                <a href="{{ route('dashboard') }}"
                   class="block w-full bg-stone-800 hover:bg-stone-700 text-white font-semibold text-sm rounded-xl py-3.5 transition-all duration-200 shadow-lg shadow-stone-200">
                    Masuk ke Dashboard
                </a>
                @else
                <a href="{{ route('login') }}"
                   class="block w-full border border-stone-200 text-stone-500 hover:bg-stone-50 hover:text-stone-700 font-medium text-xs rounded-xl py-3 transition-all duration-200">
                    Silahkan Login
                </a>
                @endauth
            </div>

        </div>

    </div>
</div>

@endsection