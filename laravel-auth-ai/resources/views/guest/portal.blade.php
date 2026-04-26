@extends('layouts.app-dashboard')

@section('title', 'Guest Portal')
@section('page-title', 'Guest Portal')

@section('content')
    <div class="max-w-4xl mx-auto mt-6">
        <!-- Hero Section -->
        <div class="bg-slate-900 rounded-2xl p-8 mb-8 relative overflow-hidden flex items-center gap-8 shadow-xl">
            <div class="absolute -top-16 -right-16 w-72 h-72 rounded-full bg-orange-500/20 blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-20 left-[30%] w-60 h-60 rounded-full bg-amber-500/10 blur-3xl pointer-events-none"></div>
            
            <div class="shrink-0 w-20 h-20 rounded-full bg-gradient-to-br from-orange-500 to-red-600 flex items-center justify-center text-white text-3xl font-bold ring-4 ring-white/10 relative z-10">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            
            <div class="relative z-10">
                <div class="text-xs font-medium tracking-widest uppercase text-orange-400 mb-2">Selamat datang kembali</div>
                <h1 class="text-3xl font-bold text-white mb-2 font-sans">Halo, {{ $user->name }}!</h1>
                <p class="text-slate-400 text-sm leading-relaxed max-w-md">
                    Akun Anda aktif namun belum mendapatkan akses penuh ke dasbor. Hubungi administrator untuk mendapatkan hak akses yang sesuai.
                </p>
            </div>
        </div>

        <!-- Alert -->
        <div class="bg-orange-500/10 border border-orange-500/20 rounded-xl p-5 mb-8 flex items-start gap-4">
            <div class="shrink-0 w-10 h-10 rounded-lg bg-orange-500/20 flex items-center justify-center text-orange-500">
                <i class="fa-solid fa-circle-exclamation text-lg"></i>
            </div>
            <div>
                <h3 class="font-bold text-orange-500 text-sm mb-1">Akses Terbatas</h3>
                <p class="text-slate-600 dark:text-slate-400 text-sm">Anda berhasil masuk ke sistem, namun peran Anda saat ini belum memiliki izin untuk mengakses fitur utama. Silakan hubungi administrator sistem Anda.</p>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs font-semibold tracking-wider uppercase text-slate-500">Total Peran</span>
                    <div class="w-9 h-9 rounded-lg bg-orange-50 dark:bg-orange-500/10 flex items-center justify-center text-orange-500">
                        <i class="fa-solid fa-user-shield"></i>
                    </div>
                </div>
                <div class="text-3xl font-bold text-slate-800 dark:text-slate-100 mb-1">{{ $roles->count() }}</div>
                <div class="text-xs text-slate-500">Peran ditetapkan</div>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs font-semibold tracking-wider uppercase text-slate-500">Izin Akses</span>
                    <div class="w-9 h-9 rounded-lg bg-amber-50 dark:bg-amber-500/10 flex items-center justify-center text-amber-500">
                        <i class="fa-solid fa-key"></i>
                    </div>
                </div>
                <div class="text-3xl font-bold text-slate-800 dark:text-slate-100 mb-1">{{ $permissions->count() }}</div>
                <div class="text-xs text-slate-500">Hak akses tersedia</div>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs font-semibold tracking-wider uppercase text-slate-500">Riwayat Login</span>
                    <div class="w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-400">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                </div>
                <div class="text-3xl font-bold text-slate-800 dark:text-slate-100 mb-1">{{ $recentLogs->count() }}</div>
                <div class="text-xs text-slate-500">Login terakhir</div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-8">
            <!-- User Info -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500">
                        <i class="fa-solid fa-id-card"></i>
                    </div>
                    <h3 class="font-semibold text-slate-800 dark:text-slate-200">Informasi Akun</h3>
                </div>
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-sm font-medium text-slate-500">Nama Lengkap</span>
                        <span class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $user->name }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-sm font-medium text-slate-500">Email</span>
                        <span class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $user->email }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-sm font-medium text-slate-500">Status Akun</span>
                        <span class="text-xs font-semibold">
                            @if($user->email_verified_at)
                                <span class="text-green-500 bg-green-50 dark:bg-green-500/10 px-2.5 py-1 rounded-full"><i class="fa-solid fa-check mr-1"></i>Terverifikasi</span>
                            @else
                                <span class="text-orange-500 bg-orange-50 dark:bg-orange-500/10 px-2.5 py-1 rounded-full"><i class="fa-solid fa-xmark mr-1"></i>Belum Diverifikasi</span>
                            @endif
                        </span>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <span class="text-sm font-medium text-slate-500">Bergabung</span>
                        <span class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $user->created_at->format('d M Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- Roles -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-8 h-8 rounded-lg bg-orange-50 dark:bg-orange-500/10 flex items-center justify-center text-orange-500">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <h3 class="font-semibold text-slate-800 dark:text-slate-200">Peran & Izin Aktif</h3>
                </div>

                <div class="mb-5">
                    <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Peran Saat Ini</div>
                    @if($roles->isNotEmpty())
                        <div class="flex flex-wrap gap-2">
                            @foreach($roles as $role)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                                    {{ $role->name }}
                                </span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-slate-400 italic">Tidak ada peran yang ditetapkan.</p>
                    @endif
                </div>

                @if($permissions->isNotEmpty())
                    <div>
                        <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Izin Tersedia</div>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($permissions->take(12) as $perm)
                                <span class="px-2.5 py-1 rounded text-[10px] font-mono bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-200/50 dark:border-amber-500/20">
                                    {{ $perm->name }}
                                </span>
                            @endforeach
                            @if($permissions->count() > 12)
                                <span class="px-2.5 py-1 rounded text-[10px] font-mono bg-slate-100 dark:bg-slate-800 text-slate-500 border border-transparent">
                                    +{{ $permissions->count() - 12 }} lainnya
                                </span>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        @if($recentLogs->isNotEmpty())
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm mb-8">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500">
                    <i class="fa-solid fa-list-ul"></i>
                </div>
                <h3 class="font-semibold text-slate-800 dark:text-slate-200">Aktivitas Login Terakhir</h3>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-800/50">
                @foreach($recentLogs as $idx => $log)
                    <div class="py-3 flex items-start gap-4">
                        <div class="mt-1.5 w-2 h-2 rounded-full {{ $idx === 0 ? 'bg-orange-500' : 'bg-slate-300 dark:bg-slate-700' }} shrink-0"></div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-slate-800 dark:text-slate-200 mb-0.5 truncate">
                                {{ $log->ip_address ?? 'Unknown IP' }}
                            </div>
                            <div class="text-xs text-slate-500 truncate">
                                {{ Str::limit($log->user_agent ?? 'Unknown Browser', 60) }}
                            </div>
                        </div>
                        <div class="shrink-0 text-xs text-slate-400">
                            {{ $log->occurred_at->diffForHumans() }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- CTA -->
        <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-xl p-8 flex flex-col sm:flex-row items-center justify-between gap-6 shadow-lg border border-slate-700/50">
            <div>
                <h3 class="text-lg font-bold text-white mb-2">Butuh Akses Lebih?</h3>
                <p class="text-slate-400 text-sm max-w-xl">Hubungi administrator sistem untuk mendapatkan peran dan izin yang diperlukan agar Anda dapat menggunakan fitur secara penuh.</p>
            </div>
            <a href="mailto:admin@{{ request()->getHost() }}" class="shrink-0 inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white font-semibold text-sm px-6 py-3 rounded-lg transition-all hover:-translate-y-0.5 hover:shadow-lg hover:shadow-orange-500/20">
                <i class="fa-solid fa-envelope"></i>
                Hubungi Admin
            </a>
        </div>
    </div>
@endsection