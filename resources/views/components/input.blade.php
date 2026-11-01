@props([
    'id' => null,
    'name' => null,
    'label' => null,
    'type' => 'text',
    'placeholder' => '',
    'required' => false,
    'error' => null,
    'help' => null,
])

@php
$inputId = $id ?? ($name ?? 'input-' . uniqid());
@endphp

<div class="w-full flex flex-col gap-1.5">
    @if($label)
        <label for="{{ $inputId }}" class="text-sm font-semibold text-[#0B1B2B] flex items-center justify-between">
            <span>{{ $label }}</span>
            @if($required)
                <span class="text-xs font-normal text-[#C8460B]">* Wajib</span>
            @endif
        </label>
    @endif

    <div class="relative">
        <input
            id="{{ $inputId }}"
            name="{{ $name ?? $inputId }}"
            type="{{ $type }}"
            placeholder="{{ $placeholder }}"
            @if($required) required @endif
            {{ $attributes->merge([
                'class' => 'w-full min-h-[44px] px-3.5 py-2.5 bg-white text-[#0B1B2B] placeholder-[#627080] text-sm rounded-lg border border-[#C5CFDA] focus:border-[#C8460B] focus:ring-1 focus:ring-[#C8460B] transition-colors disabled:bg-[#F4F6F8] disabled:cursor-not-allowed'
            ]) }}
        />
        {{ $slot }}
    </div>

    @if($help)
        <p class="text-xs text-[#627080]">{{ $help }}</p>
    @endif

    <p data-field-error="{{ $name ?? $inputId }}" class="text-xs font-medium text-[#B42318] {{ $error ? '' : 'hidden' }}">
        {{ $error }}
    </p>
</div>
