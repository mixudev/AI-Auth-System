@extends('layouts.app-dashboard')

@section('title', 'Security Config — ' . $client->name)
@section('page-title', 'Konfigurasi Area Klien')

@section('content')
<div class="max-w-7xl mx-auto space-y-8 pb-20">

    {{-- Header Section --}}
    @include('admin.sso.clients.access-areas.partials._header')

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        {{-- Left Content: Areas Selection --}}
        <div class="lg:col-span-8 space-y-6">
            
            {{-- Status & Quick Tips --}}
            @include('admin.sso.clients.access-areas.partials._info')

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-sm shadow-sm">
                
                {{-- Toolbar with Search --}}
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="relative w-full md:w-64">
                        <input type="text" id="areaSearch" 
                               placeholder="Cari area (nama/slug)..." 
                               class="w-full pl-9 pr-4 py-2 text-xs bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-sm focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[10px]"></i>
                    </div>
                    
                    <div class="flex items-center gap-2 w-full md:w-auto">
                        <button type="button" id="btnSelectAll"
                                class="flex-1 md:flex-none text-[10px] font-bold px-3 py-2 rounded-sm border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all uppercase tracking-tighter">
                            Select All
                        </button>
                        <button type="button" id="btnClearAll"
                                class="flex-1 md:flex-none text-[10px] font-bold px-3 py-2 rounded-sm border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all uppercase tracking-tighter">
                            Clear All
                        </button>
                    </div>
                </div>

                <form action="{{ route('sso.clients.sync-access-areas', $client) }}" method="POST" id="syncAreaForm">
                    @csrf
                    
                    @if($allAreas->count() > 0)
                        <div class="p-6">
                            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-2 gap-4" id="areaGrid">
                                @foreach($allAreas as $area)
                                    @include('admin.sso.clients.access-areas.partials._area_card', ['area' => $area])
                                @endforeach
                            </div>
                            
                            {{-- Empty Search Result --}}
                            <div id="emptySearch" class="hidden py-12 text-center">
                                <div class="text-slate-300 mb-3"><i class="fa-solid fa-folder-open text-4xl"></i></div>
                                <h5 class="text-sm font-bold text-slate-700 dark:text-slate-300">Tidak menemukan area</h5>
                                <p class="text-xs text-slate-500">Coba kata kunci lain.</p>
                            </div>
                        </div>
                    @else
                        <div class="px-6 py-16 text-center">
                            <i class="fa-solid fa-layer-group text-4xl text-slate-200 mb-4"></i>
                            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 uppercase tracking-widest">Database Kosong</h3>
                            <p class="text-xs text-slate-400 mt-2">Belum ada Access Area yang terdaftar di sistem.</p>
                        </div>
                    @endif
                </form>
            </div>
        </div>

        {{-- Right Sidebar: Summary & Actions --}}
        <div class="lg:col-span-4">
            @include('admin.sso.clients.access-areas.partials._summary')
        </div>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const checkboxes = document.querySelectorAll('.area-checkbox');
        const selectedCount = document.getElementById('selectedCount');
        const accessTypeLabel = document.getElementById('accessTypeLabel');
        const searchInput = document.getElementById('areaSearch');
        const cards = document.querySelectorAll('.area-card-wrapper');
        const emptySearch = document.getElementById('emptySearch');
        const grid = document.getElementById('areaGrid');

        // ─── COUNTER LOGIC
        function updateCount() {
            const count = document.querySelectorAll('.area-checkbox:checked').length;
            selectedCount.textContent = count;
            
            if (count > 0) {
                accessTypeLabel.textContent = 'RESTRICTED';
                accessTypeLabel.className = 'text-[10px] font-black px-2 py-0.5 rounded-sm uppercase tracking-tighter bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400';
            } else {
                accessTypeLabel.textContent = 'OPEN';
                accessTypeLabel.className = 'text-[10px] font-black px-2 py-0.5 rounded-sm uppercase tracking-tighter bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400';
            }
        }

        checkboxes.forEach(cb => cb.addEventListener('change', updateCount));

        // ─── TOOLBAR LOGIC
        document.getElementById('btnSelectAll').addEventListener('click', () => {
            // Only select visible ones
            cards.forEach(card => {
                if(card.style.display !== 'none') {
                    card.querySelector('.area-checkbox').checked = true;
                }
            });
            updateCount();
        });

        document.getElementById('btnClearAll').addEventListener('click', () => {
            checkboxes.forEach(cb => cb.checked = false);
            updateCount();
        });

        // ─── SEARCH LOGIC
        if(searchInput) {
            searchInput.addEventListener('input', (e) => {
                const term = e.target.value.toLowerCase().trim();
                let visibleCount = 0;

                cards.forEach(card => {
                    const name = card.dataset.name;
                    const slug = card.dataset.slug;
                    if(name.includes(term) || slug.includes(term)) {
                        card.style.display = 'block';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });

                if(visibleCount === 0 && grid) {
                    grid.classList.add('hidden');
                    emptySearch.classList.remove('hidden');
                } else if(grid) {
                    grid.classList.remove('hidden');
                    emptySearch.classList.add('hidden');
                }
            });
        }
    });

    function confirmSync() {
        const count = document.querySelectorAll('.area-checkbox:checked').length;
        const msg = count > 0
            ? `Konfigurasi ini akan mewajibkan pengguna memiliki ${count} area akses untuk login. Lanjutkan?`
            : `Menghapus semua restriksi akan membuat akses klien ini menjadi TERBUKA (OPEN). Lanjutkan?`;

        AppPopup.confirm({
            title: 'Terapkan Konfigurasi?',
            description: msg,
            confirmText: 'Ya, Terapkan',
            confirmClass: 'bg-indigo-600',
            onConfirm: () => document.getElementById('syncAreaForm').submit()
        });
    }
</script>
@endsection
