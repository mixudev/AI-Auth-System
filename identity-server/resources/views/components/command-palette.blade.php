@props(['id' => 'commandPalette'])

<div id="{{ $id }}" 
     class="fixed inset-0 z-[100] flex items-start justify-center pt-[10vh] px-4 pointer-events-none opacity-0 invisible transition-opacity duration-200 ease-out"
     role="dialog" aria-modal="true" aria-hidden="true">
    
    <!-- Backdrop with Blur -->
    <div class="fixed inset-0 bg-slate-900/40 dark:bg-slate-950/80 backdrop-blur-sm transition-opacity" 
         onclick="window.CommandPalette.close()"></div>

    <!-- Modal Box -->
    <div class="relative w-full max-w-2xl bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 pointer-events-auto overflow-hidden transform scale-95 transition-transform duration-200">
        
        <!-- Header -->
        <div class="flex items-center px-4 border-b border-slate-100 dark:border-slate-800 h-14">
            <svg class="w-5 h-5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input type="text" id="{{ $id }}-input" 
                   class="w-full py-4 px-3 text-base bg-transparent border-none outline-none focus:outline-none focus:ring-0 text-slate-800 dark:text-slate-100 placeholder-slate-400"
                   placeholder="Type a command or search..."
                   autocomplete="off">
            <div class="flex items-center gap-1 shrink-0">
                <span class="kbd">ESC</span>
            </div>
        </div>

        <!-- Content Area -->
        <div id="{{ $id }}-results" class="max-h-[65vh] overflow-y-auto custom-scrollbar">
            
            <!-- INITIAL STATE: QUICK GUIDE -->
            <div id="{{ $id }}-initial" class="p-4">
                <div class="mb-6">
                    <p class="px-2 mb-3 text-[10px] font-bold uppercase tracking-widest text-slate-400">Search Guide</p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <!-- Navigation Guide -->
                        <div class="p-3 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-500/10 text-blue-500 flex items-center justify-center mb-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                            </div>
                            <h4 class="text-[12px] font-semibold text-slate-700 dark:text-slate-200">Navigasi</h4>
                            <p class="text-[10px] text-slate-500 leading-relaxed">Cari menu dashboard seperti 'Users' atau 'Settings'.</p>
                        </div>
                        <!-- Database Guide -->
                        <div class="p-3 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
                            <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 text-emerald-500 flex items-center justify-center mb-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </div>
                            <h4 class="text-[12px] font-semibold text-slate-700 dark:text-slate-200">User Data</h4>
                            <p class="text-[10px] text-slate-500 leading-relaxed">Ketik nama/email untuk mencari data user di database.</p>
                        </div>
                        <!-- Actions Guide -->
                        <div class="p-3 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
                            <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-500/10 text-amber-500 flex items-center justify-center mb-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </div>
                            <h4 class="text-[12px] font-semibold text-slate-700 dark:text-slate-200">Aksi Cepat</h4>
                            <p class="text-[10px] text-slate-500 leading-relaxed">Ganti tema, cek kesehatan sistem, atau logout instan.</p>
                        </div>
                    </div>
                </div>

                <div>
                    <p class="px-2 mb-2 text-[10px] font-bold uppercase tracking-widest text-slate-400">Paling Sering Digunakan</p>
                    <div id="initial-list-container" class="space-y-0.5">
                        <!-- Items injected by JS -->
                    </div>
                </div>
            </div>

            <!-- SEARCH RESULTS Area -->
            <div id="{{ $id }}-dynamic" class="hidden px-2 py-2"></div>
            
            <!-- EMPTY STATE -->
            <div id="{{ $id }}-empty" class="hidden py-14 text-center">
                <svg class="mx-auto h-12 w-12 text-slate-300 dark:text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M9.172 9.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="mt-4 text-sm font-medium text-slate-900 dark:text-slate-100">No results found</h3>
                <p class="mt-1 text-xs text-slate-500">Kami tidak menemukan apa pun yang cocok dengan kata kunci tersebut.</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="px-5 py-3 bg-slate-50 dark:bg-slate-800/40 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[11px] text-slate-400 font-medium whitespace-nowrap overflow-x-hidden">
            <div class="flex items-center gap-5">
                <span class="flex items-center gap-1.5"><span class="kbd">↵</span> Run Command</span>
                <span class="flex items-center gap-1.5"><span class="kbd">↑↓</span> Navigate</span>
            </div>
            <div class="hidden sm:block">
                <span class="flex items-center gap-1.5"><span class="kbd">ESC</span> Close</span>
            </div>
        </div>
    </div>
</div>

<style>
    #{{ $id }}.open {
        opacity: 1;
        pointer-events: auto;
        visibility: visible;
    }
    #{{ $id }}.open > .relative {
        transform: scale(1);
    }

    .palette-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 14px;
        border-radius: 12px;
        transition: all 120ms ease;
        cursor: pointer;
        color: #64748b;
        text-decoration: none;
        margin-bottom: 1px;
    }

    .palette-item.active {
        background: rgba(139, 92, 246, 0.1);
        color: #7c3aed;
    }

    .dark .palette-item.active {
        background: rgba(139, 92, 246, 0.2);
        color: #a78bfa;
    }

    .palette-category-title {
        padding: 14px 14px 6px;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        color: #94a3b8;
    }

    #{{ $id }} .kbd {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 1.4rem;
        height: 1.3rem;
        padding: 0 0.4rem;
        font-size: 0.65rem;
        font-weight: 800;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        color: #64748b;
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 5px;
        box-shadow: 0 1px 0 rgba(0,0,0,0.05);
    }

    .dark #{{ $id }} .kbd {
        color: #94a3b8;
        background-color: #1e293b;
        border-color: #334155;
        box-shadow: 0 1px 0 rgba(0,0,0,0.2);
    }
</style>
