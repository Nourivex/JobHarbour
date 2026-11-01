# JOBHARBOUR GEOSPATIAL DATA PROVENANCE & DATASET MAINTENANCE GUIDE
## Dokumentasi Sumber Data, Lisensi, dan Prosedur Pembaruan Dataset

> **Platform:** JobHarbour Recruitment Platform  
> **Modul:** Real Geospatial Career Atlas (Phase 1 Baseline)  
> **Status:** Terverifikasi di Lingkungan Lokal (DDEV / MariaDB 11.8)

---

## 1. Inventaris Sumber Data Resmi & Lisensi

| Entitas Spasial | Sumber Data Primer | Edisi / Versi | Sistem Koordinat (CRS) | Lisensi / Ketentuan Penggunaan |
|---|---|---|---|---|
| **Batas Wilayah Administratif** | Badan Informasi Geospasial (BIG) & Ditjen Bina Adwil Kemendagri | Master Wilayah Kemendagri 2024 Semester 1 | EPSG:4326 (WGS84 Lat/Long) | Lisensi Data Terbuka Pemerintah Indonesia (Data Publik Republik Indonesia) |
| **Kawasan Industri & KEK** | Kementerian Perindustrian (SIINas) & Dewan Nasional KEK | Direktori Kawasan Industri Kemenperin 2024 | EPSG:4326 (WGS84) | Data Publik Pemerintah RI / Terverifikasi Kemenperin |
| **Basemap Ubin Raster** | OpenStreetMap (OSM) / CARTO Voyager | CARTO Tiles 2024 | Web Mercator (EPSG:3857) | Open Database License (ODbL) dengan atribusi wajib pada canvas |

---

## 2. Struktur Data Spasial & Standar Provenansi

### A. Tabel `administrative_regions`
Mencatat kode wilayah resmi, relasi hierarki (Provinsi -> Kabupaten/Kota), serta atribut kualitas geometri:
- `code`: Kode wilayah Kemendagri (contoh: `33.76` untuk Kota Tegal).
- `parent_code`: Kode provinsi induk (`33` untuk Jawa Tengah).
- `bounding_box`: `[min_lng, min_lat, max_lng, max_lat]` untuk zoom bounding box instan tanpa membebani browser.
- `geometry_quality`: `centroid_only` (titik pusat), `simplified_topology`, atau `boundary_polygon`.
- `provenance_source` & `source_edition`: Pelacakan asal dataset secara eksplisit.

### B. Tabel `industrial_zones`
Memisahkan secara tegas titik koordinat kawasan industri dari titik pusat kota administratif:
- Contoh: Kawasan Pelabuhan & Dok Tegal berada di koordinat maritim `-6.8521000, 109.1352000`, terpisah dari titik Balai Kota Tegal (`-6.8694000, 109.1256000`).
- `verification_status`:
  - `official_kemenperin`: Terdaftar di sistem informasi industri nasional Kemenperin.
  - `verified_estate`: Kawasan industri privat yang tervalidasi dokumen legalnya.
  - `approximate_area`: Perkiraan area (tidak diklaim sebagai koordinat presisi).

### C. Kolom Tambahan Non-Destruktif pada `job_vacancies`
- `administrative_code`: Relasi foreign key ke kode wilayah resmi.
- `industrial_zone_id`: Relasi foreign key ke kawasan industri terkait.
- `job_source_type`: Membedakan lowongan mitra langsung (`direct_recruiter`), kurasi web karir resmi (`official_career_page`), atau pusat karir kampus (`campus_partnership`).
- `location_verification_status`: Status verifikasi lokasi (`verified_industrial`, `verified_site`, `approximate_city`, `unverified`).

---

## 3. Prosedur Pembaruan Dataset (Maintenance & Update)

1. **Memperbarui Data Wilayah Baru:**
   - Jalankan seeder master geospasial:
     ```bash
     ddev artisan db:seed --class=Database\\Seeders\\GeospatialMasterSeeder
     ```
2. **Menambahkan Poligon Wilayah Baru:**
   - Simpan berkas GeoJSON terkompresi di direktori `public/geo/` atau simpan ringkasan bounding box di kolom `bounding_box`.
3. **Memverifikasi Layanan API Spasial:**
   - Panggil endpoint: `GET /api/geo/atlas` untuk memastikan payload FeatureCollection valid.
