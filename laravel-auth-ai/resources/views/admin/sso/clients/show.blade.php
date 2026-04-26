@extends('layouts.app-dashboard')
@section('title', 'SSO Client Detail')
@section('page-title', 'SSO Client Detail')

@section('content')

@if(session('client_secret'))
<div class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 rounded-lg">
    <h3 class="text-emerald-800 dark:text-emerald-300 font-semibold text-sm mb-2">Simpan Client Secret Ini!</h3>
    <p class="text-emerald-700 dark:text-emerald-400 text-xs mb-3">Ini adalah satu-satunya kesempatan Anda untuk melihat Client Secret. Jika hilang, Anda harus men-generate ulang secret baru.</p>
    <div class="bg-white dark:bg-slate-950 p-3 rounded border border-emerald-200 dark:border-emerald-800 font-mono text-sm break-all select-all">
        {{ session('client_secret') }}
    </div>
</div>
@endif

@if(session('new_webhook_secret'))
<div class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 rounded-lg">
    <h3 class="text-emerald-800 dark:text-emerald-300 font-semibold text-sm mb-2">Webhook Secret Baru!</h3>
    <p class="text-emerald-700 dark:text-emerald-400 text-xs mb-3">Silakan perbarui variabel <code>SSO_WEBHOOK_SECRET</code> di environment aplikasi client Anda.</p>
    <div class="bg-white dark:bg-slate-950 p-3 rounded border border-emerald-200 dark:border-emerald-800 font-mono text-sm break-all select-all">
        {{ session('new_webhook_secret') }}
    </div>
</div>
@endif

<div class="max-w-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
        <h2 class="text-sm font-semibold text-slate-800 dark:text-white">{{ $client->name }}</h2>
        <div class="flex gap-2">
            <a href="{{ route('sso.clients.edit', $client->id) }}" class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-medium rounded-lg">Edit</a>
            <a href="{{ route('sso.clients.index') }}" class="px-3 py-1.5 bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-medium rounded-lg">Back</a>
        </div>
    </div>
    
    <div class="p-6 space-y-6">
        
        <!-- Passport Data -->
        <div>
            <h3 class="text-xs font-mono font-medium text-slate-400 uppercase tracking-wider mb-3">OAuth2 Details (Passport)</h3>
            <div class="bg-slate-50 dark:bg-slate-800/50 rounded-lg p-4 space-y-3">
                <div>
                    <span class="block text-[10px] uppercase text-slate-500">Client ID</span>
                    <span class="font-mono text-sm text-slate-900 dark:text-white select-all">{{ $client->oauth_client_id }}</span>
                </div>
                <div>
                    <span class="block text-[10px] uppercase text-slate-500">Redirect URI</span>
                    <span class="font-mono text-sm text-slate-900 dark:text-white select-all">{{ $oauthClient->redirect ?? '-' }}</span>
                </div>
            </div>
        </div>

        <!-- Webhook Data -->
        <div>
            <div class="flex justify-between items-end mb-3">
                <h3 class="text-xs font-mono font-medium text-slate-400 uppercase tracking-wider">Global Logout Webhook</h3>
                <form action="{{ route('sso.clients.regenerate-secret', $client->id) }}" method="POST" onsubmit="return confirm('Regenerate webhook secret? Client app harus diupdate dengan secret yang baru.')">
                    @csrf
                    <button type="submit" class="text-[10px] bg-slate-200 hover:bg-slate-300 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 px-2 py-1 rounded">Regenerate Secret</button>
                </form>
            </div>
            
            <div class="bg-slate-50 dark:bg-slate-800/50 rounded-lg p-4 space-y-3">
                <div>
                    <span class="block text-[10px] uppercase text-slate-500">Webhook URL</span>
                    <span class="font-mono text-sm text-slate-900 dark:text-white select-all">{{ $client->webhook_url ?? 'Not Configured' }}</span>
                </div>
                @if($client->webhook_secret && !session('new_webhook_secret'))
                <div>
                    <span class="block text-[10px] uppercase text-slate-500">Webhook Secret</span>
                    <span class="font-mono text-sm text-slate-900 dark:text-white">****************************************************************</span>
                </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
