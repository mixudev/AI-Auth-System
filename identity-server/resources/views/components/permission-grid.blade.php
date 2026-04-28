@props([
    'permissions' => [],
    'name' => 'permissions',
    'modalType' => 'create',
])

<div class="flex flex-col gap-0.5" @click.stop>
    @foreach($permissions as $permission)
        @php
            $group = strtolower(explode('.', $permission->name)[0]);
        @endphp
        <label
            class="modal-permission-label flex items-center gap-3 px-3 py-2 rounded-lg cursor-pointer select-none hover:bg-white dark:hover:bg-slate-800/60 has-[:checked]:bg-indigo-50/80 dark:has-[:checked]:bg-indigo-900/20 transition-colors duration-150"
            @click.stop
        >
            <input
                type="checkbox"
                name="{{ $name }}[]"
                value="{{ $permission->name }}"
                class="peer h-4 w-4 shrink-0 rounded-[4px] cursor-pointer border-2 border-slate-300 dark:border-slate-600 checked:bg-indigo-600 checked:border-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 transition-all duration-150
                    perm-checkbox-{{ $modalType }}
                    perm-group-{{ $modalType }}-{{ $group }}"
            />
            <span class="text-sm font-medium leading-none text-slate-600 dark:text-slate-300 peer-checked:text-indigo-600 dark:peer-checked:text-indigo-400 transition-colors duration-150">
                {{ $permission->name }}
            </span>
        </label>
    @endforeach
</div>