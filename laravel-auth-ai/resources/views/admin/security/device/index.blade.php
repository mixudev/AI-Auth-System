@extends('layouts.app-dashboard')

@section('title', 'Trusted Devices - MixuAuth')
@section('page-title', 'Trusted Devices')
@section('page-sub', 'Kelola daftar perangkat yang telah diverifikasi dan diizinkan mengakses akun pengguna.')

@section('content')
<div class="space-y-6">
    <!-- Filter & Stats Row -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <form action="{{ route('admin.security.devices.index') }}" method="GET" class="relative group max-w-sm w-full">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-violet-500 transition-colors">
                <i class="fa-solid fa-magnifying-glass text-xs"></i>
            </span>
            <input type="text" name="search" value="{{ request('search') }}" 
                class="block w-full pl-9 pr-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-xs focus:ring-2 focus:ring-violet-500/20 focus:border-violet-500 transition-all outline-none text-slate-600 dark:text-slate-300 shadow-sm"
                placeholder="Cari user, IP, atau fingerprint...">
        </form>

        <div class="flex items-center gap-3">
             <div class="px-3 py-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg shadow-sm">
                <div class="text-[10px] uppercase tracking-wider font-bold text-slate-400">Total Terpercaya</div>
                <div class="text-sm font-mono font-bold text-slate-700 dark:text-slate-200">{{ $devices->total() }} Devices</div>
             </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto text-[11px]">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-800/20 border-b border-slate-100 dark:border-slate-800">
                        <th class="px-6 py-4 font-mono uppercase tracking-wider text-slate-400">User & Device</th>
                        <th class="px-6 py-4 font-mono uppercase tracking-wider text-slate-400">Status</th>
                        <th class="px-6 py-4 font-mono uppercase tracking-wider text-slate-400">Last Active</th>
                        <th class="px-6 py-4 font-mono uppercase tracking-wider text-slate-400 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($devices as $device)
                    <tr class="group hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors {{ $device->is_revoked ? 'opacity-60 grayscale' : '' }}">
                        <!-- Column 1: User & Device -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                    <i class="{{ $device->device_icon }} text-sm"></i>
                                </div>
                                <div class="flex flex-col">
                                    <span class="font-bold text-slate-800 dark:text-slate-100">{{ $device->user->name ?? 'User' }}</span>
                                    <span class="text-[10px] text-slate-400 font-medium">{{ $device->browser_name }} on {{ $device->os_name }}</span>
                                </div>
                            </div>
                        </td>

                        <!-- Column 2: Status -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($device->is_revoked)
                                <span class="inline-flex items-center px-2 py-0.5 rounded bg-red-100 dark:bg-red-900/20 text-[9px] font-bold text-red-600 dark:text-red-400 uppercase ring-1 ring-inset ring-red-600/20">Revoked</span>
                            @elseif($device->is_expired)
                                <span class="inline-flex items-center px-2 py-0.5 rounded bg-amber-100 dark:bg-amber-900/20 text-[9px] font-bold text-amber-600 dark:text-amber-400 uppercase ring-1 ring-inset ring-amber-600/20">Expired</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded bg-emerald-100 dark:bg-emerald-900/20 text-[9px] font-bold text-emerald-600 dark:text-emerald-400 uppercase ring-1 ring-inset ring-emerald-600/20">Active</span>
                            @endif
                        </td>

                        <!-- Column 3: Last Active -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col">
                                <span class="text-slate-700 dark:text-slate-200 font-bold">{{ $device->last_seen_at ? $device->last_seen_at->diffForHumans() : 'Never' }}</span>
                                <span class="text-[9px] text-slate-400">{{ $device->ip_address }}</span>
                            </div>
                        </td>

                        <!-- Column 4: Actions -->
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <!-- Info Button -->
                                <button onclick="showDeviceInfo({{ json_encode([
                                    'user' => $device->user->name ?? 'User',
                                    'email' => $device->user->email ?? '-',
                                    'browser' => $device->browser_name,
                                    'os' => $device->os_name,
                                    'ip' => $device->ip_address,
                                    'country' => $device->country_code ?? 'XX',
                                    'fingerprint' => $device->fingerprint_hash,
                                    'created' => $device->created_at->format('d M Y, H:i'),
                                    'last_seen' => $device->last_seen_at ? $device->last_seen_at->format('d M Y, H:i') : '-',
                                    'expires' => $device->trusted_until ? $device->trusted_until->format('d M Y, H:i') : 'Never',
                                    'status' => $device->is_revoked ? 'Revoked' : ($device->is_expired ? 'Expired' : 'Active')
                                ]) }})" 
                                    class="p-2 rounded-lg bg-slate-50 dark:bg-slate-800 text-slate-400 hover:text-violet-500 hover:bg-violet-50 dark:hover:bg-violet-900/20 transition-all border border-slate-200 dark:border-slate-700"
                                    title="Detail Info">
                                    <i class="fa-solid fa-circle-info text-xs"></i>
                                </button>

                                <!-- Revoke/Restore Action -->
                                <form id="action-form-{{ $device->id }}" action="{{ route('admin.security.devices.revoke', $device) }}" method="POST">
                                    @csrf
                                    <button type="button" 
                                        onclick="confirmDeviceAction({{ $device->id }}, '{{ $device->is_revoked ? 'pulihkan' : 'cabut' }}')"
                                        class="px-3 py-1.5 rounded-lg border {{ $device->is_revoked ? 'border-emerald-200 text-emerald-600 hover:bg-emerald-50' : 'border-red-200 text-red-600 hover:bg-red-50' }} text-[10px] font-bold transition-all uppercase flex items-center gap-1.5 shadow-sm">
                                        @if($device->is_revoked)
                                            <i class="fa-solid fa-undo text-[9px]"></i> Restore
                                        @else
                                            <i class="fa-solid fa-user-slash text-[9px]"></i> Revoke
                                        @endif
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-24 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 rounded-full bg-slate-50 dark:bg-slate-800/50 flex items-center justify-center mb-4">
                                    <i class="fa-solid fa-mobile-screen-button text-3xl text-slate-200 dark:text-slate-700"></i>
                                </div>
                                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200">No Trusted Devices</h3>
                                <p class="text-xs text-slate-400 mt-1 max-w-[250px] mx-auto">Sistem belum mencatat adanya perangkat yang dipercaya untuk saat ini.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($devices->hasPages())
        <div class="px-6 py-4 bg-slate-50/50 dark:bg-slate-800/30 border-t border-slate-100 dark:border-slate-800">
            {{ $devices->links() }}
        </div>
        @endif
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showToast('Success', 'Hash copied to clipboard', 'success');
    });
}

function showDeviceInfo(d) {
    const html = `
        <div class="text-left space-y-3 mt-4 border-t border-slate-100 dark:border-slate-800 pt-4">
            <div class="grid grid-cols-2 gap-y-3 gap-x-4">
                <div>
                    <label class="text-[9px] uppercase text-slate-400 block mb-0.5">Account</label>
                    <div class="text-xs font-bold text-slate-700 dark:text-slate-200">${d.user}</div>
                    <div class="text-[10px] text-slate-400">${d.email}</div>
                </div>
                <div>
                    <label class="text-[9px] uppercase text-slate-400 block mb-0.5">Status Trust</label>
                    <div class="text-xs font-bold ${d.status === 'Active' ? 'text-emerald-500' : 'text-red-500'}">${d.status}</div>
                </div>
                <div>
                    <label class="text-[9px] uppercase text-slate-400 block mb-0.5">Platform Info</label>
                    <div class="text-xs font-semibold text-slate-700 dark:text-slate-200">${d.browser}</div>
                    <div class="text-[10px] text-slate-400">${d.os}</div>
                </div>
                <div>
                    <label class="text-[9px] uppercase text-slate-400 block mb-0.5">Location</label>
                    <div class="text-xs font-mono text-slate-600 dark:text-slate-400">${d.ip}</div>
                    <div class="text-[10px] text-slate-400 italic">${d.country} Origin</div>
                </div>
                <div class="col-span-2">
                    <label class="text-[9px] uppercase text-slate-400 block mb-0.5">Timeline (UTC)</label>
                    <div class="flex items-center justify-between text-[10px] text-slate-500 mt-1">
                        <span>First: ${d.created}</span>
                        <span>Expires: ${d.expires}</span>
                    </div>
                </div>
            </div>
            <div class="bg-slate-50 dark:bg-black/20 p-2 rounded border border-slate-100 dark:border-slate-800">
                <label class="text-[9px] uppercase text-slate-400 block mb-1">Device Fingerprint</label>
                <code class="text-[9px] break-all text-violet-500 dark:text-violet-400 font-mono">${d.fingerprint}</code>
            </div>
        </div>
    `;

    AppPopup.show({
        type: 'info',
        title: 'Device Trust Details',
        description: 'Detailed security analysis for the selected device identifier.',
        icon: '<i class="fa-solid fa-microchip text-2xl"></i>',
        confirmText: 'Selesai',
        showButton: true,
    });
    
    // Inject custom HTML to the description area
    document.getElementById('popup-desc').innerHTML = html;
    document.getElementById('popup-desc').classList.remove('hidden');
}

function confirmDeviceAction(id, action) {
    AppPopup.confirm({
        title: 'Konfirmasi Akses',
        description: `Apakah Anda yakin ingin <b>${action}</b> kepercayaan untuk perangkat ini?`,
        confirmText: 'Ya, Lanjutkan',
        cancelText: 'Batalkan',
        onConfirm: () => document.getElementById(`action-form-${id}`).submit()
    });
}
</script>
@endsection
