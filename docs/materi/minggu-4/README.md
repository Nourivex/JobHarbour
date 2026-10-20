# Tugas Kelompok Minggu ke-4: API Design Document v1

> **Mata Kuliah:** Pemrograman Web Service (5537344 / 3 SKS)  
> **Dosen Pengampu:** Zaenul Arif, S.Kom., M.Kom.  
> **Platform Proyek:** PaduKerja (Platform Rekrutmen Terintegrasi Berbasis Microservice)  
> **Kelompok:** Kelompok 4 UHN  
> **Milestone:** Bagian Pertama Milestone M2 - API Blueprint  

---

## 📁 Berkas Luaran Tugas Minggu ke-4

Sesuai instruksi pada Modul Ajar 04 (Bab 22: Tugas Kelompok Minggu ke-4), luaran tugas mencakup dokumen Markdown di repository dan berkas PDF resmi untuk pengumpulan di LMS:

| Berkas | Format | Deskripsi |
|---|---|---|
| [`docs/api-design.md`](../../api-design.md) | Markdown | Dokumen perancangan API resmi kelompok di root folder `docs/`. |
| [`Tugas_Minggu_4_API_Design_Document.html`](Tugas_Minggu_4_API_Design_Document.html) | HTML (A4) | Sumber cetak PDF untuk LMS (13 halaman A4), isi sama dengan `docs/api-design.md`. |
| [`Lembar_Kerja_Workshop_Minggu_4.html`](Lembar_Kerja_Workshop_Minggu_4.html) | HTML (A4) | Lembar kerja Aktivitas Workshop Desain 20.1 - 20.4 (tugas LMS "Aktivitas Workshop Desain REST API"). |
| [`diagrams/resource_relationship_erd.svg`](diagrams/resource_relationship_erd.svg) | SVG / PNG | ERD ringkas 13 resource data, dikelompokkan per service. |

> File PDF tidak disimpan di repository (`*.pdf` ada di `.gitignore`). Untuk membuat PDF, buka file HTML di browser lalu Print (Ctrl+P), pilih **Save as PDF**, ukuran A4, dan matikan opsi header/footer.

---

## 📋 Cakupan Dokumen (Bagian A s.d. H + Lampiran)

1. **Bagian A - Identitas:** Nama proyek, anggota kelompok (NIM dan service), base URL gateway/service, versi API (`v1`), bahasa penamaan, dan konvensi penamaan.
2. **Bagian B - Resource & Relasi:** 14 resource (`users`, `me`, `profiles`, `skills`, `user_skills`, `categories`, `jobs`, `job_requirements`, `applications`, `pipelines`, `pipeline_transitions`, `assessments`, `interviews`, `offerings`) beserta tipe data, jenis (collection, singleton, sub-resource), tabel relasi, dan ERD.
3. **Bagian C - Endpoint Matrix:** 40 endpoint (mencakup seluruh worksheet Minggu 3 keempat anggota), dikelompokkan per service, dengan method, URI, fungsi, aktor, status sukses, dan status gagal. POST mengembalikan `201` + `Location`, DELETE mengembalikan `204`.
4. **Bagian D - Desain Query:** Filter, search (`?q=`), sort (`?sort=-created_at`), dan pagination (`?page=&per_page=`) untuk 4 endpoint collection utama (`/jobs`, `/jobs/{id}/applications`, `/pipelines`, `/applications`) ditambah D.5 untuk 7 collection lainnya, sehingga seluruh 11 collection memiliki whitelist, nilai default, dan batas maksimum.
5. **Bagian E - Representasi:** 5 contoh response (item, 2 collection dengan `meta` dan `links`, create `201`, delete `204`).
6. **Bagian F - Error & Status:** Format error seragam dengan `code`, contoh `422` dan dua contoh `409`, daftar 14 kode error, dan ringkasan status code.
7. **Bagian G - Aksi Non-CRUD:** Perhitungan skor matchmaking, transisi tahap seleksi (`POST /pipelines/{id}/transitions`), penarikan lamaran (soft delete), serta assessment, wawancara, dan penawaran kerja sebagai sub-resource pipeline.
8. **Bagian H - Catatan Keputusan:** 16 keputusan desain beserta alasannya.
9. **Lampiran - Peer Review:** Hasil pemeriksaan checklist 8 butir Aktivitas 4 beserta perbaikannya (7 butir terpenuhi; butir 6 diperbaiki lewat D.5).
