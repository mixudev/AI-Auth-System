<button
    type="{{ $type }}"
    @if($disabled) disabled @endif
    {{ $attributes->merge([
        'class' => '
            group relative inline-flex items-center gap-2
            h-9 px-4 rounded-lg
            bg-indigo-600 text-white text-sm font-medium
            shadow-sm
            overflow-hidden
            transition
            hover:bg-indigo-500
            focus:outline-none focus:ring-2 focus:ring-indigo-500/40
            active:scale-[0.98]
            disabled:opacity-60 disabled:cursor-not-allowed
        '
    ]) }}
>
    <!-- Shimmer -->
    <span
        class="pointer-events-none absolute inset-0
               -translate-x-full
               bg-gradient-to-r
               from-transparent via-white/30 to-transparent
               group-hover:translate-x-full
               transition-transform duration-700 ease-out">
    </span>

    <!-- Slot content -->
    <span class="relative z-10 flex items-center gap-2">
        {{ $slot }}
    </span>
</button>