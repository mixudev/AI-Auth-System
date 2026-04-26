@extends('layouts.app-dashboard')

@section('title', 'System Configurations')
@section('page-title', 'Global Configurations')

@section('content')
<div class="max-w-7xl mx-auto" x-data="{ 
    tab: 'general',
    sudoPassword: '',
    targetFormId: '',
    submitWithSudo(formId) {
        this.targetFormId = formId;
        this.sudoPassword = ''; // Reset password field
        AppModal.open('sudoModal');
        // Auto-focus after open animation
        setTimeout(() => document.getElementById('sudo-password-input')?.focus(), 450);
    },
    confirmSudo() {
        if (!this.sudoPassword) return;
        AppModal.close('sudoModal');
        const form = document.getElementById(this.targetFormId);
        const passwordInput = document.createElement('input');
        passwordInput.type = 'hidden';
        passwordInput.name = 'admin_password';
        passwordInput.value = this.sudoPassword;
        form.appendChild(passwordInput);
        form.submit();
    }
}">

    <!-- Premium Header -->
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-md bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-500/30">
                <i class="fa-solid fa-gears text-lg"></i>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-800 dark:text-white tracking-tight">System Settings</h1>
        </div>
        <p class="text-sm text-slate-500 dark:text-slate-400">Kelola identitas sistem, kebijakan keamanan, dan konfigurasi integrasi secara terpusat.</p>
    </div>

    <div class="flex flex-col lg:flex-row gap-8">
        
        <!-- Premium Sidebar Navigation -->
        <aside class="lg:w-64 flex-shrink-0">
            <nav class="space-y-1">
                <button @click="tab = 'general'" :class="tab === 'general' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400 border-indigo-500' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50 border-transparent'" 
                        class="w-full flex items-center gap-3 px-4 py-3 text-sm font-bold border-l-4 transition-all duration-200">
                    <i class="fa-solid fa-sliders text-base"></i>
                    <span>General</span>
                </button>
                <button @click="tab = 'security'" :class="tab === 'security' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400 border-indigo-500' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50 border-transparent'" 
                        class="w-full flex items-center gap-3 px-4 py-3 text-sm font-bold border-l-4 transition-all duration-200">
                    <i class="fa-solid fa-shield-halved text-base"></i>
                    <span>Security</span>
                </button>
                <button @click="tab = 'sso'" :class="tab === 'sso' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400 border-indigo-500' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50 border-transparent'" 
                        class="w-full flex items-center gap-3 px-4 py-3 text-sm font-bold border-l-4 transition-all duration-200">
                    <i class="fa-solid fa-key text-base"></i>
                    <span>SSO Policy</span>
                </button>
                <button @click="tab = 'mail'" :class="tab === 'mail' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400 border-indigo-500' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50 border-transparent'" 
                        class="w-full flex items-center gap-3 px-4 py-3 text-sm font-bold border-l-4 transition-all duration-200">
                    <i class="fa-solid fa-envelope-open-text text-base"></i>
                    <span>Mail Server</span>
                </button>
            </nav>

            <div class="mt-8 p-4 bg-slate-50 dark:bg-slate-800/30 rounded-md border border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2 mb-2">
                    <i class="fa-solid fa-circle-info text-indigo-500 text-xs"></i>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Information</span>
                </div>
                <p class="text-[10px] text-slate-500 leading-relaxed italic">Semua perubahan akan dicatat ke dalam audit log sistem demi alasan keamanan.</p>
            </div>
        </aside>

        <!-- Configuration Panels -->
        <main class="flex-1 max-w-4xl">
            @include('settings::Admin.configurations.partials._general')
            @include('settings::Admin.configurations.partials._security')
            @include('settings::Admin.configurations.partials._sso')
            @include('settings::Admin.configurations.partials._mail')
        </main>
    </div>

    @include('settings::Admin.configurations.partials._sudo_modal')

</div>
@endsection
