# Tugas Kelompok Minggu ke-5: OpenAPI v1

> **Mata Kuliah:** Pemrograman Web Service (5537344 / 3 SKS)  
> **Dosen Pengampu:** Zaenul Arif, S.Kom., M.Kom.  
> **Platform Proyek:** JobHarbour (Platform Rekrutmen Terintegrasi Berbasis Microservice)  
> **Kelompok:** Kelompok 4 UHN  
> **Milestone:** Bagian Kedua Milestone M2 - API Blueprint  

---

## Berkas Luaran

| Berkas | Keterangan |
|---|---|
| [`docs/openapi.yaml`](../../openapi.yaml) | Kontrak OpenAPI 3.1.0 (40 operation), diturunkan dari [`docs/api-design.md`](../../api-design.md). |
| [`Tugas_Minggu_5_OpenAPI_v1_Lint_Swagger.html`](Tugas_Minggu_5_OpenAPI_v1_Lint_Swagger.html) | Sumber PDF lampiran LMS: pemenuhan ketentuan, hasil lint, hasil uji mock, dan screenshot Swagger UI. |
| [`screenshots/`](screenshots) | Screenshot Swagger UI yang dipakai di lampiran. |

> File PDF tidak disimpan di repository (`*.pdf` ada di `.gitignore`). Buka file HTML di browser lalu Print (Ctrl+P), pilih **Save as PDF**, ukuran A4, dan matikan opsi header/footer.

---

## Pemenuhan Ketentuan (Modul 05 bagian 24)

| Kriteria | Kondisi |
|---|---|
| Lokasi | `docs/openapi.yaml`, `openapi: 3.1.0`, `info.version: 1.0.0` |
| Cakupan | 40 operation pada 25 path (seluruh endpoint API Design Document v1) |
| Components | 61 schema termasuk `Error` dan `ValidationError`; input dan output terpisah; parameter reusable `Page`, `PerPage`, `Id`, `SkillId`, `RequirementId` |
| Responses | Seluruh operation memiliki response 2xx dan 4xx |
| Query | `sort` memakai `enum` + `default`; `per_page` memakai `default` + `maximum` pada seluruh collection |
| Keamanan | `bearerAuth` (JWT) global; 7 endpoint publik memakai `security: []` |
| Contoh | Contoh request dan response realistis (login, daftar lowongan, kirim lamaran, error 409/422) |
| Kualitas | Redocly lint: valid, 0 error, 1 warning (`no-server-example.com` karena server localhost) |

---

## Cara Memeriksa Kontrak

```bash
# Lint (target 0 error)
npx @redocly/cli lint docs/openapi.yaml

# Mock server di http://127.0.0.1:4010
npx @stoplight/prism-cli mock docs/openapi.yaml

# Contoh: per_page melebihi maximum ditolak 422
curl "http://127.0.0.1:4010/jobs?per_page=500"
```

Untuk melihat dokumentasi interaktif, buka `docs/openapi.yaml` di [Swagger Editor](https://editor.swagger.io) (File, Import file).
