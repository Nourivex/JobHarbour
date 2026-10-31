@props([
    'score' => 0,
    'size' => 'md',
    'showLabels' => true,
])

@php
$s = (float) $score;
if ($s >= 80) {
    $barColor = 'bg-[#157F3B]';
    $textColor = 'text-[#157F3B]';
    $bgColor = 'bg-[#E7F5EC]';
    $category = 'Sangat Cocok';
    $statusText = 'Lolos screening otomatis';
} elseif ($s >= 50) {
    $barColor = 'bg-[#1B65A6]';
    $textColor = 'text-[#1B65A6]';
    $bgColor = 'bg-[#EBF3FB]';
    $category = 'Cocok';
    $statusText = 'Ditinjau oleh rekruter';
} else {
    $barColor = 'bg-[#C8460B]';
    $textColor = 'text-[#8A2E05]';
    $bgColor = 'bg-[#FFF3EA]';
    $category = 'Belum Cocok';
    $statusText = 'Perlu penambahan keahlian';
}
@endphp

<div {{ $attributes->merge(['class' => 'w-full flex flex-col gap-2']) }}>
    @if($showLabels)
        <div class="flex items-baseline justify-between">
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold uppercase tracking-wider text-[#627080]">Kecocokan Profil</span>
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $bgColor }} {{ $textColor }}">
                    {{ $category }}
                </span>
            </div>
            <div class="flex items-baseline gap-1">
                <span class="text-2xl font-extrabold {{ $textColor }} num-tabular font-heading tracking-tight" data-score-value>
                    {{ round($s) }}%
                </span>
            </div>
        </div>
    @endif

    <!-- Meter Bar with 50% & 80% Markers -->
    <div class="relative w-full h-3 bg-[#E2E7ED] rounded-full overflow-hidden">
        <div
            class="h-full rounded-full transition-all duration-700 ease-out {{ $barColor }}"
            style="width: {{ min(100, max(0, $s)) }}%;"
            data-score-bar
        ></div>
        <!-- 50% Threshold line -->
        <div class="absolute top-0 bottom-0 left-1/2 w-0.5 bg-white/70" title="Ambang batas Cocok (50%)"></div>
        <!-- 80% Threshold line -->
        <div class="absolute top-0 bottom-0 left-[80%] w-0.5 bg-white/70" title="Ambang batas Sangat Cocok (80%)"></div>
    </div>

    @if($showLabels)
        <div class="flex items-center justify-between text-[11px] text-[#627080] font-medium pt-0.5">
            <span>0%</span>
            <span class="pl-4">50% (Cocok)</span>
            <span>80% (Unggul)</span>
            <span>100%</span>
        </div>
    @endif
</div>
