# JOBHARBOUR - REAL GEOSPATIAL CAREER ATLAS
## Architecture & Engineering Specification Document

> Platform: JobHarbour Recruitment & Talent Matchmaking  
> Module: Interactive Geospatial Career Atlas (2D / 3D)  
> Status: Architecture Proposal & Baseline Audit (Pre-Implementation Deliverable)  
> Target Engine: MapLibre GL JS + Official Administrative Geometries (BIG/Kemendagri)

---

## 1. Audit Komponen Career Atlas Saat Ini

### 1.1 Kondisi Eksisting
Pada implementasi sebelumnya di `resources/views/pages/home.blade.php`, Career Atlas dibuat menggunakan **ilustrasi SVG statis/dekoratif**:
- Menggunakan polygon perkiraan kasar 5 pulau besar (koordinat fiktif `path d="M120 120 L210 230..."`).
- Titik koordinat industri ditaruh secara manual memakai persentase CSS absolute (`top: 72%; left: 30%`).
- Tidak memiliki sistem proyeksi peta (CRS), skala spasial, garis pantai riil, maupun batas administratif kabupaten/kota yang valid.
- Tidak dapat melakukan *pan*, *zoom*, pemadanan geospasial poligon, maupun rendering elevasi medan (3D terrain).

### 1.2 Keterbatasan Utama
1. **Tidak Ada Akurasi Geospasial:** Tidak dapat menghitung jarak geodesik (Haversine/Vincenty) antara pelamar dengan kawasan industri.
2. **Tidak Skalabel:** Menambah kawasan industri baru di Batang, Morowali, atau IKN membutuhkan penyesuaian posisi CSS manual satu per satu.
3. **Belum Ada Integrasi Data Spasial:** Tabel lowongan kerja (`job_vacancies`) saat ini hanya menyimpan teks nama kota (`varchar(255)`: misal "Tegal", "Bandung"), belum memiliki relasi spasial ke kode wilayah Kemendagri / BPS maupun koordinat lintang/bujur (Latitude/Longitude).

---

## 2. Rekomendasi Sumber Data Geografis Indonesia

Untuk memastikan keabsahan batas wilayah tanpa melanggar lisensi data dan tanpa membebani browser, berikut evaluasi dan pemilihan sumber data resmi:

| Lapisan Data | Sumber Resmi | Format & Resolusi | Sistem Koordinat | Lisensi / Ketentuan |
|---|---|---|---|---|
| **Batas Administrasi Provinsi & Kab/Kota** | **Badan Informasi Geospasial (BIG)** via Tanahair Indonesia / BPS Geospasial Kemendagri | GeoJSON & PMTiles Vector (Tingkat Ketelitian 1:50.000 disederhanakan via Visvalingam-Whyatt) | WGS84 (EPSG:4326 / Web Mercator EPSG:3857) | Lisensi Terbuka Pemerintah Indonesia (Pemerintah Republik Indonesia / Open Data) |
| **Basemap Jalan & Toponimi** | **OpenStreetMap (OSM)** / CARTO Positron / OSM Liberty | Vector Tiles / Raster Tile XYZ | EPSG:3857 | ODbL (Open Database License) dengan atribusi wajib |
| **Elevasi Medan (3D Terrain)** | **DEMNAS BIG** (resolusi 8.27m) atau **AWS Terrain Tiles / Terrarium Mapzen** | RGB Terrain-DEM Raster Tiles (`mapbox` / `terrarium` encoding) | WGS84 Web Mercator | Open Data Lisensi Publik (AWS Open Data / BIG) |
| **Kawasan Industri & KEK** | **Kemenperin (Kawasan Industri Aktif)** & Dewan Nasional KEK | Point & Polygon GeoJSON tervalidasi | EPSG:4326 | Resmi Publik Kementerian Perindustrian RI |

---

## 3. Arsitektur Rendering Dual Mode (2D & 3D)

Sistem menggunakan **satu sumber data geospasial terpusat** yang dirender secara fleksibel menggunakan **MapLibre GL JS**:

- **Mode 2D (Default):**
  - Kamera tegak lurus (`pitch: 0`, `bearing: 0`).
  - Ringan, cepat diakses dari HP dengan kartu seluler, fokus pada navigasi daftar lowongan dan seleksi daerah.
  - Membaca poligon kabupaten/kota untuk visualisasi kepadatan lowongan (*Choropleth / Cluster*).
- **Mode 3D (Eksplorasi Industri & Topografi):**
  - Mengaktifkan `map.setTerrain({ source: 'terrain-dem', exaggeration: 1.5 })`.
  - Kamera condong dramatis (`pitch: 55`, `bearing: -20`).
  - Menampilkan topografi kepulauan Indonesia: jalur maritim selat, pelabuhan alami, dataran tinggi industri, dan bukit kawasan IKN/Batang.

---

## 4. Skema Data Spasial & Provenansi (Data Provenance)

Untuk memenuhi standar integritas data dan transparansi sumber (tidak ada data dummy/tebakan), berikut skema entitas spasial yang dirancang:

### 4.1 Tabel Database Spasial: `administrative_regions`
```sql
CREATE TABLE administrative_regions (
    code VARCHAR(10) PRIMARY KEY, -- Kode Wilayah Kemendagri, cth: '33.76' untuk Kota Tegal
    parent_code VARCHAR(10) NULL, -- '33' untuk Jawa Tengah
    name VARCHAR(100) NOT NULL,
    level ENUM('province', 'regency_city') NOT NULL,
    latitude DECIMAL(10, 7) NOT NULL,
    longitude DECIMAL(10, 7) NOT NULL,
    bounding_box JSON NULL,       -- [min_lng, min_lat, max_lng, max_lat]
    provenance_source VARCHAR(100) DEFAULT 'Badan Informasi Geospasial (BIG)',
    verified_at TIMESTAMP NULL
);
```

### 4.2 Tabel Kawasan Industri & Klaster Teknologi: `industrial_zones`
```sql
CREATE TABLE industrial_zones (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    category ENUM('maritime_logistics', 'manufacturing_ev', 'technology_digital', 'energy_mining', 'agro_industry') NOT NULL,
    region_code VARCHAR(10) NOT NULL, -- FK ke administrative_regions
    latitude DECIMAL(10, 7) NOT NULL,
    longitude DECIMAL(10, 7) NOT NULL,
    polygon_geojson LONGTEXT NULL,    -- Poligon kawasan industri jika tersedia
    website_url VARCHAR(255) NULL,
    verification_status ENUM('official_kemenperin', 'verified_estate', 'approximate') NOT NULL,
    data_source VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (region_code) REFERENCES administrative_regions(code)
);
```

### 4.3 Pembaruan Non-Destruktif pada `job_vacancies`:
```sql
ALTER TABLE job_vacancies 
ADD COLUMN administrative_code VARCHAR(10) NULL AFTER location,
ADD COLUMN industrial_zone_id BIGINT UNSIGNED NULL AFTER administrative_code,
ADD COLUMN is_verified_location BOOLEAN DEFAULT FALSE AFTER industrial_zone_id,
ADD COLUMN location_source ENUM('direct_recruiter', 'official_career_page', 'unverified') DEFAULT 'direct_recruiter' AFTER is_verified_location;
```

---

## 5. Estimasi Performa, Efisiensi & Kebutuhan Infrastruktur

| Komponen | Beban Mentah | Strategi Optimasi | Ukuran Akhir di Browser |
|---|---|---|---|
| **Batas Administrasi Indonesia** | Poligon Detail >150 MB | Generalisasi Visvalingam (toleransi 0.005) + kompresi Gzip/Brotli | **~480 KB** (Provinsi lengkap) |
| **Map Rendering Runtime** | MapLibre GL Bundle | Dynamic `import('maplibre-gl')` (hanya dimuat saat section terlihat di viewport) | **~240 KB** gzipped |
| **Terrain Tiles (3D Elevation)** | Ribuan tile raster | Render on-demand sesuai tile viewport kamera + cache browser HTTP | **~1.2 MB** per sesi 3D |
| **Layer Interaktivitas Marker** | Query multi-tabel | Caching Redis 1 jam untuk agregasi skor lowongan per distrik | **~45 KB** JSON |

---

## 6. Rencana Tahapan Eksekusi Bertahap (Implementation Roadmap)

1. **Fase 1: Pondasi Geospasial (Dependencies & Seeder Wilayah):**
   - Install pustaka `maplibre-gl` dan siapkan styling MapLibre dengan palet warna JobHarbour (*Deep Navy & Oceanic Azure*).
   - Buat seeder tabel `administrative_regions` dan `industrial_zones` berisi titik koordinat valid kota-kota lowongan aktif (Tegal, Bandung, Semarang, Batang/Kendal, IKN, Batam).
2. **Fase 2: Implementasi Mode 2D Akurat:**
   - Pasang canvas MapLibre GL 2D di halaman Beranda menggantikan SVG polygon manual.
   - Muat geometri batas wilayah Indonesia asli (GeoJSON resmi).
3. **Fase 3: Layer Interaktif Kawasan Industri & Loker:**
   - Hubungkan marker distrik dengan database lowongan backend secara live.
   - Sediakan tombol sakelar layer (*Administrative Boundaries, Maritime & Logistics, Tech Hub, Active Jobs*).
4. **Fase 4: Implementasi Mode 3D Terrain:**
   - Tambahkan tombol sakelar **"2D / 3D Mode"**.
   - Integrasikan Terrain DEM tile raster untuk mengaktifkan kontur elevasi, kemiringan kamera (*pitch 55°*), dan shading pegunungan/pantai.
5. **Fase 5: Validasi Pengujian & Konsistensi UI:**
   - Verifikasi performa 60 FPS di desktop & mobile, pastikan tidak ada kebocoran memori webgl, dan periksa kesesuaian filter katalog.
