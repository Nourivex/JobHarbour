@props([
    'lines' => 3,
    'type' => 'card',
])

@if($type === 'card')
    <div {{ $attributes->merge(['class' => 'bg-white rounded-[14px] border border-[#E2E7ED] p-6 space-y-4 animate-skeleton']) }}>
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-[#E2E7ED]"></div>
            <div class="flex-1 space-y-2">
                <div class="h-4 bg-[#E2E7ED] rounded-md w-3/4"></div>
                <div class="h-3 bg-[#E2E7ED] rounded-md w-1/2"></div>
            </div>
        </div>
        <div class="space-y-2 pt-2">
            @for($i = 0; $i < $lines; $i++)
                <div class="h-3 bg-[#E2E7ED] rounded-md {{ $i === $lines - 1 ? 'w-2/3' : 'w-full' }}"></div>
            @endfor
        </div>
        <div class="flex items-center gap-2 pt-2">
            <div class="h-6 w-16 bg-[#E2E7ED] rounded-full"></div>
            <div class="h-6 w-20 bg-[#E2E7ED] rounded-full"></div>
        </div>
    </div>
@elseif($type === 'line')
    <div {{ $attributes->merge(['class' => 'h-4 bg-[#E2E7ED] rounded-md animate-skeleton']) }}></div>
@endif
