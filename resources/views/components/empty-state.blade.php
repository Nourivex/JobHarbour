@props([
    'title' => 'Belum ada data',
    'description' => 'Tidak ditemukan data yang sesuai dengan pencarian atau filter Anda.',
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center text-center p-8 sm:p-12 border border-dashed border-[#C5CFDA] rounded-2xl bg-white/60']) }}>
    <div class="w-16 h-16 rounded-2xl bg-[#FFF3EA] text-[#C8460B] flex items-center justify-center mb-4">
        <svg class="w-8 h-8" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <!-- Harbour Crane & Dock vector -->
            <path d="M6 42h36"/>
            <path d="M14 42V18l12-10h12"/>
            <path d="M14 26h24"/>
            <path d="M38 8v14"/>
            <circle cx="38" cy="24" r="2"/>
            <rect x="22" y="32" width="10" height="10" rx="1"/>
        </svg>
    </div>
    <h3 class="text-base font-bold text-[#0B1B2B] mb-1">
        {{ $title }}
    </h3>
    <p class="text-sm text-[#627080] max-w-md mb-6 leading-relaxed">
        {{ $description }}
    </p>
    @if($slot->isNotEmpty())
        <div>
            {{ $slot }}
        </div>
    @endif
</div>
