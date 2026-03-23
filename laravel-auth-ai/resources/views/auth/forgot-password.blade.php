@extends('layouts.app')

@section('title', 'Lupa Password')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,600;1,400;1,600&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
@endpush

@section('content')

<div class="min-h-screen bg-blue-50 flex items-center justify-center px-4 py-12 relative overflow-hidden">

    {{-- Ambient blobs --}}
    <div class="absolute -top-32 -right-32 w-96 h-96 bg-blue-200 rounded-full opacity-40 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -left-24 w-80 h-80 bg-purple-200 rounded-full opacity-30 blur-3xl pointer-events-none"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-yellow-100 rounded-full opacity-50 blur-3xl pointer-events-none"></div>

    {{-- Dot grid --}}
    <div class="absolute inset-0 pointer-events-none opacity-40"
         style="background-image: radial-gradient(circle, #92400e20 1px, transparent 1px); background-size: 28px 28px;">
    </div>

    <div class="relative w-full max-w-md">

        {{-- Top accent line --}}
        <div class="absolute -top-px left-16 right-16 h-px bg-gradient-to-r from-transparent via-blue-400 to-transparent z-10"></div>

        <div class="bg-white/80 backdrop-blur-xl border border-blue-100 rounded-3xl shadow-2xl shadow-blue-100/70 px-10 py-12">

            {{-- Header --}}
            <div class="flex flex-col items-center text-center mb-10">

                {{-- Icon --}}
                <div class="relative w-20 h-20 mb-6 flex items-center justify-center">
                    <div class="absolute inset-0 rounded-full border border-blue-300 opacity-30 animate-ping"></div>
                    <div class="absolute inset-2 rounded-full border border-blue-200 opacity-60 animate-pulse"></div>
                    <div class="relative w-16 h-16 bg-gradient-to-br from-blue-400 to-purple-400 rounded-2xl shadow-lg shadow-blue-300/50 flex items-center justify-center rotate-3 transition-transform duration-300 hover:rotate-0">
                        <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 9v.906a2.25 2.25 0 01-1.183 1.981l-6.478 3.488M2.25 9v.906a2.25 2.25 0 001.183 1.981l6.478 3.488m8.839 2.51l-4.66-2.51m0 0l-1.023-.55a2.25 2.25 0 00-2.134 0l-1.022.55m0 0l-4.661 2.51m16.5 1.615a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V8.844a2.25 2.25 0 011.183-1.98l7.5-4.04a2.25 2.25 0 012.134 0l7.5 4.04a2.25 2.25 0 011.183 1.98V19.5z"/>
                        </svg>
                    </div>
                </div>

                <h1 class="font-['Lora'] text-3xl font-semibold text-stone-800 tracking-tight leading-tight">
                    Lupa <em class="italic text-blue-500">Password?</em>
                </h1>

                <p class="mt-3 text-sm text-stone-400 leading-relaxed max-w-xs">
                    Tenang, masukkan email Anda dan kami akan mengirimkan link untuk mereset password.
                </p>
            </div>

            {{-- Success alert --}}
            @if (session('success'))
                <div class="flex items-start gap-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl px-4 py-3 mb-6 font-['JetBrains_Mono'] text-xs">
                    <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            {{-- Form --}}
            <form action="{{ route('password.email') }}" method="POST" class="space-y-5">
                @csrf

                {{-- Email field --}}
                <div class="space-y-1.5">
                    <label for="email" class="block font-['JetBrains_Mono'] text-[10px] uppercase tracking-[0.18em] text-stone-400">
                        Alamat Email
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-3.5 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-stone-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
                            </svg>
                        </div>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            autocomplete="email"
                            required
                            value="{{ old('email') }}"
                            placeholder="nama@email.com"
                            class="w-full pl-10 pr-4 py-3 bg-stone-50 border @error('email') border-red-300 bg-red-50 @else border-stone-200 @enderror rounded-xl text-sm text-stone-700 font-['JetBrains_Mono'] placeholder-stone-300 outline-none transition-all duration-200 focus:border-blue-400 focus:bg-blue-50/60 focus:shadow-[0_0_0_3px_rgba(251,191,36,0.15)]"
                        >
                    </div>
                    @error('email')
                        <p class="flex items-center gap-1.5 font-['JetBrains_Mono'] text-xs text-red-400">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Submit --}}
                <button
                    type="submit"
                    class="w-full flex items-center justify-center gap-2.5 bg-gradient-to-br from-blue-400 to-purple-400 hover:from-blue-300 hover:to-purple-300 text-white font-semibold text-sm rounded-xl py-3.5 transition-all duration-200 shadow-lg shadow-blue-200/60 hover:shadow-blue-300/60 hover:-translate-y-0.5 active:translate-y-0"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/>
                    </svg>
                    Kirim Link Reset
                </button>

                {{-- Divider --}}
                <div class="w-full h-px bg-stone-100"></div>

                {{-- Back link --}}
                <a href="{{ route('login') }}" class="group w-full flex items-center justify-center gap-1.5 text-xs text-stone-400 hover:text-stone-600 font-['JetBrains_Mono'] transition-colors duration-200">
                    <svg class="w-3.5 h-3.5 transition-transform duration-200 group-hover:-translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                    </svg>
                    Kembali ke halaman login
                </a>

            </form>
        </div>

        {{-- Depth shadow --}}
        <div class="absolute -bottom-3 left-8 right-8 h-8 bg-blue-200/50 rounded-3xl blur-lg -z-10"></div>
    </div>
</div>

@endsection