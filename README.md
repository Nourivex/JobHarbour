<p align="center">
  <img src="public/logo.png" width="96" alt="JobHarbour Logo">
</p>

<h1 align="center">JobHarbour</h1>

<p align="center">
  <strong>Platform Rekrutmen Terintegrasi Berbasis Microservice</strong><br>
  <em>Automated Skill-Matchmaking Engine & Multi-Stage Pipeline Tracker</em><br>
  <strong>"Pelabuhan Para Pekerja Sebelum Keberangkatan Menuju Dunia Karier"</strong>
</p>

<p align="center">

![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?style=flat-square&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.4%2B-777BB4?style=flat-square&logo=php&logoColor=white)
![Architecture](https://img.shields.io/badge/Architecture-Microservices-0D1117?style=flat-square)
![API](https://img.shields.io/badge/API-REST%20JSON-blue?style=flat-square)
![OpenAPI](https://img.shields.io/badge/OpenAPI-3.1.0-49cc90?style=flat-square&logo=openapiinitiative&logoColor=white)
![DDEV](https://img.shields.io/badge/DDEV-supported-0D1117?style=flat-square)
![Platform](https://img.shields.io/badge/Platform-Linux%20%2F%20Windows-294172?style=flat-square)
![Team](https://img.shields.io/badge/Team-Kelompok%204%20UHN-orange?style=flat-square)

</p>

---

## 📌 Informasi Akademik

| Komponen | Keterangan |
|---|---|
| **Mata Kuliah** | Pemrograman Web Service (5537344 / 3 SKS) |
| **Program Studi / Kampus** | Teknik Informatika - Universitas Harkat Negeri (Tegal) |
| **Dosen Pengampu** | Zaenul Arif, S.Kom., M.Kom. |
| **Kelompok** | Kelompok 4 |
| **Semester** | 7 (Ganjil) |

---

## ⚓ Kenalan sama JobHarbour (Buat Pemula)

Sederhananya, **JobHarbour** adalah proyek web service platform lowongan kerja dan rekrutmen. Berbeda dari job portal konvensional, JobHarbour dibangun dengan arsitektur **microservices** yang saling berkomunikasi melalui **REST API JSON**, bukan aplikasi monolitik yang semua kodenya menumpuk di satu tempat.

### 🌊 Filosofi Nama "JobHarbour"

Mengapa dinamakan **JobHarbour**?
Ibarat sebuah pelabuhan (*harbour*) yang menjadi tempat berlabuh aman bagi kapal-kapal sebelum berlayar mengarungi samudra lepas, **JobHarbour** adalah pelabuhan tempat bertemunya talenta pencari kerja dan recruiter.

Di pelabuhan ini:
1. **Talenta & Pencari Kerja** mempersiapkan profil dan keahlian terbaik mereka, mencocokkan arah kapal dengan lowongan yang sesuai, dan memantau kesiapan keberangkatan.
2. **Recruiter & Perusahaan** menemukan armada talenta yang paling cocok tanpa harus tersesat di tengah lautan ratusan berkas CV yang tidak relevan.
3. **Harmoni Daerah:** Filosofi pelabuhan ini juga sangat selaras dan mencerminkan identitas kampus **Universitas Harkat Negeri** yang berakar di **Tegal** - kota bahari dengan sejarah maritim dan pelabuhan yang kuat.

### 🎯 Masalah Nyata yang Mau Diselesaikan

Pernah melamar kerja lalu merasa CV seperti masuk ke "lubang hitam" tanpa kepastian? Atau recruiter yang kewalahan menyortir tumpukan ratusan PDF manual? JobHarbour menyelesaikan dua persoalan utama tersebut:

1. **Bagi Pelamar:** Tahu persis seberapa cocok keahlian (skills) kamu dengan kualifikasi lowongan (skor dihitung otomatis secara transparan). Kamu juga bisa memantau posisi lamaranmu berada di tahapan mana secara real-time.
2. **Bagi Recruiter:** Tidak perlu lagi memeriksa manual ratusan berkas satu per satu. Sistem langsung menyajikan peringkat pelamar berdasarkan persentase kecocokan kualifikasi, lengkap dengan papan linimasa seleksi bertahap.

---

## 🚀 Fitur Unggulan

- **🎯 Mesin Pemadanan Keahlian Otomatis (Skill-Matchmaking Engine):** Menghitung skor kecocokan antara profil pelamar dan kualifikasi lowongan secara real-time menggunakan formula *Weighted Jaccard Similarity*.
- **📊 Pelacak Linimasa Seleksi Transparan (Multi-Stage Pipeline):** Tahapan rekrutmen yang jelas dan dapat dipantau langsung:
  ```text
  Screening -> Assessment -> Interview -> Offering / Rejected
  ```
- **🏗️ Arsitektur 4 Microservices Mandiri:** Setiap layanan memiliki database dan tanggung jawab mandiri (*database-per-service*), berkomunikasi secara terstruktur lewat kontrak REST API.
- **📄 Kontrak OpenAPI 3.1 & Koleksi Pengujian Siap Pakai:** Lengkap dengan spesifikasi OpenAPI, koleksi Postman, dan Thunder Client yang bisa langsung di-import untuk mencoba endpoint.

---

## 👥 Tim Pengembang & Pembagian Layanan

Setiap anggota kelompok memegang satu layanan microservice secara penuh (mulai dari perancangan API, implementasi database dan controller, penulisan pengujian, hingga dokumentasi):

| No | Layanan Microservice | Penanggung Jawab | NIM | Database & Port | Dokumentasi Detail |
|:---:|---|---|:---:|:---:|:---:|
| 1 | **Auth & Profile Service** | **Nabe'ela Ayu Ning Tyas Zahra** | `23215052` | `db_auth` (:8001) | [Baca Modul Auth](docs/materi/minggu-2/auth-profile/README.md) |
| 2 | **Job Catalog Service** | **M. Fahren Andrean Rangkuti** | `23215030` | `db_jobs` (:8002) | [Baca Modul Job Catalog](docs/materi/minggu-2/job-catalog/README.md) |
| 3 | **Application & Matchmaking Service** | **Muhammad Yasir Ilham Nabil** | `23215040` | `db_applications` (:8003) | [Baca Modul Application](docs/materi/minggu-2/application-matchmaking/README.md) |
| 4 | **Recruitment Pipeline Service** | **MUHAMMAD AFFIF** | `24225046` | `db_pipeline` (:8004) | [Baca Modul Pipeline](docs/materi/minggu-2/recruitment-pipeline/README.md) |

---

## 📚 Peta Navigasi Dokumentasi Proyek

Biar nggak bingung mencari berkas tugas, spesifikasi, atau panduan teknis, gunakan daftar navigasi berikut:

### 1. Dokumen Arsitektur & Spesifikasi Resmi

- [📐 `docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md) - Penjelasan arsitektur sistem, topologi jaringan, komunikasi antar service, dan formula kalkulasi matchmaking.
- [📋 `docs/API_CONTRACT_STANDARDS.md`](docs/API_CONTRACT_STANDARDS.md) - Format standar JSON envelope, header HTTP, aturan status code, dan format penanganan error.
- [📝 `docs/api-design.md`](docs/api-design.md) - Dokumen perancangan API resmi kelompok (API Design Document v1) yang mencakup 40 endpoint lengkap.
- [🌐 `docs/openapi.yaml`](docs/openapi.yaml) - Spesifikasi formal kontrak OpenAPI 3.1.0 untuk seluruh endpoint JobHarbour (lolos Redocly lint dengan 0 error).
- [🧪 `docs/TESTING_GUIDE.md`](docs/TESTING_GUIDE.md) - Panduan praktis pengujian API menggunakan Postman dan Thunder Client (lengkap dengan akun demo pre-seeded).
- [📅 `docs/SPRINT_PLAN.md`](docs/SPRINT_PLAN.md) - Pemetaan milestone perkuliahan, product backlog, dan rencana sprint agile tim.

### 2. Berkas Tugas & Materi Perkuliahan Mingguan

- [📦 `docs/materi/minggu-2/README.md`](docs/materi/minggu-2/README.md) - Fondasi Web Service, protokol HTTP/HTTPS, dan implementasi 8 Endpoint Inti REST API.
  - Modul [Auth & Profile](docs/materi/minggu-2/auth-profile/README.md)
  - Modul [Job Catalog](docs/materi/minggu-2/job-catalog/README.md)
  - Modul [Application & Matchmaking](docs/materi/minggu-2/application-matchmaking/README.md)
  - Modul [Recruitment Pipeline](docs/materi/minggu-2/recruitment-pipeline/README.md)
- [📝 `docs/materi/minggu-3/README.md`](docs/materi/minggu-3/README.md) - Lembar Kerja Desain REST API (Worksheet Mandiri per Anggota) dan diagram relasi resource.
- [📐 `docs/materi/minggu-4/README.md`](docs/materi/minggu-4/README.md) - API Design Document v1 (Milestone M2 Bagian 1), rancangan resource, dan hasil aktivitas workshop.
- [⚡ `docs/materi/minggu-5/README.md`](docs/materi/minggu-5/README.md) - OpenAPI v1, pengujian mock server dengan Stoplight Prism, dan dokumentasi interaktif Swagger UI.

---

## 📡 Standar Komunikasi REST API

Semua endpoint JobHarbour berbicara dalam format **JSON** yang seragam dan mudah dipahami oleh frontend maupun client service lain.

### Format JSON Envelope Standar

Setiap respon dari server selalu dibungkus dengan format berikut:

```json
{
  "status": "success",
  "message": "Data berhasil dimuat",
  "data": {},
  "meta": {
    "timestamp": "2026-10-26T19:00:00+07:00",
    "version": "1.0.0"
  }
}
```

### Panduan Kode Status HTTP (HTTP Status Codes)

| Kode | Arti | Kapan Dipakai? |
|:---:|---|---|
| `200` | OK | Permintaan berhasil dan data dikembalikan |
| `201` | Created | Data baru berhasil dibuat di database |
| `400` | Bad Request | Format permintaan tidak sesuai atau parameter salah |
| `401` | Unauthorized | Belum login atau token JWT sudah kadaluarsa |
| `403` | Forbidden | Sudah login, tapi role akun tidak berhak mengakses |
| `404` | Not Found | Data atau URL yang dicari tidak ditemukan |
| `409` | Conflict | Terjadi bentrokan data (misal email sudah terdaftar) |
| `422` | Unprocessable Entity | Validasi input form gagal (misal password kurang panjang) |
| `500` | Internal Server Error | Terjadi kendala tidak terduga pada server |

---

## 🛠️ Kebutuhan Sistem & Teknologi

Sebelum menjalankan proyek, pastikan komputermu sudah terpasang:

- **PHP 8.4+** dan **Composer**
- **Node.js 24+** dan **npm**
- **Git**
- **DDEV + Docker** (Sangat disarankan agar tidak perlu instal database lokal manual)

---

## 🏃 Cara Cepat Menjalankan Proyek (Quick Start)

Kami sudah menyiapkan skrip otomatis (`./dev` untuk Linux/macOS atau `dev.bat` untuk Windows) agar instalasi bisa selesai dalam hitungan menit!

### 1. Clone Repositori

```bash
git clone https://github.com/Nourivex/PaduKerja.git
cd PaduKerja
```

### 2. Jalankan Setup Otomatis

**Untuk Linux / macOS:**
```bash
./dev --setup
```

**Untuk Windows:**
```bat
dev.bat --setup
```

Skrip ini akan secara otomatis:
1. Menjalankan DDEV (atau mendeteksi PHP lokal jika tanpa DDEV).
2. Memasang dependensi Composer.
3. Menyalin konfigurasi file `.env`.
4. Membuat Laravel application key (`APP_KEY`).
5. Memasang dependensi npm dan melakukan build aset frontend.

### 3. Migrasi Database dan Data Awal (Seeding)

Jalankan perintah ini untuk membuat tabel dan mengisi akun demo awal:

```bash
./dev migrate:fresh --seed
```

### 4. Perintah Berguna Lainnya

Skrip `./dev` berfungsi sebagai jembatan langsung ke Laravel Artisan:

```bash
./dev --doctor      # Cek kesehatan lingkungan kerja (diagnostik)
./dev route:list    # Melihat daftar seluruh route API yang aktif
./dev test          # Menjalankan seluruh pengujian otomatis (automated test)
./dev tinker        # Membuka konsol interaktif Laravel
```

---

## 🧪 Cara Mencoba dan Menguji API (Buat Pemula)

Nggak perlu pusing menulis kode frontend dulu kalau cuma mau mencoba API-nya. Kamu bisa langsung pakai alat bantu pengujian API:

### 1. Akun Siap Pakai di Database (Demo Accounts)

Setelah menjalankan `migrate:fresh --seed`, kamu bisa langsung memakai akun ini:

| Peran (Role) | Email | Password | Kegunaan |
|---|---|---|---|
| **Recruiter** | `recruiter@jobharbour.id` | `password123` | Buat lowongan baru & ubah status pelamar |
| **Pelamar (Budi)** | `budi@jobharbour.id` | `password123` | Pelamar dengan skill PHP, Laravel, MySQL, Git |
| **Pelamar (Siti)** | `siti@jobharbour.id` | `password123` | Pelamar dengan skill Python, Django, PostgreSQL, Docker |

> *Catatan: Akun lama `@padukerja.id` tetap didukung sebagai alias pada sistem.*

### 2. Import Koleksi Pengujian

Tersedia file koleksi siap pakai di folder `docs/`:
- **Thunder Client (VS Code):** Buka ekstensi Thunder Client -> klik menu `...` di tab Collections -> pilih **Import** -> pilih file [`docs/thunder-collection_padukerja.json`](docs/thunder-collection_padukerja.json).
- **Postman:** Buka Postman -> klik tombol **Import** -> masukkan file [`docs/PaduKerja.postman_collection.json`](docs/PaduKerja.postman_collection.json).

> 💡 Untuk panduan langkah demi langkah pengujian setiap endpoint, silakan buka dokumen [`docs/TESTING_GUIDE.md`](docs/TESTING_GUIDE.md).

---

## 📂 Struktur Direktori Proyek

```text
JobHarbour/
├── app/
│   ├── Http/Controllers/Api/   # Controller untuk setiap microservice
│   ├── Models/                 # Model Eloquent (User, JobVacancy, Application, dll)
│   └── Services/               # Logika bisnis (MatchmakingService, JwtService)
├── database/
│   ├── migrations/             # Skema tabel database per service
│   └── seeders/                # Data awal untuk pengujian
├── docs/                       # DOKUMENTASI LENGKAP PROYEK
│   ├── ARCHITECTURE.md         # Arsitektur & alur matchmaking
│   ├── API_CONTRACT_STANDARDS.md# Standar kontrak payload & error
│   ├── api-design.md           # Desain lengkap 40 endpoint REST API
│   ├── openapi.yaml            # Spesifikasi OpenAPI 3.1.0
│   ├── SPRINT_PLAN.md          # Rencana sprint & backlog
│   ├── TESTING_GUIDE.md        # Panduan testing Thunder Client & Postman
│   └── materi/                 # Berkas tugas kuliah per minggu (Minggu 2-5)
├── routes/
│   └── api.php                 # Rute REST API JobHarbour
├── tests/
│   └── Feature/                # Pengujian otomatis REST API (PHPUnit/Pest)
├── dev                         # Skrip pembantu lokal (Linux/macOS)
├── dev.bat                     # Skrip pembantu lokal (Windows)
└── README.md                   # Berkas panduan utama yang sedang kamu baca
```

---

## 🤝 Etika Kontribusi

Kalau kamu mau ikut menambahkan fitur atau memperbaiki bug di proyek ini, perhatikan beberapa aturan main berikut ya:

1. **Jaga Struktur Bersih:** Pertahankan kaidah struktur bawaan Laravel dan pemisahan logika controller/service.
2. **Kepatuhan Kontrak API:** Pastikan setiap response selalu menggunakan amplop standar JSON (`status`, `message`, `data`, `meta`).
3. **Pengujian Mandiri:** Selalu jalankan `./dev test` sebelum mengajukan pull request untuk memastikan tidak ada fitur lama yang rusak.
4. **Dokumentasi Terbaca:** Jika menambahkan atau mengubah endpoint, perbarui dokumentasi terkait di folder `docs/`.

---

## 📄 Lisensi

Proyek ini dirilis di bawah lisensi terbuka [MIT License](LICENSE).

---

<p align="center">
  Dibuat dengan semangat belajar oleh <strong>Kelompok 4</strong><br>
  Mata Kuliah Pemrograman Web Service - <strong>Universitas Harkat Negeri (Tegal)</strong>
</p>
