    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($templates as $tpl)
        <div class="group bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-all flex flex-col">
            {{-- Card Header --}}
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-indigo-500/10 text-indigo-500 flex items-center justify-center text-[10px]">
                        <i class="fa-solid fa-file-lines"></i>
                    </div>
                    <h5 class="text-xs font-bold text-slate-700 dark:text-slate-200 truncate w-32">{{ $tpl->name }}</h5>
                </div>
                <span class="px-2 py-0.5 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-500 dark:text-slate-400 text-[8px] font-bold uppercase tracking-wider">
                    {{ $tpl->purpose }}
                </span>
            </div>

            {{-- Card Preview Body --}}
            <div class="p-5 flex-1 flex flex-col">
                <div class="mb-4">
                    <p class="text-[9px] font-mono text-slate-400 uppercase tracking-widest mb-1.5">Template Slug</p>
                    <code class="text-[10px] text-indigo-500 font-bold bg-indigo-50 dark:bg-indigo-500/10 px-2 py-1 rounded">
                        {{ $tpl->slug }}
                    </code>
                </div>
                
                <div class="relative group-hover:border-indigo-500/30 transition-colors p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800 font-mono text-[10px] text-slate-600 dark:text-slate-400 leading-relaxed min-h-[100px]">
                    <div class="absolute top-2 right-2 opacity-20">
                        <i class="fa-solid fa-quote-right text-lg"></i>
                    </div>
                    {!! nl2br(e($tpl->content)) !!}
                </div>
            </div>

            {{-- Card Actions --}}
            <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-2 bg-slate-50/30 dark:bg-slate-800/10">
                <p class="text-[9px] text-slate-400">Terakhir diupdate {{ $tpl->updated_at->diffForHumans() }}</p>
                <div class="flex items-center gap-1.5">
                    <button onclick="openTemplateEditModal({{ json_encode($tpl) }})" class="p-2 rounded-lg text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-500/10 transition-colors" title="Edit Template">
                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                    </button>
                    <button onclick="confirmDeleteTemplate({{ $tpl->id }}, '{{ $tpl->name }}')" class="p-2 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors" title="Hapus">
                        <i class="fa-solid fa-trash text-xs"></i>
                    </button>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full py-16 text-center bg-white dark:bg-slate-900 border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-3xl opacity-50">
            <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-file-circle-plus text-2xl text-slate-400"></i>
            </div>
            <p class="text-sm font-medium text-slate-500">Belum ada template pesan.</p>
            <button onclick="openTemplateModal()" class="mt-4 text-xs font-bold text-indigo-500 hover:text-indigo-600 transition-colors">Buat Template Pertama Anda →</button>
        </div>
        @endforelse
    </div>

    {{-- TEMPLATE HELP CARD --}}
    <div class="bg-indigo-50 dark:bg-indigo-500/5 border border-indigo-100 dark:border-indigo-500/10 rounded-2xl p-6">
        <h4 class="text-sm font-bold text-indigo-600 dark:text-indigo-400 flex items-center gap-2">
            <i class="fa-solid fa-circle-info"></i>
            Panduan Template
        </h4>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 leading-relaxed">
            Anda dapat menggunakan placeholder di bawah ini dalam konten template Anda. Sistem akan otomatis menggantinya saat pengiriman:
        </p>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-4">
            <div class="p-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                <code class="text-indigo-500 text-[10px] font-bold">{event}</code>
                <p class="text-[9px] text-slate-400 mt-1">Isi/deskripsi kejadian</p>
            </div>
            <div class="p-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                <code class="text-indigo-500 text-[10px] font-bold">{time}</code>
                <p class="text-[9px] text-slate-400 mt-1">Waktu kejadian</p>
            </div>
            <div class="p-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                <code class="text-indigo-500 text-[10px] font-bold">{ip}</code>
                <p class="text-[9px] text-slate-400 mt-1">Alamat IP pemicu</p>
            </div>
            <div class="p-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                <code class="text-indigo-500 text-[10px] font-bold">{user}</code>
                <p class="text-[9px] text-slate-400 mt-1">Nama/Username terkait</p>
            </div>
        </div>
    </div>
</div>
