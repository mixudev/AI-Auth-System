<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Left: Active Provider Card --}}
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 right-0 p-8 opacity-5 text-indigo-500">
                <i class="fa-solid fa-tower-broadcast text-8xl"></i>
            </div>
            
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-500 text-white flex items-center justify-center shadow-lg shadow-indigo-500/20">
                            <i class="fa-solid fa-link text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-800 dark:text-white">Primary Connection Hub</h3>
                            <p class="text-xs text-slate-400">Pusat kendali rute pesan otomatis sistem.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <button onclick="testSystemConnection()" class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white text-[10px] font-bold rounded-xl shadow-lg shadow-emerald-500/20 transition-all flex items-center gap-2">
                            <i class="fa-solid fa-bolt"></i>
                            Test Pulse
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                        <p class="text-[10px] font-mono text-slate-400 uppercase tracking-widest mb-3">Current Provider</p>
                        <div class="flex items-center gap-3">
                            @if(data_get($systemSettings, 'provider') === 'fonnte')
                                <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-500/20 text-indigo-500 flex items-center justify-center">
                                    <i class="fa-solid fa-bolt"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-700 dark:text-slate-200">Fonnte API</p>
                                    <p class="text-[10px] text-slate-400">High Speed Gateway</p>
                                </div>
                            @else
                                <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-500/20 text-emerald-500 flex items-center justify-center">
                                    <i class="fa-solid fa-building-columns"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-700 dark:text-slate-200">Official API</p>
                                    <p class="text-[10px] text-slate-400">Enterprise Stable</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                        <p class="text-[10px] font-mono text-slate-400 uppercase tracking-widest mb-3">Routing Engine</p>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-500/20 text-amber-500 flex items-center justify-center">
                                <i class="fa-solid fa-shuffle"></i>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-700 dark:text-slate-200">Smart Failover</p>
                                <p class="text-[10px] text-slate-400">Auto-retry enabled</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Routing Information & Real Analytics --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 rounded-2xl shadow-sm">
                <h4 class="text-xs font-bold text-slate-700 dark:text-slate-200 flex items-center gap-2 mb-3">
                    <i class="fa-solid fa-shield-halved text-emerald-500"></i>
                    Anti-Ban Guardrail
                </h4>
                <p class="text-[10px] text-slate-400 leading-relaxed mb-4">
                    Sistem keamanan aktif untuk menjaga reputasi nomor WhatsApp Anda dengan pembatasan trafik otomatis.
                </p>
                <div class="space-y-2">
                    <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 dark:bg-slate-800/50">
                        <span class="text-[9px] font-bold text-slate-500 uppercase">Limit Harian</span>
                        <span class="text-[10px] font-mono font-bold text-indigo-500">{{ data_get($systemSettings, 'guardrail.daily_limit_per_config', 100) }} Msg</span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 dark:bg-slate-800/50">
                        <span class="text-[9px] font-bold text-slate-500 uppercase">Quiet Hours</span>
                        <span class="text-[10px] font-mono font-bold text-amber-500">
                            {{ data_get($systemSettings, 'guardrail.quiet_hours_start', '22:00') }} - {{ data_get($systemSettings, 'guardrail.quiet_hours_end', '06:00') }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 rounded-2xl shadow-sm">
                <h4 class="text-xs font-bold text-slate-700 dark:text-slate-200 flex items-center gap-2 mb-3">
                    <i class="fa-solid fa-chart-line text-indigo-500"></i>
                    Traffic Analytics (Last 24h)
                </h4>
                <div class="h-16 flex items-end gap-1 mb-3">
                    @foreach($stats['hourly_traffic'] as $data)
                        <div 
                            class="flex-1 {{ $data['count'] > 0 ? 'bg-indigo-500' : 'bg-slate-100 dark:bg-slate-800' }} rounded-t-sm hover:opacity-80 transition-all cursor-help" 
                            style="height: {{ $data['height'] }}%" 
                            title="{{ $data['hour'] }}: {{ $data['count'] }} messages"
                        ></div>
                    @endforeach
                </div>
                <div class="flex items-center justify-between text-[9px] text-slate-400 font-mono uppercase">
                    <span>{{ $stats['hourly_traffic'][0]['hour'] }}</span>
                    <span>Peak: {{ collect($stats['hourly_traffic'])->max('count') }} msg/hr</span>
                    <span>{{ end($stats['hourly_traffic'])['hour'] }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Right: System Events (Live Status Monitor) --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden flex flex-col">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
            <h3 class="text-xs font-bold text-slate-700 dark:text-slate-200 flex items-center gap-2">
                <i class="fa-solid fa-bolt-lightning text-amber-500"></i>
                Live Status Monitor
            </h3>
        </div>
        <div class="flex-1 overflow-y-auto max-h-[480px] p-5 space-y-4">
            @forelse(collect($logs)->take(10) as $log)
                <div class="flex gap-3 group">
                    <div class="flex flex-col items-center">
                        <div class="w-2 h-2 rounded-full {{ $log->status === 'success' ? 'bg-emerald-500' : 'bg-red-500' }} mt-1.5 ring-4 {{ $log->status === 'success' ? 'ring-emerald-500/10' : 'ring-red-500/10' }}"></div>
                        <div class="w-px h-full bg-slate-100 dark:bg-slate-800 group-last:hidden"></div>
                    </div>
                    <div class="pb-4">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-[10px] font-bold text-slate-700 dark:text-slate-200 uppercase tracking-tighter">{{ $log->status === 'success' ? 'Dispatched' : 'Failed' }}</span>
                            <span class="text-[9px] text-slate-400 font-mono">{{ $log->sent_at->format('H:i') }}</span>
                        </div>
                        <p class="text-[10px] text-slate-500 line-clamp-1 w-40">{{ $log->message }}</p>
                        @if($log->error_message)
                            <p class="text-[9px] text-red-500 mt-1 italic line-clamp-1">{{ $log->error_message }}</p>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-20 opacity-20">
                    <i class="fa-solid fa-satellite-dish text-4xl mb-2"></i>
                    <p class="text-[10px]">No activity detected</p>
                </div>
            @endforelse
        </div>
        <div class="p-4 bg-slate-50 dark:bg-slate-800/30 border-t border-slate-100 dark:border-slate-800">
            <button @click="tab = 'gateways'" onclick="setTimeout(() => window.scrollTo({top: document.body.scrollHeight, behavior: 'smooth'}), 100)" class="w-full py-2 text-[10px] font-bold text-indigo-500 hover:text-indigo-600 transition-colors uppercase tracking-widest">
                Full Log Activity →
            </button>
        </div>
    </div>
</div>
