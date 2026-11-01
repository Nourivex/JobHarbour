{{-- Logo perahu layar JobHarbour di atas ubin navy, supaya layar putihnya tetap terlihat di latar terang --}}
@props(['size' => 'w-9 h-9'])
<span {{ $attributes->merge(['class' => "$size shrink-0 rounded-xl bg-[#0C2238] inline-flex items-center justify-center"]) }}>
    <img src="/images/logo-jobharbour.webp" alt="" width="256" height="239" class="w-[74%] h-auto" aria-hidden="true">
</span>
