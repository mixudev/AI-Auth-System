@extends('layouts.app')

@section('title', 'Reset Password')

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
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/>
                        </svg>
                    </div>
                </div>

                <h1 class="font-['Lora'] text-3xl font-semibold text-stone-800 tracking-tight leading-tight">
                    Reset <em class="italic text-blue-500">Password</em>
                </h1>

                <p class="mt-3 text-sm text-stone-400 leading-relaxed max-w-xs">
                    Buat password baru yang kuat dan mudah Anda ingat.
                </p>
            </div>

            {{-- Email error --}}
            @error('email')
                <div class="flex items-center gap-2.5 bg-red-50 border border-red-100 text-red-500 rounded-xl px-4 py-3 mb-6 font-['JetBrains_Mono'] text-xs">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                    </svg>
                    {{ $message }}
                </div>
            @enderror

            {{-- Form --}}
            <form action="{{ route('password.update') }}" method="POST" class="space-y-5">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                {{-- Password baru --}}
                <div class="space-y-1.5">
                    <label for="password" class="block font-['JetBrains_Mono'] text-[10px] uppercase tracking-[0.18em] text-stone-400">
                        Password Baru
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-3.5 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-stone-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                            </svg>
                        </div>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            placeholder="••••••••••••"
                            class="w-full pl-10 pr-11 py-3 bg-stone-50 border @error('password') border-red-300 bg-red-50 @else border-stone-200 @enderror rounded-xl text-sm text-stone-700 font-['JetBrains_Mono'] placeholder-stone-300 outline-none transition-all duration-200 focus:border-blue-400 focus:bg-blue-50/60 focus:shadow-[0_0_0_3px_rgba(251,191,36,0.15)]"
                        >
                        {{-- Toggle show/hide --}}
                        <button type="button" onclick="togglePassword('password', this)"
                            class="absolute inset-y-0 right-3.5 flex items-center text-stone-300 hover:text-stone-500 transition-colors duration-150">
                            <svg class="w-4 h-4 eye-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="flex items-center gap-1.5 font-['JetBrains_Mono'] text-xs text-red-400">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Password strength bar --}}
                <div class="space-y-1.5">
                    <div class="flex gap-1">
                        <div class="strength-bar h-1 flex-1 bg-stone-100 rounded-full overflow-hidden transition-all duration-300">
                            <div class="strength-fill h-full w-0 rounded-full transition-all duration-500"></div>
                        </div>
                        <div class="strength-bar h-1 flex-1 bg-stone-100 rounded-full overflow-hidden transition-all duration-300">
                            <div class="strength-fill h-full w-0 rounded-full transition-all duration-500"></div>
                        </div>
                        <div class="strength-bar h-1 flex-1 bg-stone-100 rounded-full overflow-hidden transition-all duration-300">
                            <div class="strength-fill h-full w-0 rounded-full transition-all duration-500"></div>
                        </div>
                        <div class="strength-bar h-1 flex-1 bg-stone-100 rounded-full overflow-hidden transition-all duration-300">
                            <div class="strength-fill h-full w-0 rounded-full transition-all duration-500"></div>
                        </div>
                    </div>
                    <p id="strengthLabel" class="font-['JetBrains_Mono'] text-[10px] text-stone-300 transition-all duration-300">
                        Masukkan password untuk melihat kekuatannya
                    </p>
                </div>

                {{-- Konfirmasi password --}}
                <div class="space-y-1.5">
                    <label for="password_confirmation" class="block font-['JetBrains_Mono'] text-[10px] uppercase tracking-[0.18em] text-stone-400">
                        Konfirmasi Password
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-3.5 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-stone-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.955 11.955 0 003 12c0 6.627 5.373 12 12 12s12-5.373 12-12c0-2.127-.557-4.124-1.534-5.857"/>
                            </svg>
                        </div>
                        <input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            required
                            placeholder="••••••••••••"
                            class="w-full pl-10 pr-11 py-3 bg-stone-50 border border-stone-200 rounded-xl text-sm text-stone-700 font-['JetBrains_Mono'] placeholder-stone-300 outline-none transition-all duration-200 focus:border-blue-400 focus:bg-blue-50/60 focus:shadow-[0_0_0_3px_rgba(251,191,36,0.15)]"
                            id="password_confirmation"
                        >
                        <button type="button" onclick="togglePassword('password_confirmation', this)"
                            class="absolute inset-y-0 right-3.5 flex items-center text-stone-300 hover:text-stone-500 transition-colors duration-150">
                            <svg class="w-4 h-4 eye-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </button>
                    </div>
                    {{-- Match indicator --}}
                    <p id="matchLabel" class="font-['JetBrains_Mono'] text-[10px] text-stone-300 transition-all duration-300 hidden">
                    </p>
                </div>

                {{-- Submit --}}
                <button
                    type="submit"
                    class="w-full flex items-center justify-center gap-2.5 bg-gradient-to-br from-blue-400 to-purple-400 hover:from-blue-300 hover:to-purple-300 text-white font-semibold text-sm rounded-xl py-3.5 transition-all duration-200 shadow-lg shadow-blue-200/60 hover:shadow-blue-300/60 hover:-translate-y-0.5 active:translate-y-0"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                    </svg>
                    Simpan Password Baru
                </button>

            </form>
        </div>

        {{-- Depth shadow --}}
        <div class="absolute -bottom-3 left-8 right-8 h-8 bg-blue-200/50 rounded-3xl blur-lg -z-10"></div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function togglePassword(fieldId, btn) {
    const input = document.getElementById(fieldId);
    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    const icon = btn.querySelector('.eye-icon');
    icon.innerHTML = isHidden
        ? `<path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>`
        : `<path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>`;
}

(function () {
    const pwInput   = document.getElementById('password');
    const cfInput   = document.getElementById('password_confirmation');
    const fills     = document.querySelectorAll('.strength-fill');
    const label     = document.getElementById('strengthLabel');
    const matchLbl  = document.getElementById('matchLabel');

    const levels = [
        { label: 'Sangat lemah', color: 'bg-red-400',    bars: 1 },
        { label: 'Lemah',        color: 'bg-purple-400',  bars: 2 },
        { label: 'Cukup kuat',   color: 'bg-yellow-400',  bars: 3 },
        { label: 'Sangat kuat',  color: 'bg-emerald-400', bars: 4 },
    ];

    function getStrength(pw) {
        let score = 0;
        if (pw.length >= 8)  score++;
        if (pw.length >= 12) score++;
        if (/[A-Z]/.test(pw) && /[a-z]/.test(pw)) score++;
        if (/\d/.test(pw))   score++;
        if (/[^A-Za-z0-9]/.test(pw)) score++;
        return Math.min(Math.ceil(score / 1.25), 4);
    }

    pwInput.addEventListener('input', () => {
        const val = pwInput.value;
        if (!val) {
            fills.forEach(f => { f.style.width = '0%'; f.className = 'strength-fill h-full w-0 rounded-full transition-all duration-500'; });
            label.textContent = 'Masukkan password untuk melihat kekuatannya';
            label.className = 'font-[\'JetBrains_Mono\'] text-[10px] text-stone-300 transition-all duration-300';
            return;
        }
        const s = getStrength(val);
        const lv = levels[s - 1];
        fills.forEach((f, i) => {
            f.style.width = i < s ? '100%' : '0%';
            f.className = `strength-fill h-full rounded-full transition-all duration-500 ${i < s ? lv.color : ''}`;
        });
        label.textContent = lv.label;
        label.className = `font-['JetBrains_Mono'] text-[10px] transition-all duration-300 ${
            s <= 1 ? 'text-red-400' : s === 2 ? 'text-purple-400' : s === 3 ? 'text-yellow-500' : 'text-emerald-500'
        }`;
        checkMatch();
    });

    function checkMatch() {
        const pw = pwInput.value;
        const cf = cfInput.value;
        if (!cf) { matchLbl.classList.add('hidden'); return; }
        matchLbl.classList.remove('hidden');
        if (pw === cf) {
            matchLbl.textContent = '✓ Password cocok';
            matchLbl.className = "font-['JetBrains_Mono'] text-[10px] text-emerald-500 transition-all duration-300";
        } else {
            matchLbl.textContent = '✗ Password tidak cocok';
            matchLbl.className = "font-['JetBrains_Mono'] text-[10px] text-red-400 transition-all duration-300";
        }
    }

    cfInput.addEventListener('input', checkMatch);
})();
</script>
@endpush