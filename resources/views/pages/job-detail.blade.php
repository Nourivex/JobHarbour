<x-layouts.app title="Detail Lowongan - JobHarbour">

    <div id="job-detail-root" data-job-id="{{ $jobId }}" class="max-w-[1240px] mx-auto px-5 sm:px-8 py-6 sm:py-8">

        <!-- Navigasi balik + breadcrumb -->
        <nav class="flex flex-wrap items-center gap-3 text-sm" aria-label="Breadcrumb">
            <a id="detail-back" href="/catalog" class="btn-press inline-flex items-center gap-2 h-10 px-4 rounded-xl bg-white border border-[#E2E7ED] font-semibold text-[#0B1B2B] hover:border-[#C5CFDA]">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
                Kembali ke Hasil Pencarian
            </a>
            <ol class="flex flex-wrap items-center gap-2 text-[#627080]">
                <li><a href="/catalog" class="hover:text-[#0B1B2B]">Lowongan</a></li>
                <li aria-hidden="true">&rsaquo;</li>
                <li id="detail-crumb" class="text-[#0B1B2B] font-medium" aria-current="page">Memuat...</li>
            </ol>
        </nav>

        <!-- Keadaan: tidak ditemukan / gagal -->
        <div id="detail-error" class="hidden mt-6 rounded-2xl border border-dashed border-[#C5CFDA] bg-white p-12 text-center">
            <p id="detail-error-title" class="text-lg font-extrabold text-[#0B1B2B]">Lowongan tidak ditemukan</p>
            <p id="detail-error-text" class="mt-1 text-[#3D4B5C]">Lowongan ini mungkin sudah dihapus atau tautannya salah.</p>
            <a href="/catalog" class="btn-press mt-5 inline-flex items-center h-11 px-5 rounded-xl bg-[#0C2238] text-white font-semibold">Lihat lowongan lain</a>
        </div>

        <div id="detail-content" class="mt-6 grid lg:grid-cols-[minmax(0,1fr)_370px] gap-6 items-start">

            <!-- ====================================================== -->
            <!-- KIRI: ringkasan + tab                                  -->
            <!-- ====================================================== -->
            <div class="min-w-0 bg-white rounded-3xl border border-[#E2E7ED]">
                <header id="detail-header" class="p-5 sm:p-7">
                    <div class="h-[132px] rounded-2xl animate-skeleton bg-[#F4F6F8]"></div>
                </header>

                <div class="px-5 sm:px-7 border-b border-[#E2E7ED]">
                    <div role="tablist" aria-label="Bagian detail lowongan" class="flex gap-2 -mb-px overflow-x-auto">
                        <button type="button" role="tab" id="tab-detail" aria-selected="true" aria-controls="panel-detail" class="detail-tab">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M8 13h8M8 17h5"/></svg>
                            Detail Pekerjaan
                        </button>
                        <button type="button" role="tab" id="tab-process" aria-selected="false" aria-controls="panel-process" tabindex="-1" class="detail-tab">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12h4l3 8 4-16 3 8h4"/></svg>
                            Proses Rekrutmen
                        </button>
                    </div>
                </div>

                <div id="panel-detail" role="tabpanel" aria-labelledby="tab-detail" class="p-5 sm:p-7">
                    <div class="h-64 rounded-2xl animate-skeleton bg-[#F4F6F8]"></div>
                </div>
                <div id="panel-process" role="tabpanel" aria-labelledby="tab-process" class="hidden p-5 sm:p-7"></div>
            </div>

            <!-- ====================================================== -->
            <!-- KANAN: analisis kecocokan + aksi + tips                -->
            <!-- ====================================================== -->
            <aside class="lg:sticky lg:top-[96px] flex flex-col gap-5" aria-label="Analisis kecocokan dan aksi">
                <section id="detail-match" class="bg-white rounded-3xl border border-[#E2E7ED] p-5 sm:p-6">
                    <div class="h-72 rounded-2xl animate-skeleton bg-[#F4F6F8]"></div>
                </section>

                <section class="rounded-3xl border border-[#D3E6F7] bg-[#EEF6FD] p-5 sm:p-6">
                    <h2 class="flex items-center gap-2.5 text-lg font-extrabold text-[#0B1B2B]">
                        <svg class="w-5 h-5 text-[#1B65A6]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 16v-4M12 8h.01"/></svg>
                        Tips Sebelum Melamar
                    </h2>
                    <ul class="mt-4 space-y-2.5 text-[15px] text-[#3D4B5C]">
                        @foreach (['Pastikan skill di profilmu sudah lengkap dan terbaru.', 'Siapkan CV dalam format PDF, maksimal 2 MB.', 'Tulis surat lamaran singkat yang menyebut pengalaman paling relevan.'] as $tip)
                            <li class="flex gap-2.5">
                                <svg class="w-5 h-5 shrink-0 text-[#1B65A6]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 5 5 9-10"/></svg>
                                {{ $tip }}
                            </li>
                        @endforeach
                    </ul>
                </section>
            </aside>
        </div>
    </div>

    @push('scripts')
        @vite('resources/js/pages/job-detail.js')
    @endpush
</x-layouts.app>
