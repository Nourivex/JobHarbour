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

                <!-- Laporan: jalur bagi pelamar bila lowongan mencurigakan -->
                <div class="px-5 sm:px-7 py-4 border-t border-[#E2E7ED] flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-[#3D4B5C]">
                    <svg class="w-4 h-4 text-[#627080]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 22V4a1 1 0 0 1 1-1h12l-2 4 2 4H5"/></svg>
                    <span>Ada yang janggal dengan lowongan ini, misalnya diminta membayar biaya?</span>
                    <button type="button" id="detail-report" class="font-semibold text-[#B42318] hover:underline cursor-pointer">Laporkan ke admin</button>
                </div>
            </div>

            <!-- ====================================================== -->
            <!-- KANAN: analisis kecocokan + aksi + tips                -->
            <!-- ====================================================== -->
            <aside class="lg:sticky lg:top-[96px] flex flex-col gap-5" aria-label="Analisis kecocokan dan aksi">
                <section id="detail-match" class="bg-white rounded-3xl border border-[#E2E7ED] p-5 sm:p-6">
                    <div class="h-72 rounded-2xl animate-skeleton bg-[#F4F6F8]"></div>
                </section>

                <section class="rounded-3xl border border-[#D3E6F7] dark:border-[#1E3A54] bg-[#EEF6FD] dark:bg-[#13283A] p-5 sm:p-6">
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

    <!-- Dialog laporan lowongan -->
    <dialog id="report-dialog" class="report-dialog m-auto w-[min(520px,calc(100%-2rem))] rounded-2xl border border-[#E2E7ED] bg-white p-0 text-[#0B1B2B]" aria-labelledby="report-title">
        <form id="report-form" class="p-5 sm:p-6 space-y-4" novalidate>
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 id="report-title" class="text-lg font-extrabold">Laporkan lowongan</h2>
                    <p class="text-sm text-[#3D4B5C] mt-0.5">Laporan dikirim ke admin JobHarbour dan tidak terlihat oleh perusahaan.</p>
                </div>
                <button type="button" data-report-close class="w-9 h-9 -mr-2 rounded-lg flex items-center justify-center text-[#3D4B5C] hover:bg-[#F4F6F8] cursor-pointer" aria-label="Tutup">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>
            <fieldset>
                <legend class="text-sm font-semibold mb-2">Jenis masalah</legend>
                <div class="grid sm:grid-cols-2 gap-2" id="report-categories">
                    @foreach (['scam' => 'Penipuan atau meminta biaya', 'misleading' => 'Informasi menyesatkan', 'discrimination' => 'Diskriminatif', 'spam' => 'Spam atau duplikat', 'other' => 'Lainnya'] as $value => $label)
                        <label class="report-option flex items-center gap-2.5 rounded-xl border border-[#E2E7ED] px-3 py-2.5 text-sm cursor-pointer hover:border-[#C5CFDA]">
                            <input type="radio" name="category" value="{{ $value }}" class="accent-[#C8460B]" @if ($loop->first) checked @endif>
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </fieldset>
            <div>
                <label for="report-description" class="text-sm font-semibold">Ceritakan yang janggal</label>
                <textarea id="report-description" name="description" rows="4" maxlength="2000" class="mt-1.5 w-full rounded-xl border border-[#E2E7ED] bg-white px-3 py-2.5 text-[15px] text-[#0B1B2B] focus:outline-none focus:ring-2 focus:ring-[#C8460B]/30 focus:border-[#C8460B]" placeholder="contoh: Saat dihubungi, recruiter meminta biaya pelatihan Rp500.000 sebelum wawancara."></textarea>
                <p id="report-error" class="mt-1 text-sm text-[#B42318] min-h-5" role="alert"></p>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" data-report-close class="h-11 px-4 rounded-xl font-semibold text-[#3D4B5C] hover:bg-[#F4F6F8] cursor-pointer">Batal</button>
                <button type="submit" id="report-submit" class="btn-press h-11 px-5 rounded-xl font-semibold text-white bg-[#B42318] hover:bg-[#912018] cursor-pointer disabled:opacity-60">Kirim laporan</button>
            </div>
        </form>
        <div id="report-done" class="hidden p-6 text-center" role="status">
            <svg class="report-check mx-auto w-14 h-14 text-[#157F3B]" viewBox="0 0 52 52" aria-hidden="true"><circle cx="26" cy="26" r="24" fill="none" stroke="currentColor" stroke-width="2.5"/><path fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" d="M15 27l7 7 15-16"/></svg>
            <p class="mt-3 text-lg font-extrabold">Laporan terkirim</p>
            <p class="mt-1 text-sm text-[#3D4B5C]">Terima kasih. Admin JobHarbour akan memeriksa lowongan ini.</p>
            <button type="button" data-report-close class="btn-press mt-5 h-11 px-5 rounded-xl font-semibold bg-[#0C2238] text-white cursor-pointer">Tutup</button>
        </div>
    </dialog>

    @push('scripts')
        @vite('resources/js/pages/job-detail.js')
    @endpush
</x-layouts.app>
