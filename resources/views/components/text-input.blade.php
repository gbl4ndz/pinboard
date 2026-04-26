@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge([
    'class' => 'form-input' . ($disabled ? ' opacity-60 cursor-not-allowed bg-stone-50' : '')
]) }}>
