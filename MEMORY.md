# JobHarbour - Project Memory & Checkpoint Log

Dokumen ini berfungsi sebagai memori jangka panjang (*long-term memory*) untuk mencatat seluruh perjalanan pengembangan, arsitektur yang sudah dibangun, riwayat bugfix, dan status terkini proyek JobHarbour agar konteks pengerjaan tetap utuh dan tidak hilang.

---

## 1. Profil Pengguna & Tim Pengembang
- **Pengguna:** **Mas Affif** (Muhammad Affif - NIM: 24225046).
- **Mata Kuliah:** Pemrograman Web Service (5537344 / 3 SKS) - S1 Teknik Informatika, Fakultas Sains dan Teknologi, Universitas Harkat Negeri Tegal (Tahun Akademik 2026/2027).
- **Dosen Pengampu:** Zaenul Arif, S.Kom., M.Kom.
- **Tim Kelompok 4:**
  1. Nabe'ela Ayu Ning Tyas Zahra (23215052) - Auth & Profile Service (:8001 / `db_auth`)
  2. M. Fahren Andrean Rangkuti (23215030) - Job Catalog Service (:8002 / `db_jobs`)
  3. Muhammad Yasir Ilham Nabil (23215040) - Application & Matchmaking Service (:8003 / `db_applications`)
  4. Muhammad Affif (24225046) - Recruitment Pipeline Service (:8004 / `db_pipeline`)

---

## 2. Milestone & Riwayat Pengerjaan

### Milestone M1: Inisiasi Proyek & Project Charter
- Penetapan domain sistem rekrutmen berbasis 4 microservices independen.
- Pengadopsian filosofi maritim: *JobHarbour - Pelabuhan Karir para Talenta Sebelum Berlayar*.
- Desain arsitektur database-per-service dan pemodelan relasi ERD.

### Milestone M2: API Blueprint & Kontrak OpenAPI 3.1.0
- Pembuatan kontrak API OpenAPI 3.1.0 (`docs/openapi.yaml`).
- Validasi linting Redocly CLI: 0 errors, 0 warnings.
- Pembuatan antarmuka Swagger UI interaktif di rute `/docs`.
- Penegakan standar JSON Envelope pada seluruh respons.

### Milestone M3: Implementasi Core API & Pengujian Lengkap
- Implementasi 9 endpoint inti backend Laravel 13:
  - Auth JWT stateless (`POST /api/auth/login`, `GET /api/auth/me`).
  - Matriks keahlian profil (`PUT /api/profile/skills`).
  - Katalog lowongan dengan filter lokasi & pagination (`GET /api/jobs`, `POST /api/jobs`).
  - Lamaran kerja dengan kalkulasi Weighted Jaccard Similarity & upload berkas CV PDF (`POST /api/applications`).
  - Papan linimasa seleksi dengan proteksi Anti-IDOR (`PATCH /api/pipelines/{id}/status`).
- Suite pengujian otomatis PHPUnit / Pest: **12 tests passed (68 assertions)**.
- Release resmi v1.1.0 di GitHub dan workflow GitHub Actions CI berstatus hijau.

### Pembaruan Dokumen Ujian Tengah Semester (UTS):
- Penyusunan berkas presentasi resmi `materi/UTS_PWS_Kel4_JobHarbour.pptx` (23 slide bersih, bebas placeholder, bebas strip panjang em-dash/en-dash).
- Pembuatan laporan lengkap 31 halaman sesuai Soal 1 UTS:
  - `materi/UTS_PWS_Kel4_JobHarbour.docx` (Master Word)
  - `materi/UTS_PWS_Kel4_JobHarbour.pdf` (Versi cetak A4 margin 4-3-3-3 cm).
- Penyusunan draft rundown presentasi 12 menit dan skenario live demo 8 menit yang adil untuk 4 anggota kelompok.

### Integrasi Frontend Live & UI/UX Modern Harbour Tech:
- Adopsi rancangan antarmuka Stitch UI ke dalam Blade view Laravel.
- Penghapusan data mockup statis: halaman depan `/`, `/catalog`, `/login`, `/my-dock`, `/profile`, dan `/recruiter` kini **100% terhubung secara live ke database backend**.
- Audit Chrome DevTools: 0 console errors, build asset Vite (`@tailwindcss/vite`) sukses.

### Integrasi Data Karier Nyata & Mesin Agregator Otomatis:
- Pemisahan ketat antara data demo simulasi (`is_demo = true`) dan lowongan nyata terverifikasi (`is_demo = false`).
- Pembangunan registri sumber agregasi (`job_sources`) dan log sinkronisasi (`job_sync_logs`).
- Adapter ekstraksi web karier resmi live: Alkindo Naratama Tbk (`career.alkindo.co.id`) dan Hasnur Internasional Shipping Tbk (`pthis.id/career`).
- Mesin deduplikasi otomatis berbasis Canonical Hash dan external reference ID.
- Perbaikan tata letak kartu lowongan katalog agar adaptif, responsif, dan bebas terhimpit.
- Lencana transparansi asal lowongan: **Sumber Karier Resmi**, **Mitra Terverifikasi**, dan **Data Demo**.

### Audit API & Penyelesaian Isu GitHub (#9, #10, #11):
- **Isu #9:** Menutup celah akses rute `/api/aggregator/sync` dan `/api/aggregator/summary` dengan middleware `jwt.auth:admin`, mencegah pemicuan request HTTP eksternal tanpa otentikasi.
- **Isu #10:** Menghapus penulisan hardcoded `posted_by = 1` pada `JobAggregationService`. Menggunakan ID akun admin dinamis atau `null` (sesuai skema database `posted_by` nullable).
- **Isu #11:** Menegakkan aturan transisi status pipeline pada `PipelineController`. Memblokir transisi mundur (rollback) dari tahapan yang lebih tinggi ke tahapan sebelumnya dengan HTTP 422 (`INVALID_STAGE_TRANSITION`) dan mengunci status pelamar yang telah ditolak (`rejected`).
- **Pengujian:** Suite pengujian otomatis bertambah menjadi 22 tests (147 assertions) berstatus 100% hijau.

### Penggabungan PR #13 & PR #14 dan Model Verifikasi Perusahaan Mas Affif:
- Penggabungan PR #13 (perbaikan mobile & validasi sandi min 8 karakter) dan PR #14 (portal recruiter v2, catatan evaluator internal, profil perusahaan).
- Penyelesaian merge conflict pada `AuthController.php`.
- Implementasi Tiered Trust & Search Priority Boost (ide Mas Affif):
  - Recruiter baru/belum verif: boleh coba buka 1 lowongan trial (`regular_standard`), lowongan ke-2 dicegat wajib verifikasi (`403 COMPANY_NOT_VERIFIED`).
  - Perusahaan terverifikasi resmi: dapat lencana centang biru (`verified_official`), posting tanpa batas, dan otomatis mendapatkan Search Priority Boost di urutan teratas katalog pelamar.
- Testing: 38 automated tests (288 assertions) 100% passed.

### Halaman Postman API Client & Workspace Interaktif (/postman):
- Dibuat halaman mandiri mirip Postman Web (`/postman`, alias `/api-client`, `/api-studio`) yang bersih dan terpisah dari Swagger UI asli (`/docs`).
- Fitur workspace Postman:
  - Koleksi folder untuk 4 microservices + Aggregator (18 endpoint siap uji).
  - 1-Click Role Auth Switcher (Pelamar, Recruiter Resmi, Recruiter Trial, Admin) tanpa perlu copy-paste token manual.
  - Request builder lengkap (Params, Authorization Bearer, Headers, Raw JSON Body dengan beautify).
  - Response viewer warna Postman dengan status code, response time (ms), response size, copy response, dan copy cURL.
  - Tab "Panduan Rubrik UTS" menjelaskan poin kriteria penilaian dosen untuk setiap endpoint.

### Deployment Produksi Cloud (Render + Supabase PostgreSQL + Custom Domain):
- Deployment Docker multi-stage build berhasil dipasang di Render.com.
- Database persisten PostgreSQL cloud terhubung via Supabase (Project ID: `irrdaftlsuurdavytuuk`, Pooler `aws-0-ap-northeast-1.pooler.supabase.com:5432`).
- Domain resmi kustom aktif: `https://jobharbour.nour.web.id` (dengan SSL HTTPS Cloudflare).
- Monitor uptime aktif 24/7 via UptimeRobot (ping berkala 10 menit menjaga server tetap hangat dan bebas cold start).
- Seluruh 38 automated tests (288 assertions) lulus 100%.

---

## 3. Catatan Teknis Penting (Gotchas & Caveats)

1. **Format Tanda Baca (Strict):**
   - Jangan pernah menggunakan karakter `-` (U+2014) atau `-` (U+2013). Selalu ganti dengan `-` biasa.
2. **Karakter Desain UI/UX:**
   - Pertahankan tema terang (*Crisp Light*) untuk area konten utama dokumen/lowongan.
   - Hindari tema gelap pekat pada teks panjang untuk mencegah kelelahan mata pengguna.
   - Jaga konsistensi warna semantik JobHarbour (Deep Navy `#0F172A`, Oceanic Azure `#0284C7`, Lighthouse Gold `#F59E0B`).
3. **Akun Uji Demo Database:**
   - **Rekruter:** `recruiter@jobharbour.id` | Sandi: `password` atau `password123` (Role: `recruiter`).
   - **Pelamar:** `budi@jobharbour.id` | Sandi: `password` atau `password123` (Role: `applicant`).
   - **Pelamar 2:** `siti@jobharbour.id` | Sandi: `password` atau `password123` (Role: `applicant`).
4. **Perintah Harian DDEV & Build:**
   - Menjalankan container: `ddev start`
   - Menjalankan build Vite: `npm run build` atau `ddev npm run build`
   - Menjalankan testing: `ddev artisan test`
   - Mengecek database: `ddev mysql`

---

## 4. Rencana Kerja Selanjutnya (Post-UTS / Milestone M4: Secure API)
- Penerapan Refresh Token Rotation dan Redis Blacklist untuk pengelolaan logout JWT yang aman.
- Penguatan Rate Limiting Adaptif berbasis IP dan API Key peran.
- Integrasi antrean asinkron (Laravel Queue / Redis) untuk notifikasi surel progres wawancara.
