# Materi Pembelajaran Minggu ke-2: HTTP, HTTPS, dan Fondasi Komunikasi Web Service

> Mata Kuliah: Pemrograman Web Service (5537344 / 3 SKS) - Universitas Harkat Negeri
> Dosen Pengampu: Zaenul Arif, S.Kom., M.Kom
> Kelompok 4:
> - 23215052 Nabe'ela Ayu Ning Tyas Zahra (Auth & Profile Service)
> - 23215030 M. Fahren Andrean Rangkuti (Job Catalog Service)
> - 23215040 Muhammad Yasir Ilham Nabil (Application & Matchmaking Service)
> - 24225046 MUHAMMAD AFFIF (Recruitment Pipeline Service)

---

## 📌 Ringkasan 8 Endpoint Inti REST API

Sistem JobHarbour dirancang memiliki tepat **8 REST API Inti**, terbagi secara proporsional ke dalam 4 layanan microservices untuk masing-masing penanggung jawab:

| Layanan Microservice | Penanggung Jawab | NIM | Modul Utama | Method & Endpoint | Deskripsi Fungsionalitas |
|---|---|---|---|---|---|
| **[Auth & Profile Service](auth-profile/README.md)** | **Nabe'ela Ayu Ning Tyas Zahra** | `23215052` | Akun & Keahlian | `POST /api/auth/login`<br>`PUT /api/profile/skills` | 1. Login pengguna & token akses JWT<br>2. Simpan/update keahlian profil |
| **[Job Catalog Service](job-catalog/README.md)** | **M. Fahren Andrean Rangkuti** | `23215030` | Lowongan Kerja | `GET /api/jobs`<br>`POST /api/jobs` | 1. Menampilkan & filter lowongan kerja<br>2. Recruiter memposting lowongan baru |
| **[Application & Matchmaking Service](application-matchmaking/README.md)** | **Muhammad Yasir Ilham Nabil** | `23215040` | Lamaran & Scoring | `POST /api/applications`<br>`DELETE /api/applications/{id}` | 1. Mengirim lamaran + skoring otomatis<br>2. Membatalkan pengajuan lamaran |
| **[Recruitment Pipeline Service](recruitment-pipeline/README.md)** | **MUHAMMAD AFFIF** | `24225046` | Seleksi Pelamar | `PATCH /api/pipelines/{id}/status`<br>`GET /api/pipelines/{id}` | 1. Recruiter update tahap seleksi<br>2. Pelamar melihat riwayat linimasa |

---

## 📂 Struktur Dokumentasi Per Layanan Microservice

Masing-masing modul di bawah ini dapat dipelajari, dijalankan, dan diuji secara mandiri:

1. **[`docs/materi/minggu-2/auth-profile/README.md`](auth-profile/README.md)** - Panduan lengkap Auth & Profile Service (Script, Controller, Middleware JWT, Request/Response Payload, Uji Coba).
2. **[`docs/materi/minggu-2/job-catalog/README.md`](job-catalog/README.md)** - Panduan lengkap Job Catalog Service (Script, Controller, Model, Request/Response Payload, Uji Coba Thunder Client & curl).
3. **[`docs/materi/minggu-2/application-matchmaking/README.md`](application-matchmaking/README.md)** - Panduan lengkap Application & Matchmaking Service (Script, Controller, Rumus Skoring Weighted Jaccard, Request/Response Payload, Uji Coba).
4. **[`docs/materi/minggu-2/recruitment-pipeline/README.md`](recruitment-pipeline/README.md)** - Panduan lengkap Recruitment Pipeline Service (Script, Controller, Tahapan Pipeline Screening → Offering, Request/Response Payload, Uji Coba).

---

## 🛠️ Standar Global Komunikasi API

Setiap endpoint pada seluruh layanan wajib mengikuti kaidah berikut:

1. **Format JSON Envelope:**
   ```json
   {
     "status": "success | error",
     "message": "Pesan penjelasan untuk manusia",
     "data": { ... },
     "meta": {
       "timestamp": "ISO8601",
       "version": "1.0.0"
     }
   }
   ```
2. **Header Wajib:**
   - `Content-Type: application/json` (pada POST, PUT, PATCH)
   - `Accept: application/json` (pada seluruh request)
   - `Authorization: Bearer <token>` (pada endpoint terproteksi)
3. **Kode Status HTTP Standar:**
   - `200 OK` : Request berhasil diproses.
   - `201 Created` : Data baru berhasil disimpan (POST).
   - `400 Bad Request` : Input tidak valid atau lowongan sudah tutup.
   - `401 Unauthorized` : Token JWT tidak ada, salah, atau kedaluwarsa.
   - `403 Forbidden` : Hak akses peran (role) tidak mencukupi.
   - `404 Not Found` : ID data tidak ditemukan di database.
   - `409 Conflict` : Duplikasi data (misal: sudah pernah melamar).
   - `422 Unprocessable Entity` : Validasi field gagal.
