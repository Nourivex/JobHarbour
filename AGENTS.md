# JobHarbour - Agent Guidelines

> Platform Rekrutmen Terintegrasi Berbasis Microservice dengan Mesin Pemadanan Keahlian Otomatis.
> Filosofi: Pelabuhan para talenta dan pencari kerja sebelum berlayar menuju dunia kerja profesional.
> Mata Kuliah: Pemrograman Web Service (5537344 / 3 SKS) - Universitas Harkat Negeri - Kelompok 4.

---

## User Identity & Communication Protocol
- User Name: **Affif** (panggil dengan hormat dan akrab: **Mas Affif** atau **Ka Affif**).
- Gaya Komunikasi: Santai, solutif, to-the-point, ramah sesama mahasiswa teknik informatika, dan tidak bertele-tele.

---

## Project Overview

JobHarbour is a recruitment platform built with **4 independent Laravel microservices** communicating via **REST API JSON**. Each service has its own database (database-per-service pattern).

### Microservices

| # | Service | Port | Database | Owner |
|---|---------|------|----------|-------|
| 1 | **Auth & Profile Service** | `:8001` | `db_auth` | NABE'ELA AYU NING TYAS ZAHRA |
| 2 | **Job Catalog Service** | `:8002` | `db_jobs` | M. FAHREN ANDREAN RANGKUTI |
| 3 | **Application & Matchmaking Service** | `:8003` | `db_applications` | MUHAMMAD YASIR ILHAM NABIL |
| 4 | **Recruitment Pipeline Service** | `:8004` | `db_pipeline` | MUHAMMAD AFFIF |

---

## Prerequisites

Verify that PHP and Composer are available:

```sh
php -v
composer -V
```

If either command is unavailable, install using the appropriate command for the user's OS:
- **macOS:** `/bin/bash -c "$(curl -fsSL https://php.new/install/mac/8.5)"`
- **Linux:** `/bin/bash -c "$(curl -fsSL https://php.new/install/linux/8.5)"`
- **Windows:** `Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://php.new/install/windows/8.5'))`

---

## Development Environment

- **Runtime:** PHP 8.4+, Composer, Node.js 24+
- **Framework:** Laravel 13.x per service
- **Database:** MariaDB (one instance per service)
- **Local Environment:** DDEV (preferred) or local PHP fallback
- **CLI Helper:** `./dev --setup`, `./dev migrate`, `./dev test`, `./dev <artisan-command>`

---

## Strict Development Rules & Best Practices

1. **Anti-Slop AI Policy:**
   - Dilarang keras menggunakan gaya bahasa robotik, frasa AI klise (contoh: "tapestry", "delve", "pivotal", "in conclusion", "seperti pelabuhan untuk kota Tegal").
   - Bahasa harus terdengar wajar buatan tangan manusia (*human-crafted*), lugas, to-the-point, dan berbobot.
2. **Punctuation Rule (Strict):**
   - Dilarang keras menggunakan strip panjang (*em-dash* U+2014 atau *en-dash* U+2013) di seluruh dokumentasi, kode program, git commit, dan berkas HTML/Blade.
   - Selalu gunakan strip pendek biasa (-).
3. **UI/UX Consistency Guard:**
   - Pertahankan tema dominan terang (*Crisp Salt White & Slate*) dengan aksen *Deep Harbour Navy*. Jangan membuat halaman gelap pekat yang melelahkan mata pelamar.
   - Gunakan token warna Tailwind CSS yang terstandarisasi, jangan mencampur warna *hardcoded hex* acak.
   - Konsistensi border-radius (default kartu: `rounded-xl` / 12px).
4. **Git Contribution Balance:**
   - Setiap fitur yang dikerjakan harus disesuaikan dengan tanggung jawab pemilik modul (Nabe'ela, Fahren, Yasir, Affif).
5. **Memory & History Tracking:**
   - Selalu rujuk dan perbarui `MEMORY.md` untuk melacak status pengerjaan agar konteks tidak hilang saat sesi terestart.

---

## REST API Standards

1. **JSON Envelope** - Setiap respons menggunakan struktur:
   ```json
   {
     "status": "success | error",
     "message": "Pesan deskriptif bahasa manusia",
     "data": {},
     "meta": { "timestamp": "ISO8601", "version": "1.0.0" }
   }
   ```
2. **Status Codes:**
   - `200` OK, `201` Created
   - `400` Bad Request, `401` Unauthorized, `403` Forbidden (Anti-IDOR)
   - `404` Not Found, `409` Conflict (Duplicate/Illegal cancel), `422` Validation Error
   - `500` Internal Server Error
3. **Matchmaking Engine Formula:**
   ```
   Match Score = (Σ weight of matched skills) / (Σ weight of all required skills) × 100%
   ```
   Bobot: `required = 3`, `important = 2`, `nice_to_have = 1`.
4. **Pipeline Stages Sequence:**
   `Screening → Assessment → Interview → Offering / Rejected`

---

## Key Documentation

| Berkas | Tujuan |
|---|---|
| `RULES.md` | Aturan baku anti-slop, penulisan, styling CSS, dan keamanan kode |
| `MEMORY.md` | Catatan riwayat progres pengerjaan, status sprint, dan checkpoint proyek |
| `README.md` | Ringkasan proyek, setup lokal, dan tim pengembang |
| `docs/ARCHITECTURE.md` | Arsitektur microservice, formula matching, dan struktur data |
| `docs/API_CONTRACT_STANDARDS.md` | Standar kontrak REST API, envelope, dan mock data |
