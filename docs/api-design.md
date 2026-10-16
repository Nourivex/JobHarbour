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
| 3 | **MUHAMMAD AFFIF** | `24225046` | Recruitment Pipeline Service (`db_pipeline` / `:8004`) |
| 4 | **Nabe'ela Ayu Ning Tyas Zahra** | `23215052` | Auth & Profile Service (`db_auth` / `:8001`) |

### 2. Lingkungan & Base URL

| Komponen | Base URL / Host | Keterangan |
|---|---|---|
| **API Gateway (Unified)** | `http://localhost:8000/api/v1` | Reverse proxy rute lintas layanan |
| **Auth & Profile Service** | `http://localhost:8001/api/v1` | Layanan autentikasi & profil pengguna |
| **Job Catalog Service** | `http://localhost:8002/api/v1` | Layanan manajemen lowongan kerja |
| **Application Service** | `http://localhost:8003/api/v1` | Layanan berkas lamaran & matchmaking |
| **Pipeline Service** | `http://localhost:8004/api/v1` | Layanan linimasa tahapan seleksi |

### 3. Konvensi Penamaan (Naming Conventions)
- **URI Path:** Menggunakan kata benda jamak huruf kecil (*lowercase plural nouns*), dipisahkan dengan tanda hubung jika multi-kata (kebab-case), contoh: `/api/v1/jobs`, `/api/v1/job-requirements`.
- **JSON Keys & Attributes:** Menggunakan huruf kecil dengan garis bawah (*lowercase snake_case*), contoh: `employment_type`, `match_score`, `created_at`.
- **Query Parameters:** Menggunakan *snake_case*, contoh: `?per_page=15&salary_min=8000000`.
- **Format Pertukaran Data:** JSON murni (RFC 8259) dengan header wajib `Content-Type: application/json` dan `Accept: application/json`.
- **Otentikasi:** JSON Web Token (JWT) dikirimkan melalui header `Authorization: Bearer <token>`.

---

## B. Pemodelan Resource & Relasi

Sistem PaduKerja dimodelkan ke dalam 8 entitas resource utama yang saling berelasi:

### 1. Spesifikasi Atribut Resource

| Resource | Atribut Utama & Tipe Data | Deskripsi Singkat |
|---|---|---|
| **`users`** | `id` (bigint), `name` (string), `email` (string), `password_hash` (string), `role` (enum: applicant, recruiter, admin), `created_at` (datetime) | Akun pengguna sistem dan penetapan hak otoritas akses. |
| **`profiles`** | `id` (bigint), `user_id` (bigint), `phone` (string), `bio` (text), `location` (string), `portfolio_url` (string) | Data personal pelengkap akun pencari kerja maupun recruiter. |
| **`skills`** | `id` (bigint), `name` (string), `category` (string) | Katalog referensi kompetensi teknis dan soft skill. |
| **`user_skills`** | `user_id` (bigint), `skill_id` (bigint), `level` (enum: beginner, intermediate, advanced), `years_exp` (integer) | Tabel pivot matriks kompetensi yang dimiliki oleh pengguna. |
| **`jobs`** | `id` (bigint), `title` (string), `company_name` (string), `description` (text), `location` (string), `employment_type` (enum), `salary_min` (numeric), `salary_max` (numeric), `quota` (integer), `status` (enum: open, closed), `posted_by` (bigint) | Entitas publikasi lowongan pekerjaan oleh pihak recruiter. |
| **`job_requirements`** | `id` (bigint), `job_id` (bigint), `skill_name` (string), `weight` (integer: 1-3), `level` (enum: required, important, nice_to_have) | Syarat keahlian lowongan beserta bobot algoritma pemadanan. |
| **`applications`** | `id` (bigint), `job_id` (bigint), `user_id` (bigint), `cv_file_path` (string), `cover_letter` (text), `match_score` (decimal 5,2), `match_category` (enum), `status` (enum: submitted, in_review, withdrawn), `applied_at` (datetime) | Berkas lamaran kerja yang diajukan pelamar pada suatu lowongan. |
| **`pipelines`** | `id` (bigint), `application_id` (bigint), `current_stage` (enum: screening, assessment, interview, offering, rejected), `stages_completed` (integer), `created_at` (datetime) | Linimasa alur seleksi bertahap yang memantau status kandidat. |
| **`interviews`** | `id` (bigint), `pipeline_id` (bigint), `scheduled_at` (datetime), `type` (enum: online, offline), `meeting_url` (string), `interviewer` (string), `status` (enum: scheduled, completed, cancelled) | Jadwal sesi wawancara kerja yang terikat pada pipeline kandidat. |

### 2. Diagram Relasi Entitas (ERD)

```text
+----------------+       1:1       +----------------+
|     users      |-----------------|    profiles    |
+----------------+                 +----------------+
        | 1:N
        |
        +--------------------------+
        |                          |
       1:N                        1:N
+----------------+        +----------------+
|  user_skills   |        |      jobs      |
+----------------+        +----------------+
        |                          | 1:N
       N:1                         |
+----------------+        +-------------------+
|     skills     |        | job_requirements  |
+----------------+        +-------------------+
                                   | 1:N
                                   |
                          +----------------+
                          |  applications  |
                          +----------------+
                                   | 1:1
                                   |
                          +----------------+
                          |   pipelines    |
                          +----------------+
                                   | 1:N
                                   |
                          +----------------+
                          |   interviews   |
                          +----------------+
```

---

## C. Matriks Endpoint (18 Endpoint)

| No | Method | URI Endpoint | Fungsi Bisnis | Aktor / Wewenang | Status Sukses | Status Gagal |
|:---:|:---:|---|---|:---:|:---:|:---:|
| 1 | `POST` | `/api/v1/auth/register` | Mendaftarkan akun pengguna baru | Public | `201 Created` | `422 Unprocessable Entity` |
| 2 | `POST` | `/api/v1/auth/login` | Otentikasi kredensial & penerbitan token JWT | Public | `200 OK` | `401 Unauthorized`, `422` |
| 3 | `POST` | `/api/v1/auth/logout` | Mengakhiri sesi login & blacklist token JWT | Authenticated | `200 OK` | `401 Unauthorized` |
| 4 | `GET` | `/api/v1/auth/me` | Mengambil data profil pengguna aktif | Authenticated | `200 OK` | `401 Unauthorized` |
| 5 | `PUT` | `/api/v1/profile/skills` | Menyimpan/sinkronisasi daftar keahlian profil | Applicant | `200 OK` | `401`, `422 Unprocessable Entity` |
| 6 | `GET` | `/api/v1/jobs` | Daftar lowongan aktif dengan filter & pagination | Public | `200 OK` | `400 Bad Request` |
| 7 | `GET` | `/api/v1/jobs/{id}` | Mengambil detail spesifik satu lowongan kerja | Public | `200 OK` | `404 Not Found` |
| 8 | `POST` | `/api/v1/jobs` | Recruiter menerbitkan lowongan pekerjaan baru | Recruiter | `201 Created` | `401`, `422 Unprocessable Entity` |
| 9 | `PUT` | `/api/v1/jobs/{id}` | Mengubah seluruh informasi lowongan kerja | Recruiter | `200 OK` | `403 Forbidden`, `404`, `422` |
| 10 | `PATCH` | `/api/v1/jobs/{id}/status` | Mengubah status aktif lowongan (open/closed) | Recruiter | `200 OK` | `403 Forbidden`, `404 Not Found` |
| 11 | `DELETE`| `/api/v1/jobs/{id}` | Menghapus lowongan pekerjaan dari katalog | Recruiter | `200 OK` | `403 Forbidden`, `404 Not Found` |
| 12 | `POST` | `/api/v1/applications` | Mengirim lamaran & memicu skor matchmaking | Applicant | `201 Created` | `400`, `409 Conflict`, `422` |
| 13 | `GET` | `/api/v1/applications` | Daftar riwayat lamaran yang diajukan pelamar | Applicant | `200 OK` | `401 Unauthorized` |
| 14 | `GET` | `/api/v1/applications/{id}` | Detail berkas lamaran beserta skor kecocokan | Applicant / Recruiter | `200 OK` | `403 Forbidden`, `404 Not Found` |
| 15 | `DELETE`| `/api/v1/applications/{id}` | Membatalkan pengajuan berkas lamaran | Applicant | `200 OK` | `400 Bad Request`, `404 Not Found` |
| 16 | `GET` | `/api/v1/jobs/{jobId}/applications/ranked` | Daftar pelamar terurut skor kecocokan tertinggi | Recruiter | `200 OK` | `403 Forbidden`, `404 Not Found` |
| 17 | `GET` | `/api/v1/pipelines/{id}` | Linimasa alur seleksi kandidat pelamar | Applicant / Recruiter | `200 OK` | `403 Forbidden`, `404 Not Found` |
| 18 | `PATCH` | `/api/v1/pipelines/{id}/status` | Memajukan tahapan kandidat ke fase berikutnya | Recruiter | `200 OK` | `403`, `404`, `422 Unprocessable` |
| 19 | `POST` | `/api/v1/pipelines/{id}/interviews` | Menjadwalkan sesi wawancara untuk kandidat | Recruiter | `201 Created` | `400 Bad Request`, `403`, `422` |

---

## D. Desain Query: Filtering, Searching, Sorting, & Pagination

Pola query parameter diterapkan secara konsisten pada endpoint bertipe *collection* untuk mendukung performa optimal dan navigasi data fleksibel di sisi client:

### 1. Desain Query pada `GET /api/v1/jobs`

| Parameter | Tipe | Contoh Nilai | Aturan & Batasan | Deskripsi Fungsi |
|---|---|---|---|---|
| `q` | string | `?q=backend` | Maks. 80 karakter | Full-text search pada kolom `title` dan `description`. |
| `location` | string | `?location=jakarta` | Whitelist lokasi valid | Memfilter lowongan berdasarkan wilayah penempatan kerja. |
| `employment_type` | enum | `?employment_type=full-time` | `full-time`, `part-time`, `contract`, `internship` | Memfilter jenis ikatan kontrak pekerjaan. |
| `salary_min` | integer | `?salary_min=8000000` | Angka positif | Menampilkan lowongan dengan rentang gaji awal minimal. |
| `status` | enum | `?status=open` | `open`, `closed` (Default: `open`) | Memfilter status ketersediaan lowongan. |
| `sort` | string | `?sort=-created_at` | Whitelist: `created_at`, `salary_min`, `salary_max` | Pengurutan data. Tanda minus (`-`) menandakan *descending*. |
| `page` | integer | `?page=1` | Default: `1`, minimum: `1` | Menentukan nomor halaman saat ini. |
| `per_page` | integer | `?per_page=15` | Default: `15`, maksimum: `50` | Membatasi jumlah record per halaman (mencegah DoS). |

### 2. Desain Query pada `GET /api/v1/jobs/{jobId}/applications/ranked`

| Parameter | Tipe | Contoh Nilai | Aturan & Batasan | Deskripsi Fungsi |
|---|---|---|---|---|
| `category` | enum | `?category=excellent` | `excellent` (>=80%), `good` (50-79%), `under_qualified` (<50%) | Memfilter pelamar berdasarkan kelompok kualifikasi skor. |
| `sort` | string | `?sort=-match_score` | `match_score`, `applied_at` (Default: `-match_score`) | Mengurutkan pelamar dari skor tertinggi ke terendah. |
| `page` | integer | `?page=1` | Default: `1` | Nomor halaman data pelamar. |
| `per_page` | integer | `?per_page=10` | Default: `10`, maksimum: `30` | Jumlah berkas pelamar per halaman review. |

---

## E. Desain Representasi Data (JSON Envelope)

Seluruh response mengimplementasikan standar **JSON Envelope** global yang konsisten.

### 1. Representasi Item Tunggal: `GET /api/v1/jobs/12` (`200 OK`)

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
    "salary_range": {
      "min": 9000000,
      "max": 15000000,
      "currency": "IDR"
    },
    "quota": 3,
    "filled": 0,
    "status": "open",
    "requirements": [
      { "id": 1, "skill": "PHP", "weight": 3, "level": "required" },
      { "id": 2, "skill": "Laravel", "weight": 3, "level": "required" },
      { "id": 3, "skill": "MySQL", "weight": 2, "level": "important" }
    ],
    "posted_at": "2026-10-11T09:00:00+07:00"
  },
  "meta": {
    "timestamp": "2026-10-16T17:45:00+07:00",
    "version": "1.0.0"
  }
}
```

### 2. Representasi Collection Terpaginasi: `GET /api/v1/jobs?page=1&per_page=2` (`200 OK`)

```json
{
  "status": "success",
  "message": "Daftar lowongan kerja berhasil diambil",
  "data": [
    {
      "id": 12,
      "title": "Backend Developer",
      "company_name": "PT Teknologi Maju",
      "location": "Jakarta",
      "salary_min": 9000000,
      "quota": 3,
      "status": "open"
    },
    {
      "id": 13,
      "title": "Frontend Engineer",
      "company_name": "PT Solusi Digital",
      "location": "Bandung",
      "salary_min": 8500000,
      "quota": 2,
      "status": "open"
    }
  ],
  "meta": {
    "timestamp": "2026-10-16T17:45:00+07:00",
    "version": "1.0.0",
    "pagination": {
      "current_page": 1,
      "per_page": 2,
      "total_records": 18,
      "total_pages": 9,
      "has_next": true,
      "has_prev": false
    }
  }
}
```

### 3. Representasi Submit Lamaran: `POST /api/v1/applications` (`201 Created`)

```json
{
  "status": "success",
  "message": "Lamaran berhasil dikirim. Skor kecocokan berhasil dikalkulasi",
  "data": {
    "id": 45,
    "job_id": 12,
    "user_id": 101,
    "match_score": 80.0,
    "match_category": "excellent",
    "matched_skills": ["PHP", "Laravel", "MySQL"],
    "missing_skills": ["Redis"],
    "status": "submitted",
    "applied_at": "2026-10-16T17:45:05+07:00"
  },
  "meta": {
    "timestamp": "2026-10-16T17:45:05+07:00",
    "version": "1.0.0"
  }
}
```

---

## F. Penanganan Error & Kode Status HTTP

Format error menggunakan amplop terpadu dengan detail field-level validation pada atribut `errors`:

### 1. Error Validasi Input: `422 Unprocessable Entity`

```json
{
  "status": "error",
  "message": "Validasi formulir lowongan gagal",
  "data": null,
  "errors": {
    "title": ["Judul lowongan pekerjaan wajib diisi."],
    "quota": ["Kuota posisi minimal bernilai 1 orang."],
    "requirements": ["Minimal cantumkan satu syarat keahlian."]
  },
  "meta": {
    "timestamp": "2026-10-16T17:45:00+07:00",
    "version": "1.0.0"
  }
}
```

### 2. Error Aturan Bisnis (Conflict): `409 Conflict`

```json
{
  "status": "error",
  "message": "Anda sudah pernah mengajukan lamaran untuk lowongan ini sebelumnya",
  "data": null,
  "meta": {
    "timestamp": "2026-10-16T17:45:00+07:00",
    "version": "1.0.0"
  }
}
```

### 3. Ringkasan Penggunaan Status Code

| Kode HTTP | Nama Standar | Kondisi Penggunaan pada PaduKerja |
|---|---|---|
| `200` | OK | Request GET/PUT/PATCH berhasil memproses data. |
| `201` | Created | Record baru berhasil disimpan (POST `/jobs`, POST `/applications`). |
| `400` | Bad Request | Request tidak valid atau operasi bisnis melanggar aturan (misal: batalkan lamaran yang sudah masuk sesi interview). |
| `401` | Unauthorized | Token JWT hilang, tidak disertakan, atau sudah kadaluarsa. |
| `403` | Forbidden | Identitas terotentikasi, namun role tidak memiliki izin (misal: pelamar mencoba memposting lowongan kerja). |
| `404` | Not Found | ID resource yang dicari tidak ditemukan di database service. |
| `409` | Conflict | Konflik data duplikat (email sudah terdaftar atau sudah melamar lowongan). |
| `422` | Unprocessable Entity | Payload JSON valid secara sintaksis, namun gagal validasi aturan field. |
| `500` | Internal Server Error | Kegagalan sistemik server atau gangguan inter-service communication. |

---

## G. Perancangan Aksi Non-CRUD

Proses bisnis yang kompleks tidak dipaksakan ke dalam endpoint RPC berbasis kata kerja (`/calculateScore` atau `/rejectCandidate`), melainkan dimodelkan secara RESTful:

### 1. Mesin Pemadanan Keahlian Otomatis (Matchmaking Engine)
- **Pola:** *State-Enriched Creation*.
- **Endpoint:** `POST /api/v1/applications`
- **Rasional Desain:** Alih-alih membuat endpoint terpisah `POST /calculateScore`, kalkulasi skor dieksekusi secara otomatis saat resource lamaran diciptakan. Server memanggil data skill pelamar dan requirements lowongan, menghitung rumus **Weighted Jaccard Similarity**, dan langsung menyematkan atribut `match_score` serta `match_category` ke dalam record `applications`.

### 2. Transisi Tahapan Seleksi (Pipeline State Machine)
- **Pola:** *Sub-Resource State Transition*.
- **Endpoint:** `PATCH /api/v1/pipelines/{id}/status`
- **Request Body:** `{ "target_stage": "assessment", "notes": "Lolos screening awal" }`
- **Rasional Desain:** Menghindari endpoint kata kerja acak seperti `/advanceCandidate` atau `/passScreening`. Tahapan seleksi kandidat dikelola sebagai atribut status dari resource `pipelines`, dengan validasi State Machine agar kandidat tidak dapat melompati tahapan baku (`Screening` -> `Assessment` -> `Interview` -> `Offering` / `Rejected`).

### 3. Penarikan Berkas Lamaran (Application Withdrawal)
- **Pola:** *Soft-State Deletion*.
- **Endpoint:** `DELETE /api/v1/applications/{id}`
- **Rasional Desain:** Menggunakan method `DELETE` standar. Secara internal, server tidak melakukan operasi `DROP/DELETE` fisik pada baris database, melainkan memperbarui atribut status menjadi `withdrawn` untuk mempertahankan keutuhan audit trail pelamar dan recruiter.

---

## H. Catatan Keputusan Desain (Architectural Decisions)

1. **Penggunaan JSON Envelope Universal:** Seluruh respon (baik sukses maupun error) dibungkus amplop berstruktur seragam (`status`, `message`, `data`, `meta`). Pola ini memudahkan pembuatan interceptor tunggal pada client web/mobile front-end dalam menangani respon dan parsing metadata pagination.
2. **Page-Based Offset Pagination:** Dipilih karena platform rekrutmen PaduKerja membutuhkan navigasi halaman eksplisit (*page navigation UI*). Untuk mengatasi resiko performa offset pada dataset besar, parameter dibatasi secara ketat dengan `per_page` default 15 dan limit maksimum 50.
3. **Pemisahan Semantik PUT vs PATCH:** `PUT` hanya digunakan pada operasi penggantian data profil secara utuh (`PUT /api/v1/profile/skills`), sedangkan `PATCH` digunakan untuk pembaruan atribut selektif (seperti status lowongan atau tahapan seleksi) untuk menghemat bandwidth payload.
4. **Isolasi Database-per-Service:** Keempat layanan microservice tidak pernah berbagi akses tabel database secara langsung. Seluruh kebutuhan agregasi data dilakukan murni via REST API inter-service dengan referensi ID unik, menjamin skalabilitas independen tiap modul.
