@php $isChecked = in_array($area->id, $assignedIds); @endphp
<label class="area-card-wrapper relative cursor-pointer group block h-full" data-name="{{ strtolower($area->name) }}" data-slug="{{ strtolower($area->slug) }}">
    <input type="checkbox"
           name="access_area_ids[]"
           value="{{ $area->id }}"
           {{ $isChecked ? 'checked' : '' }}
           class="area-checkbox peer sr-only">
    
    <div class="h-full p-4 rounded-sm border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:border-indigo-400 dark:hover:border-indigo-500/50 peer-checked:border-indigo-600 peer-checked:bg-indigo-50/30 dark:peer-checked:bg-indigo-900/10 dark:peer-checked:border-indigo-500 transition-all flex flex-col shadow-sm relative">
        
        <div class="flex items-start justify-between mb-2">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-sm bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 group-hover:text-indigo-500 transition-colors">
                    <i class="fa-solid fa-tag text-[10px]"></i>
                </div>
                <div class="font-bold text-xs text-slate-700 dark:text-slate-300 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors uppercase tracking-tight">
                    {{ $area->name }}
                </div>
            </div>
        </div>
        
        <div class="text-[10px] font-mono text-slate-400 dark:text-slate-500 mb-2 truncate bg-slate-50 dark:bg-slate-800/50 px-1.5 py-0.5 rounded-sm self-start border border-slate-100 dark:border-slate-700">
            {{ $area->slug }}
        </div>

        @if($area->description)
            <p class="text-[11px] text-slate-500 dark:text-slate-400 flex-grow leading-snug line-clamp-2">
                {{ $area->description }}
            </p>
        @else
            <p class="text-[11px] text-slate-400 dark:text-slate-500 italic flex-grow">
                No description provided.
            </p>
        @endif

        <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-between items-center">
            <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 flex items-center gap-1">
                <i class="fa-solid fa-users text-[9px]"></i> {{ $area->users_count ?? 0 }}
            </span>
            
            <div class="w-4 h-4 rounded-sm border border-slate-300 dark:border-slate-600 flex items-center justify-center peer-checked:bg-indigo-600 peer-checked:border-indigo-600 transition-all">
                <i class="fa-solid fa-check text-[8px] text-white opacity-0 peer-checked:opacity-100"></i>
            </div>
        </div>
    </div>
</label>
