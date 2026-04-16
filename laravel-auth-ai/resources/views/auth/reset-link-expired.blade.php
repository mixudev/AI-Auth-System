@extends('layouts.app')

@section('title', 'Link Kadaluarsa')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,600;1,400;1,600&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
@endpush

@section('content')

<div class="min-h-screen bg-stone-50 flex items-center justify-center px-4 py-12 relative overflow-hidden">
    
    {{-- Decorative backgrounds --}}
    <div class="absolute -top-32 -right-32 w-96 h-96 bg-red-100 rounded-full opacity-40 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -left-24 w-80 h-80 bg-stone-200 rounded-full opacity-30 blur-3xl pointer-events-none"></div>

    <div class="relative w-full max-w-md">
        
        <div class="bg-white/90 backdrop-blur-xl border border-stone-100 rounded-3xl shadow-2xl p-10 text-center">
            
            {{-- Icon --}}
            <div class="w-20 h-20 bg-red-50 rounded-2xl flex items-center justify-center mx-auto mb-8 rotate-3 transition-transform hover:rotate-0">
                <svg class="w-10 h-10 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12v-.008zM21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>

            <h1 class="font-['Lora'] text-3xl font-semibold text-stone-800 tracking-tight leading-tight mb-4">
                Link <em class="italic text-red-500">Kadaluarsa</em>
            </h1>

            <p class="text-stone-400 text-sm leading-relaxed mb-10 mx-auto max-w-[280px]">
                {{ $message }}<br>
                Silakan ajukan permintaan reset password baru.
            </p>

            {{-- Actions --}}
            <div class="space-y-4">
                <a href="{{ route('password.request') }}" 
                   class="block w-full bg-stone-800 hover:bg-stone-700 text-white font-semibold text-sm rounded-xl py-3.5 transition-all duration-200 shadow-lg shadow-stone-200">
                    Minta Link Baru
                </a>
                
                <a href="{{ route('login') }}" 
                   class="block w-full border border-stone-200 text-stone-500 hover:bg-stone-50 hover:text-stone-700 font-medium text-xs rounded-xl py-3 transition-all duration-200">
                    Kembali ke Login
                </a>
            </div>

        </div>

    </div>
</div>

@endsection
