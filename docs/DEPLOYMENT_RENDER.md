# Panduan Deploy JobHarbour ke Render.com

Dokumen ini menjelaskan langkah demi langkah mempublikasikan aplikasi JobHarbour ke platform **Render.com** secara gratis (*Free Tier*) menggunakan Docker container.

---

## 1. Keunggulan Konfigurasi Docker JobHarbour di Render

- **Multi-Stage Build:** Membangun aset Vite/Tailwind dan dependensi Composer secara terisolasi sehingga ukuran image akhir tetap ramping dan cepat dimuat.
- **PHP 8.4 + Nginx:** Menggunakan PHP 8.4 resmi dengan ekstensi lengkap (`pdo_mysql`, `pdo_sqlite`, `pdo_pgsql`, `mbstring`, `zip`, `bcmath`, `intl`, `opcache`).
- **Zero-Config Database (SQLite Default):** Otomatis membuat berkas database SQLite di container dan menjalankan migrasi serta seeder data demo.
- **Dukungan External Database:** Jika ingin menggunakan MariaDB/MySQL cloud (seperti TiDB Cloud, Aiven, atau Clever Cloud), cukup ganti variabel `DB_CONNECTION` dan host database di dashboard Render.
- **Auto Dynamic Port:** Otomatis membaca variabel `$PORT` yang diinjeksikan oleh Render dan menghubungkannya ke Nginx.

---

## 2. Cara Deploy Cepat (Metode Blueprint - Rekomendasi)

Render menyediakan fitur **Blueprint** yang membaca berkas `render.yaml` di repositori secara otomatis.

1. Buka [https://dashboard.render.com](https://dashboard.render.com) dan masuk dengan akun GitHub Anda.
2. Klik tombol **New +** di pojok kanan atas, lalu pilih **Blueprint**.
3. Hubungkan repositori GitHub Anda: **`Nourivex/JobHarbour`**.
4. Render akan mendeteksi berkas `render.yaml` secara otomatis.
5. Klik **Apply**.
6. Render akan mulai melakukan proses build Docker, instalasi aset, migrasi database, dan menyalakan web server.
7. Setelah status berubah menjadi **Live**, Anda akan mendapatkan URL publik gratis (misal: `https://jobharbour.onrender.com`).

---

## 3. Cara Deploy Manual (Web Service)

Jika ingin membuat Web Service secara manual tanpa Blueprint:

1. Buka [https://dashboard.render.com](https://dashboard.render.com).
2. Klik **New +** -> **Web Service**.
3. Pilih repositori **`Nourivex/JobHarbour`**.
4. Isi data konfigurasi:
   - **Name:** `jobharbour` (atau nama lain pilihan Anda).
   - **Region:** `Singapore (Southeast Asia)` agar akses dari Indonesia berkecepatan tinggi.
   - **Branch:** `main`.
   - **Runtime:** Pilih **Docker**.
   - **Instance Type:** Pilih **Free**.
5. Di bagian **Environment Variables**, tambahkan:
   - `APP_NAME` = `JobHarbour`
   - `APP_ENV` = `production`
   - `APP_DEBUG` = `false`
   - `DB_CONNECTION` = `sqlite`
   - `DB_DATABASE` = `/var/www/html/database/database.sqlite`
   - `SESSION_DRIVER` = `database`
   - `CACHE_STORE` = `database`
   - `QUEUE_CONNECTION` = `database`
   - `SEED_ON_DEPLOY` = `true`
6. Klik **Create Web Service**.

---

## 4. Akun Uji Coba Demo Pasca Deploy

Setelah deploy selesai, sistem otomatis menjalankan database seeder dengan akun berikut:

| Peran (Role) | Email | Kata Sandi | Kegunaan Uji Coba |
|---|---|---|---|
| **Pelamar** | `budi@jobharbour.id` | `password123` | Uji lamaran kerja, riwayat My Dock, kalkulasi skor matchmaking |
| **Recruiter Resmi** | `recruiter@jobharbour.id` | `password123` | Akses Portal Recruiter, posting lowongan resmi, kelola pipeline ATS |
| **Recruiter Baru** | `recruiter.baru@jobharbour.id` | `password123` | Uji posting lowongan trial dan cegat lowongan kedua (403) |
| **Administrator** | `admin@jobharbour.id` | `password123` | Verifikasi perusahaan dan pantau agregator eksternal |

---

## 5. Rute Penting untuk Demonstrasi Dosen

Setelah aplikasi online di Render:

- **Portal Utama:** `https://<nama-app>.onrender.com/`
- **Katalog Lowongan:** `https://<nama-app>.onrender.com/catalog`
- **Postman API Client:** `https://<nama-app>.onrender.com/postman` (Konsol interaktif uji 18 endpoint)
- **Swagger UI:** `https://<nama-app>.onrender.com/docs`
- **Portal Recruiter:** `https://<nama-app>.onrender.com/recruiter`
- **Admin Verifikasi Perusahaan:** `https://<nama-app>.onrender.com/admin/verifications`

---

## 6. Catatan Khusus Render Free Tier

- Layanan Free Tier di Render akan masuk mode tidur (*sleep*) jika tidak ada trafik selama 15 menit.
- Saat diakses kembali setelah tidur, request pertama akan membutuhkan waktu sekitar 30 - 50 detik untuk bangun (*cold start*). Request selanjutnya akan berjalan cepat seperti biasa.
- Berkas database SQLite pada Free Tier akan direset jika container di-restart (misal saat redeploy). Jika membutuhkan database permanen dan persisten, disarankan menyambungkan ke layanan MariaDB/MySQL cloud eksternal gratis (seperti TiDB Cloud atau Aiven) dengan mengisi `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` pada menu Environment di Render.
