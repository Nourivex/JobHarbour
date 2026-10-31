@props([
    'hover' => false,
    'padding' => 'p-6',
])

@php
$classes = 'bg-white rounded-[14px] border border-[#E2E7ED] shadow-xs ' . ($hover ? 'card-hover ' : '') . $padding . ' ' . $attributes->get('class');
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</div>
