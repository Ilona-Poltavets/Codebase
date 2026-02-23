@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full rounded-lg px-3 py-2 text-start text-sm font-medium text-white bg-slate-900 dark:bg-cyan-500/20 dark:text-cyan-200 dark:ring-1 dark:ring-cyan-400/30 transition'
            : 'block w-full rounded-lg px-3 py-2 text-start text-sm font-medium text-slate-600 hover:text-cyan-700 hover:bg-cyan-50 dark:text-slate-300 dark:hover:text-cyan-300 dark:hover:bg-slate-800 transition';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
