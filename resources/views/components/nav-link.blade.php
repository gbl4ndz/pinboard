@props(['active'])

@php
$classes = ($active ?? false)
    ? 'inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-semibold text-stone-900 bg-stone-100 rounded-lg transition duration-150'
    : 'inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-stone-500 hover:text-stone-800 hover:bg-stone-50 rounded-lg transition duration-150';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
