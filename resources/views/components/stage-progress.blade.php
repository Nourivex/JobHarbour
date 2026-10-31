@props([
    'currentStage' => 'screening',
    'status' => 'in_progress',
])

@php
$stages = [
    ['key' => 'screening', 'label' => 'Screening'],
    ['key' => 'assessment', 'label' => 'Assessment'],
    ['key' => 'interview', 'label' => 'Wawancara'],
    ['key' => 'offering', 'label' => 'Penawaran'],
];

$stageIndex = 0;
foreach ($stages as $idx => $stg) {
    if ($stg['key'] === $currentStage) {
        $stageIndex = $idx;
        break;
    }
}

$isRejected = $currentStage === 'rejected' || $status === 'rejected';
$isCanceled = $currentStage === 'canceled' || $status === 'canceled';
@endphp

<div {{ $attributes->merge(['class' => 'w-full py-2']) }}>
    @if($isCanceled)
        <div class="flex items-center gap-2 p-3 rounded-xl bg-[#F4F6F8] border border-[#C5CFDA] text-[#627080] text-xs font-medium">
            <x-icon name="x-circle" class="w-4 h-4 text-[#627080] shrink-0" />
            <span>Lamaran ini telah dibatalkan secara mandiri oleh pelamar.</span>
        </div>
    @elseif($isRejected)
        <div class="flex items-center gap-2 p-3 rounded-xl bg-[#FDEEEC] border border-[#B42318]/20 text-[#B42318] text-xs font-medium">
            <x-icon name="x-circle" class="w-4 h-4 text-[#B42318] shrink-0" />
            <span>Proses seleksi untuk posisi ini telah selesai (tidak lolos tahap seleksi).</span>
        </div>
    @else
        <div class="flex items-center justify-between relative">
            <!-- Connecting Line -->
            <div class="absolute top-1/2 left-4 right-4 -translate-y-1/2 h-0.5 bg-[#E2E7ED] z-0"></div>

            @foreach($stages as $idx => $stg)
                @php
                    $isPassed = $idx < $stageIndex || ($idx === $stageIndex && $status === 'passed');
                    $isCurrent = $idx === $stageIndex && $status !== 'passed';
                @endphp
                <div class="flex flex-col items-center gap-1.5 relative z-10">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-colors
                        @if($isPassed)
                            bg-[#157F3B] text-white shadow-xs
                        @elseif($isCurrent)
                            bg-[#C8460B] text-white ring-4 ring-[#FFF3EA] shadow-xs
                        @else
                            bg-white text-[#627080] border-2 border-[#E2E7ED]
                        @endif
                    ">
                        @if($isPassed)
                            <x-icon name="check" class="w-4 h-4" />
                        @else
                            {{ $idx + 1 }}
                        @endif
                    </div>
                    <span class="text-[11px] font-medium whitespace-nowrap
                        @if($isCurrent)
                            text-[#C8460B] font-bold
                        @elseif($isPassed)
                            text-[#157F3B]
                        @else
                            text-[#627080]
                        @endif
                    ">
                        {{ $stg['label'] }}
                    </span>
                </div>
            @endforeach
        </div>
    @endif
</div>
