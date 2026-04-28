@extends('layouts.app-dashboard')

@section('title', 'Global Security Policy')
@section('page-title', 'Global Security Policy')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800 dark:text-white tracking-tight">Kebijakan Keamanan Global</h2>
            <p class="text-[11px] font-medium text-slate-500 uppercase tracking-widest">Enterprise Security Policy Control</p>
        </div>
    </div>

    <form action="{{ route('admin.security.policy.update') }}" method="POST" class="space-y-6">
        @csrf
        
        <!-- Password Policy -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-sm shadow-sm">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/30 dark:bg-slate-800/20">
                <h3 class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest flex items-center gap-2">
                    <i class="fa-solid fa-shield-halved text-indigo-500"></i> Password & Auth Policy
                </h3>
            </div>
            <div class="p-6 space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <label for="password_min_length" class="block text-[10px] font-bold text-slate-400 uppercase mb-2">Minimal Karakter Password</label>
                        <input type="number" id="password_min_length" name="password_min_length" value="{{ $settings['password_min_length'] }}" required
                               class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-sm text-xs focus:ring-0 focus:border-slate-400 dark:focus:border-slate-600 transition-all font-mono">
                    </div>
                </div>

                <div class="space-y-4">
                    <label class="flex items-start gap-4 cursor-pointer group">
                        <input type="checkbox" name="password_require_symbols" value="1" {{ $settings['password_require_symbols'] ? 'checked' : '' }}
                               class="w-4 h-4 mt-0.5 text-slate-800 border-slate-300 dark:border-slate-700 rounded-sm focus:ring-0">
                        <div>
                            <span class="block text-xs font-bold text-slate-700 dark:text-slate-200 uppercase tracking-tight">Wajib Menggunakan Simbol</span>
                            <span class="block text-[10px] text-slate-400 font-medium">Memaksa penggunaan karakter unik seperti @, #, $, atau %.</span>
                        </div>
                    </label>

                    <label class="flex items-start gap-4 cursor-pointer group">
                        <input type="checkbox" name="password_require_numbers" value="1" {{ $settings['password_require_numbers'] ? 'checked' : '' }}
                               class="w-4 h-4 mt-0.5 text-slate-800 border-slate-300 dark:border-slate-700 rounded-sm focus:ring-0">
                        <div>
                            <span class="block text-xs font-bold text-slate-700 dark:text-slate-200 uppercase tracking-tight">Wajib Menggunakan Angka</span>
                            <span class="block text-[10px] text-slate-400 font-medium">Memaksa penggunaan setidaknya satu digit angka (0-9).</span>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Brute Force Protection -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-sm shadow-sm">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/30 dark:bg-slate-800/20">
                <h3 class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest flex items-center gap-2">
                    <i class="fa-solid fa-user-lock text-rose-500"></i> Brute Force Protection
                </h3>
            </div>
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <label for="max_login_attempts" class="block text-[10px] font-bold text-slate-400 uppercase mb-2">Max Login Attempts</label>
                        <input type="number" id="max_login_attempts" name="max_login_attempts" value="{{ $settings['max_login_attempts'] }}" required
                               class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-sm text-xs focus:ring-0 focus:border-slate-400 dark:focus:border-slate-600 transition-all font-mono">
                    </div>
                    <div>
                        <label for="lockout_duration" class="block text-[10px] font-bold text-slate-400 uppercase mb-2">Lockout Duration (Menit)</label>
                        <input type="number" id="lockout_duration" name="lockout_duration" value="{{ $settings['lockout_duration'] }}" required
                               class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-sm text-xs focus:ring-0 focus:border-slate-400 dark:focus:border-slate-600 transition-all font-mono">
                    </div>
                </div>
            </div>
        </div>

        <!-- IP Whitelist -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-sm shadow-sm">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/30 dark:bg-slate-800/20">
                <h3 class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest flex items-center gap-2">
                    <i class="fa-solid fa-network-wired text-emerald-500"></i> Admin IP Whitelisting
                </h3>
            </div>
            <div class="p-6">
                <textarea id="ip_whitelist" name="ip_whitelist" rows="3" placeholder="Contoh: 127.0.0.1, 192.168.1.1"
                          class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-sm text-xs focus:ring-0 focus:border-slate-400 dark:focus:border-slate-600 transition-all font-mono leading-relaxed">{{ $settings['ip_whitelist'] }}</textarea>
                <div class="flex items-center gap-2 mt-3 text-[10px] text-amber-500 font-bold uppercase tracking-tighter">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    Warning: Incorrect IP configuration may lock you out of the admin panel.
                </div>
            </div>
        </div>

        <div class="flex justify-end pt-4">
            <button type="submit" class="bg-slate-800 hover:bg-slate-900 dark:bg-slate-700 dark:hover:bg-slate-600 text-white px-10 py-2.5 rounded-sm text-[11px] font-bold uppercase tracking-widest shadow-lg shadow-slate-900/10 transition-all border border-transparent">
                Update Security Policy
            </button>
        </div>
    </form>
</div>
@endsection
