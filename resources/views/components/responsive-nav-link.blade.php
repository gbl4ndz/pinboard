@props(['active'])

@php
$classes = ($active ?? false)
    ? 'block w-full px-3 py-2 rounded-lg text-sm font-semibold text-stone-900 bg-stone-100 transition duration-150 ease-in-out'
    : 'block w-full px-3 py-2 rounded-lg text-sm font-medium text-stone-600 hover:text-stone-900 hover:bg-stone-50 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
