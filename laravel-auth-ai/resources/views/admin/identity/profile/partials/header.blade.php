<!-- ════ TOP HEADER ════ -->
<div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden mb-6">
    <!-- Banner -->
    <div class="h-32 bg-gradient-to-r from-slate-100 to-slate-50 dark:from-slate-800 dark:to-slate-800/60 border-b border-slate-100 dark:border-slate-800/60 relative overflow-hidden">
        <div class="absolute inset-0 opacity-[0.04] dark:opacity-[0.06]" style="background-image:radial-gradient(circle,#64748b 1px,transparent 1px);background-size:20px 20px"></div>
    </div>

    <div class="px-6 md:px-8">
        <!-- Avatar & Name -->
        <div class="flex flex-col md:flex-row items-center md:items-end -mt-12 mb-4 gap-4">
            <div class="flex flex-col md:flex-row items-center md:items-end gap-5 text-center md:text-left">
                <div class="w-24 h-24 rounded-lg bg-white dark:bg-slate-900 p-1 border border-slate-200 dark:border-slate-700 shadow-sm relative z-10">
                    <div class="w-full h-full rounded-md bg-slate-100 dark:bg-slate-800 flex items-center justify-center overflow-hidden">
                        @if(Auth::user()->avatar)
                            <img src="{{ Auth::user()->avatar_url }}" class="w-full h-full object-cover" id="header-avatar-img">
                        @else
                            <span class="text-3xl font-bold text-slate-500 dark:text-slate-400">{{ substr(Auth::user()->name,0,1) }}</span>
                        @endif
                    </div>
                </div>
                <div class="pb-1">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white" id="header-name">{{ Auth::user()->name }}</h2>
                    <p class="text-[13px] text-slate-500 dark:text-slate-400 font-medium mt-0.5">{{ Auth::user()->email }}</p>
                </div>
            </div>
        </div>

        <!-- Horizontal Navigation -->
        <nav class="flex overflow-x-auto border-t border-slate-100 dark:border-slate-800/60 hide-scrollbar" id="profile-nav">
            @php
                $navItems = [
                    ['panel' => 'profile',      'icon' => 'fa-user-gear',         'label' => 'Profil'],
                    ['panel' => 'security',     'icon' => 'fa-shield-halved',     'label' => 'Keamanan'],
                    ['panel' => 'preferences',  'icon' => 'fa-sliders',           'label' => 'Preferensi'],
                    ['panel' => 'devices',      'icon' => 'fa-laptop',            'label' => 'Perangkat'],
                    ['panel' => 'activity',     'icon' => 'fa-clock-rotate-left', 'label' => 'Log Aktivitas'],
                ];
            @endphp
            @foreach($navItems as $item)
                <a href="{{ $profileBaseUrl }}?panel={{ $item['panel'] }}"
                   @click.prevent="switchPanel('{{ $item['panel'] }}')"
                   class="profile-nav-link whitespace-nowrap px-3 py-4 border-b-2 text-[13px] font-semibold transition-colors mr-2 hover:text-slate-900 dark:hover:text-white"
                   :class="activePanel === '{{ $item['panel'] }}' ? 'border-violet-500 text-violet-600 dark:text-violet-400' : 'border-transparent text-slate-500 dark:text-slate-400 hover:border-slate-300 dark:hover:border-slate-600'">
                    <i class="fa-solid {{ $item['icon'] }} mr-1.5 text-xs"></i>{{ $item['label'] }}
                </a>
            @endforeach
        </nav>
    </div>
</div>
