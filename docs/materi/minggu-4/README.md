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
| [`Tugas_Minggu_4_API_Design_Document.pdf`](Tugas_Minggu_4_API_Design_Document.pdf) | PDF (A4) | Berkas cetak resmi siap kumpul di LMS (4 halaman A4) dengan kop logo baru UHN. |
| [`Tugas_Minggu_4_API_Design_Document.html`](Tugas_Minggu_4_API_Design_Document.html) | HTML | Sumber template web dokumen dengan styling cetak CSS presisi. |

---

## 📋 Cakupan 8 Bagian Dokumen (Bagian A s.d. H)

1. **Bagian A - Identitas Proyek & Anggota Tim:** Nama proyek, anggota kelompok (4 mahasiswa beserta NIM dan peran microservice), base URL gateway/service, versi API (`v1`), dan konvensi penamaan (`snake_case` dan plural nouns).
2. **Bagian B - Pemodelan Resource & Relasi:** 8 entitas resource utama (`users`, `profiles`, `skills`, `jobs`, `requirements`, `applications`, `pipelines`, `interviews`) lengkap dengan tipe data atribut dan diagram ERD ringkas.
3. **Bagian C - Matriks Endpoint:** 18 endpoint RESTful yang mencakup rute publik dan terotentikasi, pemetaan kata kerja HTTP semantik, peran aktor, serta status code respon.
4. **Bagian D - Desain Query:** Standar parameter filtering, searching (`?q=`), sorting (`?sort=-created_at`), dan pagination (`?page=1&per_page=15`) dengan batas maksimum untuk mencegah DoS.
5. **Bagian E - Representasi Data:** Contoh payload JSON Envelope seragam untuk item tunggal (`200 OK`), collection terpaginasi (`meta.pagination`), dan submit lamaran (`201 Created`).
6. **Bagian F - Penanganan Error & Status Code:** Format error envelope standar, contoh error validasi form (`422 Unprocessable Entity`), dan contoh konflik aturan bisnis (`409 Conflict`).
7. **Bagian G - Aksi Non-CRUD:** Pemodelan proses bisnis kompleks ke pola RESTful (kalkulasi matchmaking otomatis, transisi tahapan seleksi state machine, dan pembatalan soft-state).
8. **Bagian H - Catatan Keputusan Desain:** Rangkuman keputusan arsitektur (universal envelope, page-based pagination, pemisahan PUT vs PATCH, dan isolasi database-per-service).
