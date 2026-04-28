@extends('layouts.app-dashboard')

@section('title', 'Edit Gateway: ' . $config->name)
@section('page-title', 'WhatsApp Gateway')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-sm">
        <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Edit Konfigurasi</h3>
            <a href="{{ route('wa-gateway.config.index') }}" class="text-xs text-slate-400 hover:text-indigo-500 transition-colors">Kembali</a>
        </div>
        
        <form action="{{ route('wa-gateway.config.update', $config) }}" method="POST" class="p-6 space-y-4 modal-body">
            @csrf
            @method('PUT')
            
            @if($errors->any())
                <div class="px-3 py-2 bg-red-50 dark:bg-red-500/10 text-red-500 text-[10px] rounded-lg border border-red-200 dark:border-red-500/20 font-bold">
                    {{ $errors->first() }}
                </div>
            @endif

            <div>
                <label for="name">Nama Konfigurasi</label>
                <input type="text" id="name" name="name" value="{{ old('name', $config->name) }}" required>
            </div>

            <div>
                <label for="token">Fontte API Token</label>
                <input type="password" id="token" name="token" placeholder="Biarkan kosong jika tidak ingin mengubah token">
            </div>

            <div>
                <label for="alert_phone_number">Nomor Penerima Alert</label>
                <input type="text" id="alert_phone_number" name="alert_phone_number" value="{{ old('alert_phone_number', $config->alert_phone_number) }}" required>
            </div>

            <div class="flex items-start gap-3 p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-800">
                <div class="pt-0.5">
                    <input type="checkbox" id="send_on_critical_alert" name="send_on_critical_alert" value="1" class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" {{ old('send_on_critical_alert', $config->send_on_critical_alert) ? 'checked' : '' }}>
                </div>
                <div>
                    <label for="send_on_critical_alert" class="mb-0 text-slate-700 dark:text-slate-300 font-bold normal-case tracking-normal">Aktifkan Alert Kritis</label>
                    <p class="text-[10px] text-slate-400 mt-0.5 leading-relaxed">Aktifkan pengiriman notifikasi keamanan otomatis.</p>
                </div>
            </div>

            <div class="flex items-start gap-3 p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-800">
                <div class="pt-0.5">
                    <input type="checkbox" id="is_active" name="is_active" value="1" class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" {{ old('is_active', $config->is_active) ? 'checked' : '' }}>
                </div>
                <div>
                    <label for="is_active" class="mb-0 text-slate-700 dark:text-slate-300 font-bold normal-case tracking-normal">Gateway Aktif</label>
                    <p class="text-[10px] text-slate-400 mt-0.5 leading-relaxed">Aktifkan atau nonaktifkan penggunaan gateway ini dalam sistem.</p>
                </div>
            </div>

            <div class="pt-4 flex justify-end gap-3">
                <a href="{{ route('wa-gateway.config.index') }}" class="modal-btn-cancel">Batal</a>
                <button type="submit" class="modal-btn-primary">Update Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection
