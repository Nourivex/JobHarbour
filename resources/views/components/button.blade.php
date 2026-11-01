@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
])

@php
$baseClasses = 'inline-flex items-center justify-center font-medium rounded-lg transition-all duration-150 btn-press focus-visible:outline-hidden disabled:opacity-50 disabled:pointer-events-none cursor-pointer';

$sizeClasses = [
    'sm' => 'text-xs px-3 py-1.5 gap-1.5 min-h-[36px]',
    'md' => 'text-sm px-4 py-2.5 gap-2 min-h-[44px]',
    'lg' => 'text-base px-5 py-3 gap-2.5 min-h-[48px]',
][$size] ?? 'text-sm px-4 py-2.5 gap-2 min-h-[44px]';

$variantClasses = [
    'primary' => 'bg-[#C8460B] hover:bg-[#993406] text-white shadow-xs',
    'secondary' => 'bg-white hover:bg-[#F4F6F8] text-[#0B1B2B] border border-[#C5CFDA] shadow-xs',
    'navy' => 'bg-[#0C2238] hover:bg-[#133150] text-white shadow-xs',
    'ghost' => 'bg-transparent hover:bg-[#E2E7ED]/50 text-[#3D4B5C] hover:text-[#0B1B2B]',
    'danger' => 'bg-[#B42318] hover:bg-[#911a12] text-white shadow-xs',
    'soft-orange' => 'bg-[#FFF3EA] hover:bg-[#ffe5d3] text-[#8A2E05] border border-[#C8460B]/20',
][$variant] ?? 'bg-[#C8460B] hover:bg-[#993406] text-white';

$classes = "{$baseClasses} {$sizeClasses} {$variantClasses} {$attributes->get('class')}";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
