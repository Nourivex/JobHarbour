# MASTER PROMPT & DOKUMEN MATERI PRESENTASI UTS (CANVA AI)
## Mata Kuliah: Pemrograman Web Service (5537344 / 3 SKS)
### Proyek: JobHarbour - Kelompok 4 | Universitas Harkat Negeri (Tegal)

> **PETUNJUK CARA PAKAI DI CANVA:**
> 1. **Opsi A (Canva Docs ke Presentasi):** Buat dokumen baru di **Canva Docs**, salin seluruh teks di bawah ini (atau per bab), lalu klik tombol **"Convert" / "Ubah ke Presentasi"** di pojok kanan atas Canva. Pilih tema formal/teknologi navy blue.
> 2. **Opsi B (Canva AI Magic Design / Presentation Generator):** Salin bagian **"Prompt Global Canva AI"** di bawah ini sebagai prompt pembuka, lalu masukkan rincian slide per slide untuk meng-generate layout yang presisi sesuai template UTS dosen pengampu.
> 3. **Format & Warna Template Dosen:** Background utama Putih / Fog Slate, Warna Header Deep Harbour Navy (`#0F172A`), Aksen Oceanic Azure (`#0284C7`), Aksen Skor Signal Amber (`#F59E0B`), Font Judul *Plus Jakarta Sans* / *Inter* (minimal 14 pt, poin-poin ringkas, tanpa paragraf panjang).

---

```text
PROMPT GLOBAL UNTUK CANVA AI / MAGIC PRESENTATION:

"Buatkan presentasi profesional akademik untuk Ujian Tengah Semester (UTS) mata kuliah Pemrograman Web Service dengan judul 'JobHarbour: Platform Rekrutmen Terintegrasi Berbasis Microservice dengan Mesin Pemadanan Keahlian Otomatis'.
Desain harus bersih, minimalis, dan formal akademik (berlatar belakang putih/abu-abu terang dengan aksen biru tua navy #0F172A dan biru laut #0284C7).
Gunakan rasio 16:9, tata letak berbasis kartu modular, bullet points ringkas, tabel terstruktur, dan blok cuplikan kode monospace.
Ikuti urutan persis 24 slide berikut tanpa mengubah sistematika template resmi dosen pengampu."
```

---

# SLIDE 1: PETUNJUK TEMPLATE
*(Slide ini dilewati saat presentasi / dihapus sebelum diekspor)*
- **Status:** Slide instruksi template resmi Fakultas Sains dan Teknologi UHN Tegal.
- **Aturan Presentasi:** Durasi presentasi maksimal 12 menit, demo sistem 8 menit, dan tanya jawab 10 menit.
- **Kerahasiaan:** Tidak menampilkan data rahasia (`.env`, password, token akses privat).

---

# SLIDE 2: SAMPUL UTAMA
**PRESENTASI PROJECT UTS - KELOMPOK 4**

### JobHarbour
**Platform Rekrutmen Terintegrasi Berbasis Microservice dengan Mesin Pemadanan Keahlian Otomatis (Automated Skill-Matchmaking Engine & Multi-Stage Pipeline Tracker)**

- **Mata Kuliah:** Pemrograman Web Service (5537344 / 3 SKS)
- **Dosen Pengampu:** Zaenul Arif, S.Kom., M.Kom.
- **Program Studi:** S1 Teknik Informatika, Fakultas Sains dan Teknologi
- **Perguruan Tinggi:** Universitas Harkat Negeri (Tegal) - Tahun 2026
- **Disusun Oleh Tim Pengembang:**
  1. M. Fahren Andrean Rangkuti (NIM: 23215030)
  2. Muhammad Yasir Ilham Nabil (NIM: 23215040)
  3. Nabe'ela Ayu Ning Tyas Zahra (NIM: 23215052)
  4. Muhammad Affif (NIM: 24225046)

---

# SLIDE 3: TIM & PEMBAGIAN PERAN
**PENGANTAR: Tim Pengembang & Kontribusi Nyata**

1. **Nabe'ela Ayu Ning Tyas Zahra (NIM: 23215052)**
   - *Peran:* Backend Engineer - Auth & Profile Service (`db_auth` / Port 8001)
   - *Kontribusi Utama:* Autentikasi stateless kustom JWT, enkripsi kredensial, registrasi multi-role (Pelamar & Rekruter), serta manajemen matriks keahlian profil.
2. **M. Fahren Andrean Rangkuti (NIM: 23215030)**
   - *Peran:* Backend Engineer - Job Catalog Service (`db_jobs` / Port 8002)
   - *Kontribusi Utama:* Operasi CRUD lowongan kerja, perancangan bobot persyaratan keahlian (*job requirements*), query pencarian, filter kota, dan paginasi.
3. **Muhammad Yasir Ilham Nabil (NIM: 23215040)**
   - *Peran:* Backend Engineer - Application & Matchmaking Service (`db_applications` / Port 8003)
   - *Kontribusi Utama:* Algoritma kecocokan *Weighted Jaccard Similarity*, upload resume berkas CV PDF, penyimpanan dokumen lamaran, dan transaksi basis data.
4. **Muhammad Affif (NIM: 24225046)**
   - *Peran:* Backend & Security Engineer - Recruitment Pipeline Service (`db_pipeline` / Port 8004)
   - *Kontribusi Utama:* Linimasa pelacakan seleksi 4 tahap (*Screening, Assessment, Interview, Offering*), proteksi otorisasi anti-IDOR, penjadwalan wawancara, dan setup CI/CD.

---

# SLIDE 4: AGENDA PRESENTASI
**PENGANTAR: Alur Pembahasan & Pembagian Pembicara**

- **01. Pendahuluan**
  - Latar belakang, problem statement, tujuan proyek, ruang lingkup, dan identifikasi aktor.
  - *Pembicara:* Nabe'ela Ayu Ning Tyas Zahra
- **02. Analisis & Perancangan API**
  - Arsitektur microservice, Entity Relationship Diagram (ERD), matriks 8+ endpoint inti, spesifikasi OpenAPI 3.1, dan standardisasi envelope JSON.
  - *Pembicara:* M. Fahren Andrean Rangkuti
- **03. Implementasi & Pengujian**
  - Struktur kode berlapis Laravel, transaksi atomic `DB::transaction`, proteksi anti-IDOR, automated tests PHPUnit, adapter API eksternal, dan kolaborasi Git.
  - *Pembicara:* Muhammad Yasir Ilham Nabil & Muhammad Affif
- **04. Penutup & Skenario Demo**
  - Kendala teknis, evaluasi pencapaian M1-M3, rencana pengembangan pasca-UTS, dan demo sistem langsung.
  - *Pembicara:* Seluruh Anggota Tim

---

# SLIDE 5: PEMBATAS 01 - PENDAHULUAN
## 01. Pendahuluan
*"Menghadirkan transparansi proses rekrutmen melalui algoritma pemadanan keahlian objektif dan linimasa seleksi bertahap yang terpantau real-time."*

---

# SLIDE 6: LATAR BELAKANG & PROBLEM STATEMENT
**01. PENDAHULUAN: Masalah Riil Dunia Kerja**

### Latar Belakang
- **Ketidakpastian Pelamar (*Black Hole Effect*):** Lebih dari 70% pencari kerja mengeluhkan tidak pernah menerima kepastian status berkas setelah melamar.
- **Beban Penapisan Manual Rekruter:** Rekruter menghabiskan ratusan jam menyortir berkas CV tanpa metrik kecocokan kompetensi awal yang terstandarisasi.
- **Urgensi Web Service:** Dibutuhkan arsitektur layanan mandiri (*microservice*) yang memisahkan beban komputasi penelusuran lowongan dan kalkulasi skor matchmaking.
- **Harmoni Identitas Tegal:** Filosofi pelabuhan (*harbour*) sebagai gerbang bahari keberangkatan para talenta profesional di Kota Bahari Tegal.

### Problem Statement
> *"Pelamar mengalami kendala dalam mengetahui tingkat kesesuaian keahliannya terhadap kualifikasi lowongan dan ketiadaan transparansi posisi lamaran, sementara rekruter terbebani oleh penyortiran manual berkas tanpa sistem pemeringkatan otomatis."*

---

# SLIDE 7: TUJUAN, RUANG LINGKUP & AKTOR
**01. PENDAHULUAN: Batasan & Pengguna Sistem**

### Tujuan Proyek
1. Mengembangkan sistem web service perekrutan kerja dengan arsitektur REST API mandiri berbasis Laravel 13 dan MariaDB.
2. Mengotomatisasi kalkulasi kecocokan kualifikasi pelamar menggunakan algoritma *Weighted Jaccard Similarity Index*.
3. Menyediakan pelacakan status seleksi bertahap (*multi-stage pipeline tracker*) secara transparan dan aman dari eksploitasi IDOR.

### Ruang Lingkup & Batasan
- **Tercakup:** Autentikasi JWT, manajemen lowongan, upload berkas CV PDF (maks. 2 MB), matchmaking skor real-time, linimasa seleksi 4 tahap, dan dokumentasi OpenAPI 3.1.
- **Batasan Proyek:** Tidak mencakup gerbang pembayaran (*payment gateway*) dan tidak melayani tes psikotes interaktif berbasis video.

### Aktor & Konsumen API
- **Pelamar (Applicant):** Mengelola keahlian profil, mencari lowongan, mengirim berkas lamaran, dan memantau linimasa.
- **Perekrut (Recruiter):** Memposting lowongan berbobot kualifikasi, meninjau profil pelamar, mengunduh CV, dan mengubah tahap seleksi.
- **Konsumen API:** Antarmuka Web SPA, aplikasi mobile masa depan, dan server pengujian Postman.

---

# SLIDE 8: PEMBATAS 02 - ANALISIS & PERANCANGAN
## 02. Analisis & Perancangan API
*"Arsitektur microservices, model data terdistribusi, spesifikasi OpenAPI 3.1.0, dan standardisasi kontrak JSON envelope."*

---

# SLIDE 9: ARSITEKTUR SOLUSI
**02. ANALISIS & PERANCANGAN: Topologi Sistem Terdistribusi**

### Alur Komunikasi Client-Server
`Klien (Web SPA / Mobile / Postman)`
`   │ (HTTP JSON REST API / Authorization Bearer JWT)`
`   ▼`
`[JobHarbour API Gateway & Application Layer]`
`   ├─ Service 1: Auth & Profile Service ──► db_auth`
`   ├─ Service 2: Job Catalog Service ──────► db_jobs`
`   ├─ Service 3: Application & Matchmaking ─► db_applications`
`   └─ Service 4: Recruitment Pipeline ─────► db_pipeline`
`   │`
`   ▼ (External HTTP Adapter - Resilient with Timeout & Fallback)`
`[Layanan Pendukung Eksternal: Geocoding & Document Storage]`

- **Pola Arsitektur:** *Database-per-Service* (tanpa join tabel lintas basis data).
- **Protokol Pertukaran:** REST API murni berstandar RFC 8259, header wajib `Accept: application/json`.

---

# SLIDE 10: MODEL DATA (ERD)
**02. ANALISIS & PERANCANGAN: Entitas & Relasi Data**

### Entitas Inti Sistem
1. `users` (id, name, email [UNIQUE], password_hash, role)
2. `profiles` (id, user_id [FK], phone, bio, location)
3. `user_skills` (id, user_id [FK], skill_name, level, years_exp)
4. `job_vacancies` (id, title, company, description, location, salary_min, salary_max, status, posted_by [FK])
5. `job_requirements` (id, job_id [FK], skill, weight [1-3], level [required/important/nice_to_have])
6. `applications` (id, job_id [FK], user_id [FK], match_score, match_category, cv_file_path, status)
7. `pipelines` (id, application_id [FK], current_stage, notes)
8. `pipeline_transitions` (id, pipeline_id [FK], stage, status, transition_at, notes, scheduled_at)

### Integritas & Constraint Penting
- **Constraint Unik:** `users.email` unik; pelamar hanya boleh mengajukan 1 berkas per lowongan (`unique(job_id, user_id)`).
- **Integritas Relasi:** Validasi relasi data lintas service diverifikasi secara ketat melalui logic layer API.

---

# SLIDE 11: RESOURCE & ENDPOINT INTI
**02. ANALISIS & PERANCANGAN: Matriks 9 Endpoint Utama**

| Method | URI Endpoint | Fungsi Bisnis | Kode Sukses | Kode Gagal |
|:---:|---|---|:---:|:---:|
| `POST` | `/api/auth/login` | Otentikasi pengguna & penerbitan token JWT | `200 OK` | `401, 422` |
| `PUT` | `/api/profile/skills` | Menyimpan kumpulan skill keahlian pelamar | `200 OK` | `401, 422` |
| `GET` | `/api/jobs` | Katalog lowongan kerja (filter, pencarian, paginasi) | `200 OK` | `422` |
| `GET` | `/api/jobs/my` | Daftar lowongan milik rekruter yang sedang login | `200 OK` | `401, 403` |
| `POST` | `/api/jobs` | Publikasi lowongan kerja baru beserta bobot skill | `201 Created` | `401, 403, 422` |
| `POST` | `/api/applications` | Mengirim lamaran + hitung skor + upload CV PDF | `201 Created` | `401, 409, 422` |
| `GET` | `/api/applications/{id}/cv` | Mengunduh / membaca file berkas resume CV (PDF) | `200 OK` | `401, 403, 404` |
| `DELETE`| `/api/applications/{id}` | Membatalkan lamaran (hanya jika tahap awal) | `200 OK` | `401, 403, 409` |
| `PATCH` | `/api/pipelines/{id}/status` | Update tahap kandidat (dengan proteksi anti-IDOR) | `200 OK` | `401, 403, 422` |

---

# SLIDE 12: KONTRAK OPENAPI (OAS 3.1.0)
**02. ANALISIS & PERANCANGAN: Spesifikasi Formal API**

### Informasi Spesifikasi
- **Berkas Kontrak:** `docs/openapi.yaml` (Standar OpenAPI Specification v3.1.0).
- **Kepatuhan Redocly:** Lolos pengujian lint otomatis (*Redocly CLI*) dengan status **0 errors / 0 warnings**.
- **Dokumentasi Interaktif:** Swagger UI terintegrasi langsung pada rute lokal `/docs` dan `/api/documentation`.

### Cuplikan Kontrak YAML
```yaml
openapi: 3.1.0
info:
  title: JobHarbour Core REST API
  version: 1.1.0
  description: Kontrak REST API platform rekrutmen terintegrasi Kelompok 4 UHN Tegal.
paths:
  /api/applications:
    post:
      summary: Submit lamaran baru dan hitung skor matchmaking
      security:
        - BearerAuth: []
      requestBody:
        content:
          multipart/form-data:
            schema:
              type: object
              required: [job_id]
              properties:
                job_id: { type: integer }
                cover_letter: { type: string }
                cv_file: { type: string, contentMediaType: application/pdf }
      responses:
        '201':
          description: Berkas lamaran berhasil dibuat beserta kalkulasi skor.
        '409':
          description: Konflik aturan bisnis (sudah melamar atau lowongan tutup).
```

---

# SLIDE 13: FORMAT RESPONSE & ERROR
**02. ANALISIS & PERANCANGAN: Standardisasi JSON Envelope**

### Format Sukses Standar (HTTP 200 / 201)
```json
{
  "status": "success",
  "message": "Data lowongan kerja berhasil dimuat.",
  "data": { "id": 1, "title": "Backend Engineer", "company": "PT Solusi Bahari" },
  "meta": { "timestamp": "2026-10-30T09:00:00+07:00", "version": "1.0.0" }
}
```

### Format Error Terstandarisasi (HTTP 4xx / 5xx)
```json
{
  "status": "error",
  "message": "Akses ditolak. Anda bukan pemilik dari lowongan kerja ini.",
  "error_code": "IDOR_FORBIDDEN",
  "data": null,
  "errors": { "auth": ["Resource ownership verification failed."] },
  "meta": { "timestamp": "2026-10-30T09:00:00+07:00", "version": "1.0.0" }
}
```

### Pemetaan Kode Status HTTP
- `200 OK`: Pengambilan data, pembaruan, dan pembatalan sukses.
- `201 Created`: Pembuatan resource baru (lowongan, lamaran) + header *Location*.
- `401 Unauthorized`: Token JWT tidak ada (`MISSING_BEARER_TOKEN`) atau habis masa berlaku (`TOKEN_EXPIRED`).
- `403 Forbidden`: Hak akses role ditolak atau upaya manipulasi IDOR (`IDOR_FORBIDDEN`).
- `404 Not Found`: Data entitas tidak ditemukan di database.
- `409 Conflict`: Benturan aturan bisnis / pembatalan ditolak pada tahap lanjut (`CANCELLATION_NOT_ALLOWED`).
- `422 Unprocessable Entity`: Kegagalan validasi masukan form request.

---

# SLIDE 14: PEMBATAS 03 - IMPLEMENTASI & PENGUJIAN
## 03. Implementasi & Pengujian
*"Arsitektur berlapis Laravel, atomic transaction, proteksi anti-IDOR, adapter API eksternal, dan validasi automated test."*

---

# SLIDE 15: STRUKTUR BERLAPIS LARAVEL
**03. IMPLEMENTASI: Pola Arsitektur Berlapis & Bersih**

### Pemisahan Tanggung Jawab (*Separation of Concerns*)
1. **Route Layer (`routes/api.php`):** Registrasi endpoint REST API dengan pengelompokan prefix dan middleware keamanan `jwt.auth`.
2. **Form Request Layer (`app/Http/Requests`):** Validasi ketat tipe data, ukuran file PDF (maks. 2 MB), dan aturan otorisasi masukan.
3. **Controller Layer (`app/Http/Controllers/Api`):** Controller tipis (*skinny controller*) yang hanya bertugas mendistribusikan request ke service.
4. **Service Layer (`app/Services`):** Tempat seluruh logika bisnis utama berada (`MatchmakingService`, `JwtService`).
5. **Model Layer (`app/Models`):** Definisi relasi Eloquent ORM, `$fillable`, dan proteksi mass assignment.
6. **API Response Trait (`app/Traits/ApiResponse.php`):** Standardisasi konsisten seluruh response envelope JSON.

### Pohon Direktori Kode Inti
```text
app/
├── Http/
│   ├── Controllers/Api/ (AuthController, JobVacancyController, ApplicationController, PipelineController)
│   └── Middleware/      (JwtAuth - Verifikasi Stateless JWT & Anti-IDOR)
├── Models/              (User, JobVacancy, Application, Pipeline, PipelineTransition)
├── Services/            (MatchmakingService, JwtService, ExternalLocationService)
└── Traits/              (ApiResponse - Amplop JSON Terpadu)
```

---

# SLIDE 16: ATURAN BISNIS & TRANSAKSI
**03. IMPLEMENTASI: Formula Matchmaking & Atomic DB Transaction**

### Formula Mesin Kecocokan (*Weighted Jaccard Similarity*)
$$\text{Match Score} = \frac{\sum \text{Bobot Keahlian yang Cocok}}{\sum \text{Bobot Seluruh Keahlian yang Disyaratkan}} \times 100\%$$
- Pembobotan: `required = 3`, `important = 2`, `nice_to_have = 1`.
- Kategori Otomatis: `excellent` (≥ 80%), `good` (50% - 79%), `under_qualified` (< 50%).

### Alur Eksekusi Database Transaction (`DB::transaction`)
```php
return DB::transaction(function () use ($request, $user, $job) {
    // 1. Simpan berkas resume PDF ke storage
    $cvPath = $request->file('cv_file')->store('resumes', 'public');
    
    // 2. Buat entitas lamaran dengan lock database
    $application = Application::create([
        'job_id' => $job->id, 'user_id' => $user->id,
        'cv_file_path' => $cvPath, 'status' => 'submitted'
    ]);
    
    // 3. Panggil Matchmaking Service dan hitung skor
    $matchResult = $this->matchmakingService->calculate($userSkills, $jobRequirements);
    $application->update(['match_score' => $matchResult['score']]);
    
    // 4. Inisialisasi tahapan pipeline awal secara atomik
    Pipeline::create(['application_id' => $application->id, 'current_stage' => 'screening']);
    return $application;
}, 3); // Coba ulang maksimal 3 kali jika terjadi deadlock
```

---

# SLIDE 17: INTEGRASI API EKSTERNAL
**03. IMPLEMENTASI: Pola Adapter Berketahanan Tinggi (*Resilient*)**

### Spesifikasi Layanan Integrasi
- **Layanan:** API Validasi Geocoding Wilayah & Status Operasional Dermaga Pelabuhan Tegal.
- **Tujuan:** Verifikasi otomatis jarak domisili pelamar terhadap lokasi penempatan kerja perusahaan.
- **Konfigurasi:** Terpusat di `config/services.php` dan file lingkungan `.env` (tanpa hardcoded URL).

### Parameter Ketahanan (*Resiliency Strategy*)
1. **Timeout Rendah (3 detik):** Mencegah *thread starvation* jika server pihak ketiga mengalami gangguan respons.
2. **Retry Otomatis (3 kali):** Mengulang permintaan dengan jeda interval 200 ms saat terjadi kendala jaringan sesaat.
3. **Penyimpanan Cache (12 Jam):** Menghemat kuota pemanggilan API eksternal dan mempercepat waktu muat request.
4. **Graceful Fallback (*Fail-Open*):** Jika API eksternal padam total, sistem beralih ke cache lokal atau nilai default aman sehingga transaksi lamaran pelamar tetap berjalan sukses.

---

# SLIDE 18: HASIL PENGUJIAN OTOMATIS
**03. PENGUJIAN: Validasi Mutu Kode (*Green Test Suite*)**

### Ringkasan Pengujian PHPUnit / Feature Tests
- **Total Pengujian:** **12 Test Cases** (Unit & Feature Tests).
- **Total Pernyataan (*Assertions*):** **68 Assertions**.
- **Status Akhir:** **100% Lulus (PASS)** dalam waktu eksekusi **0,50 detik**.

### Matriks Pengujian Skenario Kunci
1. `test_auth_service_login_and_token_generation`: Validasi login kredensial benar vs salah (200 & 401).
2. `test_job_catalog_jobs_list_and_filter`: Verifikasi pencarian teks dan paginasi data lowongan (200).
3. `test_job_catalog_recruiter_post_job`: Validasi otorisasi recruiter dalam menerbitkan lowongan (201 & 403).
4. `test_job_catalog_recruiter_my_jobs_update_and_delete`: CRUD lowongan mandiri recruiter (200).
5. `test_application_matchmaking_apply_and_scoring`: Pembuktian kalkulasi formula Weighted Jaccard akurat (201).
6. `test_application_upload_cv_pdf_and_download`: Pengujian upload file PDF dan download otentikasi (200 & 201).
7. `test_pipeline_idor_protection_blocks_unauthorized_recruiter`: Memblokir rekruter luar yang mencoba mengubah status (403 `IDOR_FORBIDDEN`).
8. `test_application_cancellation_prevented_when_already_in_interview`: Menolak pembatalan pada tahap interview (409 `CANCELLATION_NOT_ALLOWED`).
9. `test_jwt_expired_or_invalid_returns_standardized_error_code`: Menangani token kedaluwarsa dengan pesan seragam (401 `TOKEN_EXPIRED`).

---

# SLIDE 19: KOLABORASI & VERSION CONTROL
**03. MANAJEMEN PROYEK: Alur Kerja Git & GitHub Actions**

### Rekam Jejak Kolaborasi Tim (Kelompok 4)
- **Branching Strategy:** Mengadopsi alur kerja `feature/` dan `fix/` dengan perlindungan cabang `main`.
- **Statistik Kolaborasi:** Seluruh 4 anggota memiliki riwayat *pull request*, *review approval*, dan penyelesaian *issue* sesuai milestone.
- **GitHub Actions (CI/CD):** Pipeline otomatis terpicu pada setiap push/PR untuk menjalankan migrasi database dan pengujian `./dev test` (Status: *Passing* hijau).
- **Rilis Resmi:** **Tag Release `v1.1.0`** menandai pencapaian penuh Milestone M3 (Core REST API).

### Alur Siklus Kerja (*Workflow*)
`Issue Terdata` ──► `Feature Branch` ──► `Pull Request` ──► `Peer Review & Approval` ──► `Automated Test CI` ──► `Merged to Main`

---

# SLIDE 20: SKENARIO DEMO LANGSUNG
**03. DEMO SISTEM: Skenario Pengujian Pertemuan 8 (Maks. 8 Menit)**

1. **Pemeriksaan Repositori & CI (Pembicara: Affif):**
   - Menampilkan rilis `v1.1.0` pada GitHub dan status kelulusan pipeline GitHub Actions.
2. **Eksekusi Otomatis Test Suite (Pembicara: Yasir):**
   - Menjalankan `./dev test` di terminal hingga 12 skenario feature test lulus hijau (0.5 detik).
3. **Demo Endpoint Autentikasi & Profil (Pembicara: Nabe'ela):**
   - Registrasi, login pelamar, penerbitan token JWT, dan pembaruan keahlian profil (`PUT /api/profile/skills`).
4. **Demo Lowongan & Filter Paginasi (Pembicara: Fahren):**
   - Eksplorasi katalog lowongan (`GET /api/jobs?location=Tegal`), posting lowongan recruiter (`POST /api/jobs`), dan pencegahan validasi kosong (422).
5. **Demo Matchmaking & Upload CV PDF (Pembicara: Yasir):**
   - Pengiriman berkas lamaran dengan berkas PDF (`POST /api/applications`), verifikasi skor Jaccard 83.3%, dan pengunduhan kembali file CV (`GET /api/applications/{id}/cv`).
6. **Demo Proteksi Anti-IDOR & Aturan Pembatalan (Pembicara: Affif):**
   - Rekruter B mencoba memajukan lamaran Rekruter A -> diblokir `403 IDOR_FORBIDDEN`.
   - Pelamar membatalkan lamaran yang sudah masuk tahap wawancara -> diblokir `409 CANCELLATION_NOT_ALLOWED`.

---

# SLIDE 21: PEMBATAS 04 - PENUTUP
## 04. Penutup
*"Evaluasi capaian milestone, penanganan kendala teknis, dan rencana pengembangan pasca-UTS (Milestone M4)."*

---

# SLIDE 22: KENDALA, EVALUASI & RENCANA
**04. PENUTUP: Refleksi & Roadmap Milestone M4**

### Kendala Teknis & Solusi Terapan
- **Tantangan Validasi Multipart Form Data:** Pengiriman array skill bersamaan dengan upload file biner PDF. *Solusi:* Implementasi deserialisasi defensif pada Form Request.
- **Potensi Celah Akses IDOR:** Rekruter dapat memanipulasi pelamar milik perusahaan lain. *Solusi:* Pengetatan *resource ownership check* di level controller dan middleware.

### Evaluasi Capaian Tim
- Seluruh target **Milestone M1 (Proposal)**, **M2 (API Blueprint)**, dan **M3 (Core API)** telah terselesaikan 100% tepat waktu sesuai rubrik perkuliahan.

### Rencana Pengembangan Lanjutan (Milestone M4 - Secure API)
1. Penerapan rotasi *Refresh Token* otomatis dan mekanisme *Blacklisting Token*.
2. Implementasi *Rate Limiting* adaptif berbasis IP dan API Key guna mencegah serangan *Denial-of-Service (DoS)*.
3. Integrasi sistem notifikasi asynchronous (Email / WhatsApp Gateway) saat kandidat dinyatakan lolos ke tahap interview.

---

# SLIDE 23: KESIMPULAN
**04. PENUTUP: Rangkuman Ketercapaian Proyek**

1. **Ketercapaian M1 (Konsep & Problem):** Masalah transparansi rekrutmen berhasil dirumuskan dan diselesaikan dengan arsitektur web service terintegrasi.
2. **Ketercapaian M2 (Perancangan & Kontrak):** Menghasilkan 40 rancangan endpoint berstandar OpenAPI 3.1.0 yang tervalidasi tanpa error.
3. **Ketercapaian M3 (Implementasi & Mutu):** Core REST API beroperasi dengan struktur berlapis, atomic transaction, upload PDF, dan 100% lolos automated test.
4. **Nilai Tambah Nyata:** Formula *Weighted Jaccard Similarity* memberikan penilaian objektif bagi pelamar dan efisiensi waktu kerja bagi rekruter.

**Tautan Repositori Resmi:**
`https://github.com/Nourivex/JobHarbour` · Release `v1.1.0`

---

# SLIDE 24: TERIMA KASIH & TANYA JAWAB
## Terima Kasih!
**"JobHarbour: Berlabuh dengan Kesiapan, Berlayar Menuju Kesuksesan Profesional"**

**Kelompok 4 - S1 Teknik Informatika**
Fakultas Sains dan Teknologi, Universitas Harkat Negeri (Tegal)
*Dosen Pengampu: Zaenul Arif, S.Kom., M.Kom.*

---
*Silakan ajukan pertanyaan pada sesi tanya jawab.*
