# API Design Document v1: PaduKerja

> **Platform Rekrutmen Terintegrasi Berbasis Microservice**  
> *Automated Skill-Matchmaking Engine & Multi-Stage Pipeline Tracker*  
> Mata Kuliah: Pemrograman Web Service (5537344 / 3 SKS) - Universitas Harkat Negeri  
> Dosen Pengampu: Zaenul Arif, S.Kom., M.Kom.  
> Bagian Pertama Milestone M2 - API Blueprint (Minggu ke-4)  

---

## A. Identitas Proyek & Konvensi

### 1. Informasi Tim Pengembang (Kelompok 4)

| No | Nama Mahasiswa | NIM | Tanggung Jawab Microservice |
|:---:|---|:---:|---|
| 1 | **M. Fahren Andrean Rangkuti** | `23215030` | Job Catalog Service (`db_jobs` / `:8002`) |
| 2 | **Muhammad Yasir Ilham Nabil** | `23215040` | Application & Matchmaking Service (`db_applications` / `:8003`) |
| 3 | **Nabe'ela Ayu Ning Tyas Zahra** | `23215052` | Auth & Profile Service (`db_auth` / `:8001`) |
| 4 | **Muhammad Affif** | `24225046` | Recruitment Pipeline Service (`db_pipeline` / `:8004`) |

### 2. Lingkungan, Base URL & Versi

| Komponen | Base URL / Host | Keterangan |
|---|---|---|
| **API Gateway (Unified)** | `http://localhost:8000/api/v1` | Reverse proxy rute lintas layanan (titik masuk client) |
| **Auth & Profile Service** | `http://localhost:8001/api/v1` | Layanan autentikasi & profil pengguna |
| **Job Catalog Service** | `http://localhost:8002/api/v1` | Layanan manajemen lowongan kerja |
| **Application Service** | `http://localhost:8003/api/v1` | Layanan berkas lamaran & matchmaking |
| **Pipeline Service** | `http://localhost:8004/api/v1` | Layanan linimasa tahapan seleksi |

- **Versi API:** `v1`, dipasang sebagai prefix URI `/api/v1` pada seluruh endpoint. Perubahan yang tidak kompatibel akan dirilis sebagai `/api/v2`.
- Seluruh URI pada dokumen ini ditulis relatif terhadap base URL Gateway.

### 3. Konvensi Penamaan (Naming Conventions)
- **Bahasa penamaan:** Bahasa **Inggris** untuk nama resource, atribut, dan parameter. Pesan (`message`) untuk pengguna ditulis dalam Bahasa Indonesia.
- **URI Path:** Kata benda jamak huruf kecil (*lowercase plural nouns*), kebab-case untuk multi-kata, tanpa kata kerja, nesting maksimal 2 tingkat. Contoh: `/api/v1/jobs`, `/api/v1/jobs/{id}/applications`.
- **JSON Keys & Attributes:** *snake_case*, contoh: `employment_type`, `match_score`, `created_at`.
- **Query Parameters:** *snake_case*, contoh: `?per_page=15&salary_min=8000000`.
- **Format Waktu:** ISO 8601 dengan zona waktu, contoh: `2026-10-16T17:45:00+07:00`.
- **Format Pertukaran Data:** JSON (RFC 8259) dengan header wajib `Content-Type: application/json` dan `Accept: application/json`.
- **Otentikasi:** JSON Web Token (JWT) melalui header `Authorization: Bearer <token>`.

---

## B. Pemodelan Resource & Relasi

Sistem PaduKerja dimodelkan ke dalam 11 resource (10 resource data + 1 singleton `me`) yang tersebar di 4 service.

### 1. Spesifikasi Atribut Resource

| Resource | Service | Jenis | Atribut Utama & Tipe Data | Deskripsi Singkat |
|---|---|---|---|---|
| **`users`** | Auth | Collection | `id` (bigint), `name` (string), `email` (string, unik), `password_hash` (string, tidak pernah diekspos), `role` (enum: applicant, recruiter, admin), `created_at` (datetime) | Akun pengguna dan hak akses. |
| **`me`** | Auth | Singleton | gabungan `users` + `profiles` milik pengguna yang login | Profil pengguna aktif (`/me`). |
| **`profiles`** | Auth | 1:1 dengan users | `id` (bigint), `user_id` (bigint), `phone` (string), `bio` (text), `location` (string), `portfolio_url` (string) | Data personal pelengkap akun. |
| **`skills`** | Auth | Collection | `id` (bigint), `name` (string, unik), `category` (string) | Katalog referensi kompetensi. |
| **`user_skills`** | Auth | Sub-resource (`/me/skills`) | `user_id` (bigint), `skill_id` (bigint), `level` (enum: beginner, intermediate, advanced), `years_exp` (integer) | Pivot keahlian milik pengguna. |
| **`jobs`** | Job Catalog | Collection | `id` (bigint), `title` (string), `company_name` (string), `description` (text), `location` (string), `employment_type` (enum: full-time, part-time, contract, internship), `salary_min` (integer), `salary_max` (integer), `quota` (integer), `status` (enum: open, closed), `posted_by` (bigint), `created_at` (datetime) | Lowongan yang diterbitkan recruiter. |
| **`job_requirements`** | Job Catalog | Bagian dari `jobs` | `id` (bigint), `job_id` (bigint), `skill_name` (string), `level` (enum: required, important, nice_to_have), `weight` (integer: 3/2/1) | Syarat keahlian lowongan beserta bobot matchmaking. |
| **`applications`** | Application | Collection | `id` (bigint), `job_id` (bigint), `user_id` (bigint), `cv_file_path` (string), `cover_letter` (text), `match_score` (decimal 5,2), `match_category` (enum: excellent, good, under_qualified), `status` (enum: submitted, in_review, withdrawn), `applied_at` (datetime) | Berkas lamaran pelamar pada satu lowongan. |
| **`pipelines`** | Pipeline | Collection | `id` (bigint), `application_id` (bigint), `current_stage` (enum: screening, assessment, interview, offering, rejected), `created_at` (datetime), `updated_at` (datetime) | Status seleksi bertahap seorang kandidat. |
| **`pipeline_transitions`** | Pipeline | Sub-resource (`/pipelines/{id}/transitions`) | `id` (bigint), `pipeline_id` (bigint), `from_stage` (enum), `to_stage` (enum), `notes` (text), `changed_by` (bigint), `created_at` (datetime) | Riwayat perpindahan tahap beserta timestamp (audit trail). |
| **`interviews`** | Pipeline | Sub-resource (`/pipelines/{id}/interviews`) | `id` (bigint), `pipeline_id` (bigint), `scheduled_at` (datetime), `type` (enum: online, offline), `meeting_url` (string), `interviewer` (string), `status` (enum: scheduled, completed, cancelled) | Jadwal wawancara kandidat. |

### 2. Relasi

| Relasi | Kardinalitas | Keterangan |
|---|:---:|---|
| users - profiles | 1 : 1 | Satu akun memiliki satu profil. |
| users - skills (via user_skills) | N : M | Pengguna memiliki banyak keahlian. |
| users (recruiter) - jobs | 1 : N | Recruiter menerbitkan banyak lowongan. |
| jobs - job_requirements | 1 : N | Lowongan memiliki banyak syarat keahlian. |
| jobs - applications | 1 : N | Lowongan menerima banyak lamaran. |
| users (applicant) - applications | 1 : N | Pelamar mengajukan banyak lamaran (maks. 1 per lowongan). |
| applications - pipelines | 1 : 1 | Pipeline dibuat otomatis saat lamaran dibuat. |
| pipelines - pipeline_transitions | 1 : N | Setiap perpindahan tahap dicatat. |
| pipelines - interviews | 1 : N | Satu kandidat dapat dijadwalkan beberapa wawancara. |

Relasi lintas service (misal `applications.job_id` ke `jobs.id`) hanya berupa referensi ID. Tidak ada foreign key lintas database; validasi dilakukan melalui REST API antar-service.

### 3. Diagram Relasi Entitas (ERD Ringkas)

![ERD PaduKerja](materi/minggu-4/diagrams/resource_relationship_erd.svg)

Versi teks:

```text
 [Auth Service]                                [Job Catalog Service]
+-------------+  1:1  +-------------+          +-------------+  1:N  +------------------+
|    users    |-------|  profiles   |          |    jobs     |-------| job_requirements |
+-------------+       +-------------+          +-------------+       +------------------+
   |  1:N                                         ^     | 1:N
   |            +-------------+  N:1  +--------+  |     |
   +------------| user_skills |-------| skills |  |     |
   |            +-------------+       +--------+  |     |
   |  1:N (recruiter memposting) -----------------+     |
   |                                                    |
   |  1:N (applicant melamar)                           |
   |                    [Application Service]           |
   +----------------->  +----------------+ <------------+
                        |  applications  |
                        +----------------+
                                | 1:1
                    [Pipeline Service]
                        +----------------+  1:N  +----------------------+
                        |   pipelines    |-------| pipeline_transitions |
                        +----------------+       +----------------------+
                                | 1:N
                        +----------------+
                        |   interviews   |
                        +----------------+
```

---

## C. Matriks Endpoint (21 Endpoint)

Keterangan status: setiap `POST` yang membuat resource mengembalikan `201 Created` beserta header `Location`; setiap `DELETE` mengembalikan `204 No Content` tanpa body.

| No | Method | URI Endpoint | Fungsi Bisnis | Aktor | Status Sukses | Status Gagal |
|:---:|:---:|---|---|:---:|:---:|---|
| 1 | `POST` | `/api/v1/auth/register` | Mendaftarkan akun baru | Public | `201` | `409` email terdaftar, `422` |
| 2 | `POST` | `/api/v1/auth/login` | Verifikasi kredensial & terbitkan JWT | Public | `200` | `401` kredensial salah, `422` |
| 3 | `POST` | `/api/v1/auth/logout` | Mencabut (blacklist) token aktif | Semua (login) | `204` | `401` |
| 4 | `GET` | `/api/v1/me` | Profil pengguna yang sedang login | Semua (login) | `200` | `401` |
| 5 | `PUT` | `/api/v1/me/skills` | Mengganti seluruh daftar keahlian pengguna | Applicant | `200` | `401`, `403`, `422` |
| 6 | `GET` | `/api/v1/skills` | Katalog keahlian (untuk autocomplete) | Public | `200` | `422` query tidak valid |
| 7 | `GET` | `/api/v1/jobs` | Daftar lowongan + filter, search, sort, pagination | Public | `200` | `422` query tidak valid |
| 8 | `GET` | `/api/v1/jobs/{id}` | Detail lowongan beserta requirements | Public | `200` | `404` |
| 9 | `POST` | `/api/v1/jobs` | Menerbitkan lowongan baru | Recruiter | `201` | `401`, `403`, `422` |
| 10 | `PUT` | `/api/v1/jobs/{id}` | Mengganti seluruh data lowongan | Recruiter (pemilik) | `200` | `401`, `403`, `404`, `422` |
| 11 | `PATCH` | `/api/v1/jobs/{id}` | Memperbarui sebagian field (misal `status: closed`) | Recruiter (pemilik) | `200` | `401`, `403`, `404`, `422` |
| 12 | `DELETE` | `/api/v1/jobs/{id}` | Menghapus lowongan | Recruiter (pemilik), Admin | `204` | `401`, `403`, `404`, `409` masih ada lamaran aktif |
| 13 | `POST` | `/api/v1/applications` | Mengirim lamaran & menghitung skor matchmaking | Applicant | `201` | `401`, `403`, `404` job tidak ada, `409` sudah melamar / lowongan ditutup, `422` |
| 14 | `GET` | `/api/v1/applications` | Riwayat lamaran milik pelamar | Applicant | `200` | `401`, `422` |
| 15 | `GET` | `/api/v1/applications/{id}` | Detail lamaran & rincian skor | Applicant (pemilik), Recruiter (pemilik lowongan) | `200` | `401`, `403`, `404` |
| 16 | `DELETE` | `/api/v1/applications/{id}` | Menarik (withdraw) lamaran | Applicant (pemilik) | `204` | `401`, `403`, `404`, `409` sudah tahap interview |
| 17 | `GET` | `/api/v1/jobs/{id}/applications` | Daftar pelamar satu lowongan, default urut skor tertinggi | Recruiter (pemilik) | `200` | `401`, `403`, `404`, `422` |
| 18 | `GET` | `/api/v1/pipelines/{id}` | Status tahap seleksi kandidat saat ini | Applicant (pemilik), Recruiter | `200` | `401`, `403`, `404` |
| 19 | `POST` | `/api/v1/pipelines/{id}/transitions` | Memindahkan kandidat ke tahap berikutnya / rejected | Recruiter | `201` | `401`, `403`, `404`, `409` transisi tidak sah, `422` |
| 20 | `GET` | `/api/v1/pipelines/{id}/transitions` | Riwayat perpindahan tahap (timeline) | Applicant (pemilik), Recruiter | `200` | `401`, `403`, `404` |
| 21 | `POST` | `/api/v1/pipelines/{id}/interviews` | Menjadwalkan wawancara kandidat | Recruiter | `201` | `401`, `403`, `404`, `409` kandidat belum di tahap interview, `422` |

Catatan otorisasi: `GET /api/v1/applications` secara implisit hanya mengembalikan lamaran milik pelamar yang login. Aturan ini ditegakkan oleh server, bukan oleh parameter dari client.

---

## D. Desain Query: Filtering, Searching, Sorting, & Pagination

Aturan umum untuk semua endpoint collection:
- **Model pagination:** offset/page-based (`page`, `per_page`), sesuai `paginate()` Laravel.
- **Whitelist:** hanya parameter dan field yang tercantum di tabel yang diterima. Parameter tidak dikenal diabaikan; nilai tidak valid (misal `sort=password`, `per_page=500`, `page=0`) ditolak dengan `422`.
- **Urutan default deterministik:** selalu ditambah `id` sebagai pemutus seri agar halaman tidak "melompat".
- **Metadata:** dikirim di body melalui `meta` dan `links` (lihat bagian E).

### 1. `GET /api/v1/jobs`

| Parameter | Jenis | Tipe | Contoh | Aturan, Default & Batas |
|---|---|---|---|---|
| `q` | Search | string | `?q=backend` | Dicari pada `title` dan `description`; maks. 80 karakter. |
| `location` | Filter | string | `?location=jakarta` | Pencocokan tepat (case-insensitive). |
| `employment_type` | Filter | enum (multi) | `?employment_type=full-time,contract` | `full-time`, `part-time`, `contract`, `internship`; dipisah koma. |
| `salary_min` | Filter rentang | integer | `?salary_min=8000000` | >= 0; lowongan dengan `salary_max` >= nilai ini. |
| `status` | Filter | enum | `?status=open` | `open`, `closed`. Default: `open`. |
| `sort` | Sort | string | `?sort=-salary_min` | Whitelist: `created_at`, `salary_min`, `salary_max`, `title`. Awalan `-` = descending. Default: `-created_at,id`. |
| `page` | Pagination | integer | `?page=2` | Default `1`, minimum `1`. |
| `per_page` | Pagination | integer | `?per_page=20` | Default `15`, maksimum `50`. |

Contoh gabungan:
```
GET /api/v1/jobs?q=laravel&location=jakarta&employment_type=full-time&salary_min=8000000&sort=-salary_min&page=1&per_page=10
```

### 2. `GET /api/v1/jobs/{id}/applications`

| Parameter | Jenis | Tipe | Contoh | Aturan, Default & Batas |
|---|---|---|---|---|
| `match_category` | Filter | enum (multi) | `?match_category=excellent,good` | `excellent` (80-100), `good` (50-79), `under_qualified` (0-49). |
| `status` | Filter | enum | `?status=submitted` | `submitted`, `in_review`, `withdrawn`. Default: semua kecuali `withdrawn`. |
| `score_min` | Filter rentang | number | `?score_min=60` | 0-100. |
| `sort` | Sort | string | `?sort=-match_score` | Whitelist: `match_score`, `applied_at`. Default: `-match_score,applied_at,id` (skor tertinggi, lalu pelamar paling awal). |
| `page` | Pagination | integer | `?page=1` | Default `1`, minimum `1`. |
| `per_page` | Pagination | integer | `?per_page=10` | Default `10`, maksimum `30`. |

### 3. `GET /api/v1/applications` (riwayat milik pelamar)

| Parameter | Jenis | Contoh | Aturan, Default & Batas |
|---|---|---|---|
| `status` | Filter | `?status=submitted,in_review` | `submitted`, `in_review`, `withdrawn`. |
| `sort` | Sort | `?sort=-applied_at` | Whitelist: `applied_at`, `match_score`. Default: `-applied_at,id`. |
| `page` / `per_page` | Pagination | `?page=1&per_page=15` | Default `1` / `15`, maksimum `per_page` = `50`. |

---

## E. Desain Representasi Data (JSON Envelope)

Seluruh response sukses memakai envelope `status`, `message`, `data`, `meta`; response collection menambahkan `links`. Representasi hanya memuat field yang dibutuhkan client (misal `password_hash` tidak pernah dikirim).

### 1. Item: `GET /api/v1/jobs/12` (`200 OK`)

```json
{
  "status": "success",
  "message": "Detail lowongan berhasil diambil",
  "data": {
    "id": 12,
    "title": "Backend Developer",
    "company_name": "PT Teknologi Maju",
    "description": "Membangun REST API microservices dengan Laravel dan MariaDB.",
    "location": "Jakarta",
    "employment_type": "full-time",
    "salary_range": { "min": 9000000, "max": 15000000, "currency": "IDR" },
    "quota": 3,
    "status": "open",
    "requirements": [
      { "id": 1, "skill_name": "PHP", "level": "required", "weight": 3 },
      { "id": 2, "skill_name": "Laravel", "level": "required", "weight": 3 },
      { "id": 3, "skill_name": "MySQL", "level": "important", "weight": 2 }
    ],
    "created_at": "2026-10-11T09:00:00+07:00",
    "updated_at": "2026-10-11T09:00:00+07:00"
  },
  "meta": { "timestamp": "2026-10-16T17:45:00+07:00", "version": "v1" }
}
```

### 2. Collection: `GET /api/v1/jobs?page=2&per_page=2` (`200 OK`)

```json
{
  "status": "success",
  "message": "Daftar lowongan berhasil diambil",
  "data": [
    { "id": 13, "title": "Frontend Engineer", "company_name": "PT Solusi Digital", "location": "Bandung", "salary_min": 8500000, "status": "open" },
    { "id": 14, "title": "QA Engineer", "company_name": "PT Kode Nusantara", "location": "Tegal", "salary_min": 6000000, "status": "open" }
  ],
  "links": {
    "first": "http://localhost:8000/api/v1/jobs?page=1&per_page=2",
    "last": "http://localhost:8000/api/v1/jobs?page=9&per_page=2",
    "prev": "http://localhost:8000/api/v1/jobs?page=1&per_page=2",
    "next": "http://localhost:8000/api/v1/jobs?page=3&per_page=2"
  },
  "meta": {
    "timestamp": "2026-10-16T17:45:00+07:00",
    "version": "v1",
    "current_page": 2,
    "per_page": 2,
    "from": 3,
    "to": 4,
    "last_page": 9,
    "total": 17
  }
}
```

### 3. Create: `POST /api/v1/applications` (`201 Created`)

Request:
```json
{ "job_id": 12, "cover_letter": "Saya tertarik dengan posisi ini...", "cv_file_path": "cv/101-backend.pdf" }
```

Response header: `Location: /api/v1/applications/45`

```json
{
  "status": "success",
  "message": "Lamaran berhasil dikirim dan skor kecocokan telah dihitung",
  "data": {
    "id": 45,
    "job_id": 12,
    "user_id": 101,
    "match_score": 80.00,
    "match_category": "excellent",
    "matched_skills": ["PHP", "Laravel", "MySQL"],
    "missing_skills": ["Redis"],
    "status": "submitted",
    "pipeline_id": 30,
    "applied_at": "2026-10-16T17:45:05+07:00"
  },
  "meta": { "timestamp": "2026-10-16T17:45:05+07:00", "version": "v1" }
}
```

### 4. Collection: `GET /api/v1/jobs/12/applications?match_category=excellent&per_page=10` (`200 OK`)

```json
{
  "status": "success",
  "message": "Daftar pelamar berhasil diambil",
  "data": [
    { "id": 45, "user_id": 101, "applicant_name": "Rina Saputri", "match_score": 80.00, "match_category": "excellent", "status": "submitted", "applied_at": "2026-10-16T17:45:05+07:00" },
    { "id": 51, "user_id": 117, "applicant_name": "Dimas Pratama", "match_score": 88.89, "match_category": "excellent", "status": "in_review", "applied_at": "2026-10-17T08:10:00+07:00" }
  ],
  "links": {
    "first": "http://localhost:8000/api/v1/jobs/12/applications?match_category=excellent&page=1&per_page=10",
    "last": "http://localhost:8000/api/v1/jobs/12/applications?match_category=excellent&page=1&per_page=10",
    "prev": null,
    "next": null
  },
  "meta": { "timestamp": "2026-10-18T09:00:00+07:00", "version": "v1", "current_page": 1, "per_page": 10, "from": 1, "to": 2, "last_page": 1, "total": 2 }
}
```

### 5. Delete: `DELETE /api/v1/applications/45` (`204 No Content`)

Tidak ada body response. Client cukup memeriksa status code.

---

## F. Penanganan Error & Kode Status HTTP

Seluruh error memakai satu format: `status: "error"`, `message` (dibaca manusia), `code` (konstanta untuk logika client), `errors` (khusus 422, per field), dan `meta`.

### 1. Validasi: `POST /api/v1/jobs` (`422 Unprocessable Content`)

```json
{
  "status": "error",
  "message": "Data yang dikirim tidak valid.",
  "code": "VALIDATION_FAILED",
  "errors": {
    "title": ["Judul lowongan wajib diisi."],
    "quota": ["Kuota minimal bernilai 1."],
    "requirements": ["Minimal cantumkan satu syarat keahlian."]
  },
  "meta": { "timestamp": "2026-10-16T17:45:00+07:00", "version": "v1" }
}
```

### 2. Aturan Bisnis: `POST /api/v1/applications` (`409 Conflict`)

```json
{
  "status": "error",
  "message": "Anda sudah pernah melamar lowongan ini.",
  "code": "APPLICATION_ALREADY_EXISTS",
  "meta": { "timestamp": "2026-10-16T17:45:00+07:00", "version": "v1" }
}
```

### 3. Aturan Bisnis: `POST /api/v1/pipelines/30/transitions` (`409 Conflict`)

```json
{
  "status": "error",
  "message": "Kandidat tidak dapat dipindahkan dari screening langsung ke interview.",
  "code": "INVALID_STAGE_TRANSITION",
  "meta": { "timestamp": "2026-10-18T10:00:00+07:00", "version": "v1" }
}
```

### 4. Daftar Kode Error Bisnis

| `code` | Status | Kondisi |
|---|:---:|---|
| `VALIDATION_FAILED` | 422 | Field body atau query parameter tidak valid. |
| `UNAUTHENTICATED` | 401 | Token tidak ada, tidak valid, atau kedaluwarsa. |
| `FORBIDDEN` | 403 | Role atau kepemilikan tidak sesuai. |
| `RESOURCE_NOT_FOUND` | 404 | ID tidak ditemukan atau tidak boleh diketahui pengguna. |
| `EMAIL_ALREADY_REGISTERED` | 409 | Registrasi dengan email yang sudah ada. |
| `APPLICATION_ALREADY_EXISTS` | 409 | Pelamar melamar lowongan yang sama dua kali. |
| `JOB_CLOSED` | 409 | Melamar lowongan berstatus `closed`. |
| `JOB_HAS_ACTIVE_APPLICATIONS` | 409 | Menghapus lowongan yang masih memiliki lamaran aktif. |
| `WITHDRAWAL_NOT_ALLOWED` | 409 | Menarik lamaran yang sudah masuk tahap interview atau lebih. |
| `INVALID_STAGE_TRANSITION` | 409 | Transisi tahap melompat, mundur, atau dari tahap final. |

### 5. Ringkasan Penggunaan Status Code

| Kode | Nama | Penggunaan di PaduKerja |
|:---:|---|---|
| `200` | OK | GET, PUT, PATCH berhasil; login berhasil. |
| `201` | Created | Resource baru dibuat (`register`, `jobs`, `applications`, `transitions`, `interviews`) + header `Location`. |
| `204` | No Content | DELETE berhasil dan logout. |
| `400` | Bad Request | Body bukan JSON yang dapat di-parse. |
| `401` | Unauthorized | Token JWT tidak ada, tidak valid, atau kedaluwarsa; kredensial login salah. |
| `403` | Forbidden | Role tidak berhak (misal applicant membuat lowongan) atau bukan pemilik resource. |
| `404` | Not Found | ID resource tidak ditemukan. |
| `405` | Method Not Allowed | Method tidak didukung pada URI (misal `PUT /api/v1/applications/{id}`). |
| `409` | Conflict | Bertentangan dengan state saat ini (lihat daftar kode bisnis). |
| `422` | Unprocessable Content | Validasi field atau query parameter gagal. |
| `500` | Internal Server Error | Kesalahan tak terduga / gangguan komunikasi antar-service; stack trace tidak ditampilkan. |

---

## G. Perancangan Aksi Non-CRUD

Proses bisnis tidak dimodelkan sebagai endpoint kata kerja (`/calculateScore`, `/advanceCandidate`, `/rejectCandidate`), melainkan sebagai resource.

### 1. Matchmaking Engine (perhitungan skor)
- **Pola:** efek samping dari pembuatan resource (*computed on create*).
- **Endpoint:** `POST /api/v1/applications`
- **Alasan:** skor selalu dibutuhkan tepat saat lamaran dibuat, sehingga tidak perlu endpoint terpisah. Application Service mengambil skill pelamar dari Auth Service (`GET /me` dengan token pelamar) dan requirements dari Job Catalog Service (`GET /jobs/{id}`), menghitung **Weighted Jaccard Similarity**, lalu menyimpan `match_score` dan `match_category` pada lamaran:

  ```
  Score = (Σ bobot skill yang cocok) / (Σ bobot seluruh skill yang disyaratkan) × 100%
  bobot: required = 3, important = 2, nice_to_have = 1
  ```

  Setelah lamaran tersimpan, Application Service membuat pipeline melalui Pipeline Service dengan tahap awal `screening`.

### 2. Transisi Tahap Seleksi (Pipeline State Machine)
- **Pola:** sub-resource yang mencatat peristiwa (*state transition as resource*), sama seperti pola `/bookings/{id}/approvals` pada modul.
- **Endpoint:** `POST /api/v1/pipelines/{id}/transitions`
- **Request:** `{ "to_stage": "assessment", "notes": "Lolos screening awal" }`
- **Alasan:** setiap perpindahan tahap harus tercatat beserta waktu, pelaku, dan catatannya. Dengan membuat resource `transitions`, riwayat dapat dibaca kembali (`GET /pipelines/{id}/transitions`) dan `current_stage` pada pipeline diperbarui otomatis. Server memvalidasi state machine:

  ```
  screening -> assessment -> interview -> offering
      |             |             |
      +-------------+-------------+--> rejected
  ```
  Transisi melompat, mundur, atau dari tahap final (`offering`, `rejected`) ditolak dengan `409 INVALID_STAGE_TRANSITION`.

### 3. Penarikan Lamaran (Withdrawal)
- **Pola:** `DELETE` dengan *soft delete*.
- **Endpoint:** `DELETE /api/v1/applications/{id}` -> `204 No Content`
- **Alasan:** dari sudut pandang client, lamaran dihapus dari daftar aktifnya. Di server, baris tidak dihapus fisik; `status` diubah menjadi `withdrawn` agar audit trail recruiter tetap utuh. Penarikan ditolak (`409 WITHDRAWAL_NOT_ALLOWED`) bila kandidat sudah berada di tahap `interview` atau lebih.

---

## H. Catatan Keputusan Desain

| No | Keputusan | Alasan |
|:---:|---|---|
| 1 | Nama resource dan atribut berbahasa Inggris, pesan berbahasa Indonesia. | Selaras dengan istilah Laravel/HTTP; pesan tetap mudah dipahami pengguna. |
| 2 | Envelope seragam `status`, `message`, `data`, `meta` (+ `links` untuk collection; + `code`, `errors` untuk error). | Client cukup memiliki satu interceptor untuk parsing sukses, error, dan pagination. |
| 3 | `PUT` dan `PATCH` keduanya disediakan untuk `jobs`. | `PUT` untuk form edit lengkap; `PATCH` untuk perubahan kecil seperti menutup lowongan (`{"status":"closed"}`) tanpa endpoint `/status` terpisah. `PUT /me/skills` karena daftar skill selalu dikirim utuh. |
| 4 | `POST` -> `201` + `Location`; `DELETE` -> `204`. | Mengikuti semantik HTTP standar dan konvensi modul. |
| 5 | Pelanggaran aturan bisnis memakai `409` + `code`, bukan `400`. | `400` dicadangkan untuk request yang rusak secara format; `409` menunjukkan konflik dengan state resource. |
| 6 | Offset pagination (`page`, `per_page`) dengan default dan batas maksimum. | Recruiter butuh nomor halaman dan total pelamar; batas maksimum mencegah *Unrestricted Resource Consumption* (OWASP API4:2023). |
| 7 | Daftar pelamar terurut skor memakai `GET /jobs/{id}/applications?sort=-match_score`, bukan `/applications/ranked`. | Urutan adalah fungsi query parameter, bukan resource baru; menghindari URI berisi kata sifat/aksi. |
| 8 | Nesting maksimal 2 tingkat; akses detail memakai URI datar (`/applications/{id}`, `/pipelines/{id}`). | Menghindari URI panjang seperti `/jobs/12/applications/45/pipelines/30`. |
| 9 | Singleton `/me` untuk profil pengguna aktif. | ID pengguna diambil dari token, sehingga pengguna tidak dapat mengakses profil orang lain dengan mengganti ID. |
| 10 | Database-per-service; relasi lintas service hanya berupa ID dan diakses lewat REST. | Menjaga independensi deployment dan kepemilikan data tiap service. |
| 11 | Endpoint otentikasi tetap memakai `/auth/register`, `/auth/login`, `/auth/logout`. | Pengecualian yang lazim untuk otentikasi (mengikuti contoh `POST /auth/logout` pada modul); sesi/token bukan resource bisnis yang dikelola client. |

---

## I. Lampiran: Hasil Peer Review (Aktivitas 4)

> Diisi berdasarkan hasil peer review dengan kelompok lain di kelas. Kolom "Perbaikan" mencatat perubahan yang sudah dilakukan pada dokumen ini.

**Kelompok reviewer:** ..........................  **Tanggal:** ..........................

| No | Butir Pemeriksaan | Ya | Tidak | Catatan Reviewer | Perbaikan yang Dilakukan |
|:---:|---|:---:|:---:|---|---|
| 1 | Semua URI memakai kata benda jamak, huruf kecil, dan tanpa kata kerja | | | | |
| 2 | Prefix `/api/v1` dipakai konsisten | | | | |
| 3 | Method sesuai operasi; GET tidak mengubah data | | | | |
| 4 | POST -> 201, DELETE -> 204, dan status gagal relevan dicantumkan | | | | |
| 5 | Nesting maksimal 2 tingkat | | | | |
| 6 | Setiap collection memiliki pagination dengan batas maksimum | | | | |
| 7 | Filter/sort memakai query parameter dengan whitelist | | | | |
| 8 | Format error seragam | | | | |

### Riwayat Revisi Internal (sebelum peer review)

| Sebelum | Sesudah | Alasan |
|---|---|---|
| `DELETE` mengembalikan `200` | `204 No Content` | Butir checklist 4. |
| `GET /jobs/{jobId}/applications/ranked` | `GET /jobs/{id}/applications?sort=-match_score` | URI tanpa kata sifat/aksi; urutan lewat query (butir 1 & 7). |
| `PATCH /jobs/{id}/status` | `PATCH /jobs/{id}` | Perubahan parsial cukup lewat PATCH pada item. |
| `PATCH /pipelines/{id}/status` | `POST /pipelines/{id}/transitions` | Riwayat transisi tercatat sebagai resource. |
| `GET /auth/me`, `PUT /profile/skills` | `GET /me`, `PUT /me/skills` | Singleton sesuai konvensi modul. |
| Pelanggaran bisnis memakai `400` | `409` + `code` | `400` khusus request rusak. |
| Collection tanpa `links` | Ditambah `links` (first, last, prev, next) | Bagian E wajib memuat meta/links. |
