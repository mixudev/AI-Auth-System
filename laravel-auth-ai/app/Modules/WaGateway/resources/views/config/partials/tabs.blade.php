<div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <h1 class="text-lg font-semibold text-slate-800 dark:text-slate-100">WhatsApp Gateway Configuration</h1>
        <p class="text-xs text-slate-400 mt-0.5">Kelola integrasi WhatsApp dan template pesan untuk sistem.</p>
    </div>
    
    <div class="flex items-center gap-2 bg-slate-100 dark:bg-slate-800/50 p-1 rounded-xl border border-slate-200 dark:border-slate-800">
        <button 
            @click="tab = 'gateways'"
            :class="tab === 'gateways' ? 'bg-white dark:bg-slate-700 text-indigo-500 shadow-sm' : 'text-slate-400 hover:text-slate-600'"
            class="px-4 py-1.5 text-[10px] font-bold uppercase tracking-wider rounded-lg transition-all"
        >
            Gateways
        </button>
        <button 
            @click="tab = 'templates'"
            :class="tab === 'templates' ? 'bg-white dark:bg-slate-700 text-indigo-500 shadow-sm' : 'text-slate-400 hover:text-slate-600'"
            class="px-4 py-1.5 text-[10px] font-bold uppercase tracking-wider rounded-lg transition-all"
        >
            Templates
        </button>
        <button 
            @click="tab = 'logs'"
            :class="tab === 'logs' ? 'bg-white dark:bg-slate-700 text-indigo-500 shadow-sm' : 'text-slate-400 hover:text-slate-600'"
            class="px-4 py-1.5 text-[10px] font-bold uppercase tracking-wider rounded-lg transition-all"
        >
            Logs
        </button>
    </div>

    <div class="flex items-center gap-2">
        <button onclick="openSystemConfigModal()"
           class="px-4 py-2 bg-slate-700 hover:bg-slate-800 text-white font-bold text-xs rounded-lg transition-all flex items-center gap-2 shadow-sm">
            <i class="fa-solid fa-sliders text-[10px]"></i>
            System Config
        </button>

        <template x-if="tab === 'gateways'">
            <button onclick="openInfoModal()" class="px-3 py-1.5 bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/20 rounded-lg flex items-center gap-2 transition-all group">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-tight">System Online & Info</span>
                <i class="fa-solid fa-circle-info text-emerald-500/50 group-hover:text-emerald-500 transition-colors"></i>
            </button>
        </template>
        <template x-if="tab === 'templates'">
            <button 
                onclick="openTemplateModal()"
                class="px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white font-bold text-xs rounded-lg transition-all flex items-center gap-2 shadow-sm"
            >
                <i class="fa-solid fa-plus text-[10px]"></i>
                Buat Template
            </button>
        </template>
    </div>
</div>
