@props([
    'variant' => 'neutral',
    'size' => 'md',
])

@php
$sizeClasses = [
    'sm' => 'text-[11px] px-2 py-0.5 rounded-full font-medium',
    'md' => 'text-xs px-2.5 py-1 rounded-full font-semibold',
    'lg' => 'text-sm px-3 py-1.5 rounded-full font-semibold',
][$size] ?? 'text-xs px-2.5 py-1 rounded-full font-semibold';

$variantClasses = [
    'orange' => 'bg-[#FFF3EA] text-[#8A2E05] border border-[#C8460B]/20',
    'navy' => 'bg-[#0C2238] text-white',
    'blue' => 'bg-[#EBF3FB] text-[#1B65A6] border border-[#1B65A6]/20',
    'success' => 'bg-[#E7F5EC] text-[#157F3B] border border-[#157F3B]/20',
    'danger' => 'bg-[#FDEEEC] text-[#B42318] border border-[#B42318]/20',
    'neutral' => 'bg-[#F4F6F8] text-[#3D4B5C] border border-[#E2E7ED]',
][$variant] ?? 'bg-[#F4F6F8] text-[#3D4B5C] border border-[#E2E7ED]';

$classes = "inline-flex items-center gap-1.5 whitespace-nowrap {$sizeClasses} {$variantClasses} {$attributes->get('class')}";
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>
