# JobHarbour - Strict Engineering & Quality Rules

Dokumen ini memuat aturan baku yang WAJIB dipatuhi dalam pengembangan platform JobHarbour. Aturan ini dirancang untuk menjaga kualitas kode, konsistensi antarmuka visual, dan keaslian penulisan manusia.

---

## 1. Panggilan Pengguna & Protokol Komunikasi
- Pengguna bernama **Affif**. Panggil dengan sebutan **Mas Affif** atau **Ka Affif**.
- Gaya interaksi: Ramah sesama mahasiswa teknik informatika, santai, lugas, solutif, to-the-point, dan tidak bertele-tele.

---

## 2. Anti-Slop AI Policy (Keaslian Tulisan Manusia)
Dilarang keras memproduksi teks bergaya robotik, klise AI generatif, atau kalimat mengada-ada yang tidak mencerminkan tulisan tangan mahasiswa/engineer asli:
1. **Daftar Kata/Frasa Terlarang:**
   - Jargon AI klise: *"tapestry", "delve", "pivotal", "in conclusion", "furthermore", "testament to", "crucial aspect"*.
   - Frasa aneh mengada-ada: contohnya *"metafora pelabuhan untuk kota Tegal"*, *"seperti simfoni kode"*.
2. **Gaya Penulisan Mahasiswa (Tone of Voice):**
   - Gunakan gaya bahasa mahasiswa yang santai, wajar, praktis, dan mudah dipahami teman sekelompok.
   - Hindari bahasa birokrasi korporat yang kaku atau sok ahli/dosen.
   - Tulis to-the-point langsung ke inti solusi tanpa basa-basi pengantar yang panjang.
3. **Keaslian Berkas Akademik:**
   - Dilarang mencantumkan label pembagian kelompok internal (seperti "Paket A", "Paket B", "Paket C", "Paket D") di dalam dokumen tugas/laporan UTS.
   - Lokasi kampus Universitas Harkat Negeri selalu konsisten di **Tegal** (Jawa Tengah), bukan Jakarta.

---

## 3. Strict Punctuation Rule (Larangan Strip Panjang)
- **Dilarang Keras** menggunakan strip panjang:
  - *Em-dash* (`-` / `\u2014`)
  - *En-dash* (`-` / `\u2013`)
- **Selalu gunakan strip pendek biasa (`-`)** di seluruh dokumentasi, kode program, skrip JavaScript, template Blade, teks HTML, commit message, dan laporan.

---

## 4. UI/UX Consistency Guard & Aturan Desain
1. **Bahasa Desain ("Modern Harbour Tech"):**
   - Tema dasar: Dominan **Terang Bersih (Crisp Salt White & Fog Slate)**.
   - **Alasan:** Portal rekrutmen padat dengan teks kualifikasi, deskripsi lowongan, dan dokumen CV. Tampilan gelap pekat (full dark) membuat mata lelah (*eye strain*) dan tidak inklusif untuk semua kalangan.
   - Sentuhan gelap (*Deep Harbour Navy #0F172A*) hanya digunakan sebagai **aksen** (contoh: Navbar Header atau Hero Banner atas).
2. **Standardisasi Palet Warna (Tailwind Semantic Tokens):**
   - Dilarang mencampur warna *hardcoded hex* acak (seperti tiba-tiba memakai warna oranye kemerahan `#C8460B` di satu halaman sementara halaman lain memakai biru laut `#0284C7`).
   - Gunakan standar warna JobHarbour:
     - Primary: Deep Harbour Navy (`#0F172A`, `#1E293B`) & Oceanic Azure (`#0284C7`, `#0EA5E9`).
     - Accents: Lighthouse Gold / Amber (`#F59E0B`) untuk skor kecocokan.
     - Status: Emerald Green (`#10B981`) untuk diterima/offered, Crimson (`#EF4444`) untuk ditolak.
     - Background: Salt White (`#F8FAFC`) dan Fog Slate (`#F1F5F9`).
3. **Konsistensi Sudut Kartu (Border Radius):**
   - Standar kartu dan container: `rounded-xl` (12px).
   - Hindari ketidakseragaman ukuran rounded antar komponen.

---

## 5. Standar Rekayasa Backend & Keamanan Web Service
1. **No Cross-Database Queries:**
   - Arsitektur microservice mengisolasi database (`db_auth`, `db_jobs`, `db_applications`, `db_pipeline`). Komunikasi antar-layanan wajib melalui REST API.
2. **Proteksi Anti-IDOR (Insecure Direct Object Reference):**
   - Rekruter hanya boleh memanipulasi lowongan dan tahapan seleksi kandidat pada lowongan miliknya sendiri (`posted_by == auth_user.id`).
   - Pelamar hanya dapat membatalkan lamaran miliknya yang masih berstatus `submitted` (tolak dengan HTTP 409 jika sudah tahap interview).
3. **Format JSON Envelope Wajib:**
   ```json
   {
     "status": "success | error",
     "message": "Pesan deskriptif bahasa manusia",
     "data": {},
     "meta": { "timestamp": "ISO8601", "version": "1.0.0" }
   }
   ```
4. **Keamanan Konfigurasi:**
   - Kunci rahasia JWT, kata sandi, dan kredensial database wajib diisolasi di `.env` dan tidak boleh bocor ke commit Git.

---

## 6. Distribusi Kontribusi Git Tim Kelompok 4
Pastikan commit dan pull request didistribusikan merata ke 4 anggota sesuai peran:
- **Nabe'ela Ayu Ning Tyas Zahra:** Auth & Profile Service (`/api/auth/*`, `/api/profile/*`)
- **M. Fahren Andrean Rangkuti:** Job Catalog Service (`/api/jobs/*`)
- **Muhammad Yasir Ilham Nabil:** Application & Matchmaking Service (`/api/applications/*`)
- **Muhammad Affif (Mas Affif):** Recruitment Pipeline Service (`/api/pipelines/*`) & CI/CD Security

## 7. Arsitektur Stacking Context & Teleport Dropdown
1. **Aturan Stacking Hero vs Card:**
   - Hero Section wajib berada di layer dasar tanpa `z-index` yang menutupi komponen di bawahnya.
   - Card Statistik (`-mt-20`) wajib memiliki `z-20` agar menumpuk di atas banner laut hero.
2. **Aturan Popover / Dropdown Menu:**
   - Dropdown yang keluar dari area form pencarian WAJIB menggunakan positioning `fixed` dengan floating positioner JavaScript (setara `<Teleport to="body">`) dengan `z-index: 99999`.
   - Hal ini untuk mencegah clipping akibat `overflow-hidden` pada hero container.
3. **Standarisasi Ikon UI:**
   - Dilarang keras menggunakan tag font icon yang bisa membocorkan teks mentah ligatur (seperti `my_location` atau `public`).
   - Selalu gunakan inline SVG vektor murni yang terdefinisi rapi.
