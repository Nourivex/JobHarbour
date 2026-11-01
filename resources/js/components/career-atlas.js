import * as maplibregl from 'maplibre-gl';
import 'maplibre-gl/dist/maplibre-gl.css';

// Konfigurasi Worker URL statis mandiri agar tidak bergantung pada CDN eksternal
maplibregl.setWorkerUrl('/vendor/maplibre/maplibre-gl-worker.js');

// Pustaka Ikon Vektor Lucide Semantik (Bebas Font Glitch)
const LUCIDE_ICONS = {
    'anchor': '<circle cx="12" cy="5" r="3"/><line x1="12" y1="22" x2="12" y2="8"/><path d="M5 12H2a10 10 0 0 0 20 0h-3"/>',
    'ship': '<path d="M2 21c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1 .6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"/><path d="M19.38 20A11.6 11.6 0 0 0 21 14l-9-4-9 4c0 2.9.94 5.34 2.81 7.76"/><path d="M19 13V7a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v6"/><line x1="12" y1="1" x2="12" y2="5"/>',
    'factory': '<path d="M2 20a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8l-7 5V8l-7 5V4a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M17 18h1"/><path d="M12 18h1"/><path d="M7 18h1"/>',
    'cpu': '<rect x="4" y="4" width="16" height="16" rx="2"/><rect x="9" y="9" width="6" height="6"/><path d="M15 2v2"/><path d="M9 2v2"/><path d="M20 15h2"/><path d="M20 9h2"/><path d="M9 20v2"/><path d="M15 20v2"/><path d="M2 9h2"/><path d="M2 15h2"/>',
    'pickaxe': '<path d="m14 10 6.5-6.5a1.5 1.5 0 0 1 2 2L16 12"/><path d="M14 10 8 16a2.83 2.83 0 0 1-4 0 2.83 2.83 0 0 1 0-4l6-6"/><path d="m18 8 2 2"/><path d="m2 2 4 4"/>',
    'zap': '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>',
    'leaf': '<path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/><path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/>',
    'building-2': '<path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/>'
};

const CATEGORY_COLORS = {
    'maritime_logistics': '#0284C7',
    'manufacturing_ev': '#F59E0B',
    'technology_digital': '#10B981',
    'energy_mining': '#EF4444',
    'agro_industry': '#14B8A6'
};

export class CareerAtlas {
    constructor(containerId, options = {}) {
        this.containerId = containerId;
        this.options = options;
        this.map = null;
        this.currentLevel = 0; // 0: Indonesia, 1: Provinsi, 2: Kab/Kota, 3: Kecamatan
        this.selectedProvince = null;
        this.selectedRegency = null;
        this.selectedDistrict = null;
        this.provincesData = null;
        this.regenciesData = null;
        this.industryData = null;
        this.markersOnMap = [];
        this.activeCategoryFilter = 'all';
    }

    async init() {
        const container = document.getElementById(this.containerId);
        if (!container) return;

        const nauticalStyle = {
            version: 8,
            sources: {},
            layers: [
                {
                    id: 'background',
                    type: 'background',
                    paint: { 'background-color': '#081628' }
                }
            ]
        };

        this.map = new maplibregl.Map({
            container: this.containerId,
            style: nauticalStyle,
            center: [118.0, -2.2],
            zoom: 4.35,
            minZoom: 3.8,
            maxZoom: 16,
            maxBounds: [
                [92.0, -13.0],
                [144.0, 8.5]
            ],
            attributionControl: false
        });

        this.map.addControl(new maplibregl.NavigationControl({ showCompass: true }), 'top-right');

        this.map.on('load', async () => {
            await this.setupSourcesAndLayers();
            this.bindUIControls();
            this.bindMapEvents();
        });
    }

    async setupSourcesAndLayers() {
        try {
            // 1. Batas 38 Provinsi (Level 1)
            const provRes = await fetch('/api/geo/provinces', { headers: { 'Accept': 'application/json' } });
            this.provincesData = await provRes.json();
            this.map.addSource('indonesia-provinces', { type: 'geojson', data: this.provincesData, generateId: true });

            this.map.addLayer({
                id: 'provinces-fill',
                type: 'fill',
                source: 'indonesia-provinces',
                paint: {
                    'fill-color': [
                        'case',
                        ['boolean', ['feature-state', 'hover'], false],
                        '#0284C7',
                        '#132B45'
                    ],
                    'fill-opacity': 0.95
                }
            });

            this.map.addLayer({
                id: 'provinces-outline',
                type: 'line',
                source: 'indonesia-provinces',
                paint: {
                    'line-color': [
                        'case',
                        ['boolean', ['feature-state', 'hover'], false],
                        '#38BDF8',
                        '#1E3A5F'
                    ],
                    'line-width': ['case', ['boolean', ['feature-state', 'hover'], false], 2.5, 1.2]
                }
            });

            // 2. Batas 518 Kabupaten / Kota (Level 2)
            const regRes = await fetch('/api/geo/regencies', { headers: { 'Accept': 'application/json' } });
            this.regenciesData = await regRes.json();
            this.map.addSource('indonesia-regencies', { type: 'geojson', data: this.regenciesData, generateId: true });

            this.map.addLayer({
                id: 'regencies-fill',
                type: 'fill',
                source: 'indonesia-regencies',
                minzoom: 5.5,
                paint: {
                    'fill-color': [
                        'case',
                        ['boolean', ['feature-state', 'hover'], false],
                        '#0284C7',
                        '#183654'
                    ],
                    'fill-opacity': 0.8
                }
            });

            this.map.addLayer({
                id: 'regencies-outline',
                type: 'line',
                source: 'indonesia-regencies',
                minzoom: 5.5,
                paint: {
                    'line-color': '#38BDF8',
                    'line-width': 1.0,
                    'line-dasharray': [2, 1]
                }
            });

            // 3. Batas Kecamatan (Level 3)
            this.map.addSource('indonesia-districts', {
                type: 'geojson',
                data: { type: 'FeatureCollection', features: [] },
                generateId: true
            });

            this.map.addLayer({
                id: 'districts-fill',
                type: 'fill',
                source: 'indonesia-districts',
                minzoom: 8.0,
                paint: {
                    'fill-color': [
                        'case',
                        ['boolean', ['feature-state', 'hover'], false],
                        '#F26A1B',
                        '#1A4064'
                    ],
                    'fill-opacity': 0.85
                }
            });

            this.map.addLayer({
                id: 'districts-outline',
                type: 'line',
                source: 'indonesia-districts',
                minzoom: 8.0,
                paint: {
                    'line-color': '#FDBA74',
                    'line-width': 1.5
                }
            });

            // 4. Kawasan Industri & Pelabuhan Nasional
            const indRes = await fetch('/api/geo/industry-zones', { headers: { 'Accept': 'application/json' } });
            this.industryData = await indRes.json();
            
            // Render Marker Semantik HTML dengan Ikon Lucide Nyata
            this.renderSemanticHTMLMarkers();

        } catch (err) {
            console.error('CareerAtlas initialization error:', err);
        }
    }

    renderSemanticHTMLMarkers() {
        // Bersihkan marker lama jika ada
        this.markersOnMap.forEach(m => m.remove());
        this.markersOnMap = [];

        if (!this.industryData || !Array.isArray(this.industryData.features)) return;

        this.industryData.features.forEach(feat => {
            const p = feat.properties;
            const coords = feat.geometry.coordinates;
            const cat = p.category;
            const iconName = p.semantic_icon || 'factory';
            const color = CATEGORY_COLORS[cat] || '#C8460B';
            const iconSvg = LUCIDE_ICONS[iconName] || LUCIDE_ICONS['factory'];

            // Elemen Marker HTML dengan Ikon Semantik Vektor Nyata
            const el = document.createElement('div');
            el.className = 'career-atlas-marker group cursor-pointer relative transition-transform duration-150 hover:scale-125';
            el.setAttribute('data-zone-id', p.id);
            el.setAttribute('data-category', cat);
            
            el.innerHTML = `
                <!-- Beacon Pulse -->
                <div class="absolute -inset-1.5 rounded-full opacity-30 animate-ping pointer-events-none" style="background-color: ${color};"></div>
                <!-- Core Marker Box -->
                <div class="relative w-8 h-8 rounded-xl flex items-center justify-center text-white shadow-xl border-2 border-white ring-1 ring-black/20" style="background-color: ${color};">
                    <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        ${iconSvg}
                    </svg>
                </div>
            `;

            el.addEventListener('click', (e) => {
                e.stopPropagation();
                this.showIndustrialIntelligencePanel(p);
            });

            const marker = new maplibregl.Marker({ element: el })
                .setLngLat(coords)
                .addTo(this.map);

            this.markersOnMap.push(marker);
        });
    }

    bindMapEvents() {
        const tooltip = document.getElementById('atlas-hover-tooltip');

        // Hover & Click Level 1: Provinsi
        let hoveredProvId = null;
        this.map.on('mousemove', 'provinces-fill', (e) => {
            if (this.map.getZoom() >= 5.5) return;
            if (e.features.length > 0) {
                if (hoveredProvId !== null) {
                    this.map.setFeatureState({ source: 'indonesia-provinces', id: hoveredProvId }, { hover: false });
                }
                hoveredProvId = e.features[0].id;
                this.map.setFeatureState({ source: 'indonesia-provinces', id: hoveredProvId }, { hover: true });
                this.map.getCanvas().style.cursor = 'pointer';

                const p = e.features[0].properties;
                if (tooltip) {
                    tooltip.classList.remove('hidden');
                    tooltip.style.left = `${e.point.x + 15}px`;
                    tooltip.style.top = `${e.point.y + 15}px`;
                    tooltip.innerHTML = `
                        <div class="font-extrabold text-xs text-white uppercase tracking-wider">${p.region_name || p.PROVINSI}</div>
                        <div class="text-[10px] text-sky-400 font-semibold mt-0.5">Provinsi (Level 1) • Kode: ${p.region_code}</div>
                        <div class="text-[9px] text-slate-300 mt-0.5">Klik untuk melihat Kabupaten/Kota</div>
                    `;
                }
            }
        });

        this.map.on('mouseleave', 'provinces-fill', () => {
            if (hoveredProvId !== null) {
                this.map.setFeatureState({ source: 'indonesia-provinces', id: hoveredProvId }, { hover: false });
            }
            hoveredProvId = null;
            if (tooltip) tooltip.classList.add('hidden');
        });

        this.map.on('click', 'provinces-fill', (e) => {
            if (this.map.getZoom() >= 5.5) return;
            const p = e.features[0].properties;
            this.handleProvinceClick(p, e.lngLat);
        });

        // Hover & Click Level 2: Kabupaten / Kota
        let hoveredRegId = null;
        this.map.on('mousemove', 'regencies-fill', (e) => {
            if (this.map.getZoom() < 5.5 || this.map.getZoom() >= 8.5) return;
            if (e.features.length > 0) {
                if (hoveredRegId !== null) {
                    this.map.setFeatureState({ source: 'indonesia-regencies', id: hoveredRegId }, { hover: false });
                }
                hoveredRegId = e.features[0].id;
                this.map.setFeatureState({ source: 'indonesia-regencies', id: hoveredRegId }, { hover: true });
                this.map.getCanvas().style.cursor = 'pointer';

                const p = e.features[0].properties;
                const admType = p.admin_type ? p.admin_type.toUpperCase() : (p.region_name?.toLowerCase().includes('kota') ? 'KOTA' : 'KABUPATEN');

                if (tooltip) {
                    tooltip.classList.remove('hidden');
                    tooltip.style.left = `${e.point.x + 15}px`;
                    tooltip.style.top = `${e.point.y + 15}px`;
                    tooltip.innerHTML = `
                        <div class="font-extrabold text-xs text-white uppercase tracking-wider">${p.region_name}</div>
                        <div class="text-[10px] text-sky-400 font-semibold mt-0.5">${admType} (Level 2) • ${p.province_name}</div>
                        <div class="text-[10px] text-slate-300">Kode Wilayah: ${p.region_code}</div>
                        <div class="text-[9px] text-amber-300 mt-0.5">Klik untuk melihat Kecamatan</div>
                    `;
                }
            }
        });

        this.map.on('mouseleave', 'regencies-fill', () => {
            if (hoveredRegId !== null) {
                this.map.setFeatureState({ source: 'indonesia-regencies', id: hoveredRegId }, { hover: false });
            }
            hoveredRegId = null;
            if (tooltip) tooltip.classList.add('hidden');
        });

        this.map.on('click', 'regencies-fill', (e) => {
            if (this.map.getZoom() < 5.5 || this.map.getZoom() >= 8.5) return;
            const p = e.features[0].properties;
            this.handleRegencyClick(p, e.lngLat);
        });

        // Hover & Click Level 3: Kecamatan
        let hoveredDistId = null;
        this.map.on('mousemove', 'districts-fill', (e) => {
            if (e.features.length > 0) {
                if (hoveredDistId !== null) {
                    this.map.setFeatureState({ source: 'indonesia-districts', id: hoveredDistId }, { hover: false });
                }
                hoveredDistId = e.features[0].id;
                this.map.setFeatureState({ source: 'indonesia-districts', id: hoveredDistId }, { hover: true });
                this.map.getCanvas().style.cursor = 'pointer';

                const p = e.features[0].properties;
                if (tooltip) {
                    tooltip.classList.remove('hidden');
                    tooltip.style.left = `${e.point.x + 15}px`;
                    tooltip.style.top = `${e.point.y + 15}px`;
                    tooltip.innerHTML = `
                        <div class="font-extrabold text-xs text-orange-400 uppercase tracking-wider">Kecamatan ${p.district_name}</div>
                        <div class="text-[10px] text-white mt-0.5">${p.regency_name}, ${p.province_name}</div>
                        <div class="text-[10px] text-sky-300 font-mono">Kode Wilayah: ${p.district_code}</div>
                    `;
                }
            }
        });

        this.map.on('mouseleave', 'districts-fill', () => {
            if (hoveredDistId !== null) {
                this.map.setFeatureState({ source: 'indonesia-districts', id: hoveredDistId }, { hover: false });
            }
            hoveredDistId = null;
            if (tooltip) tooltip.classList.add('hidden');
        });

        this.map.on('click', 'districts-fill', (e) => {
            const p = e.features[0].properties;
            this.handleDistrictClick(p, e.lngLat);
        });
    }

    async handleProvinceClick(props, lngLat) {
        this.currentLevel = 1;
        this.selectedProvince = props.region_name || props.PROVINSI;
        this.selectedRegency = null;
        this.selectedDistrict = null;
        this.updateHierarchyBreadcrumb();

        this.map.flyTo({
            center: [lngLat.lng, lngLat.lat],
            zoom: 6.8,
            speed: 1.2,
            essential: true
        });

        this.showRegionPanelInfo({
            level: 'Provinsi (Level 1)',
            code: props.region_code,
            name: this.selectedProvince,
            parent: 'Republik Indonesia',
            note: 'Menampilkan batas Kabupaten dan Kota resmi BATAS_KABKOTA_AR_2026. Klik salah satu Kabupaten/Kota untuk melihat Kecamatan.'
        });
    }

    async handleRegencyClick(props, lngLat) {
        this.currentLevel = 2;
        this.selectedRegency = props.region_name;
        // Jamin province name sinkron 100% dari relasi data induk kab/kota
        this.selectedProvince = props.province_name || this.selectedProvince;
        this.selectedDistrict = null;
        const regCode = props.region_code;
        this.updateHierarchyBreadcrumb();

        // Lazy load batas kecamatan
        let districtAvailable = false;
        try {
            const distRes = await fetch(`/api/geo/districts?regency_code=${encodeURIComponent(regCode)}`);
            const distData = await distRes.json();
            
            if (this.map.getSource('indonesia-districts')) {
                this.map.getSource('indonesia-districts').setData(distData);
                districtAvailable = (distData.features && distData.features.length > 0);
            }
        } catch (e) {
            console.error('Gagal memuat batas kecamatan:', e);
        }

        this.map.flyTo({
            center: [lngLat.lng, lngLat.lat],
            zoom: 9.2,
            speed: 1.2,
            essential: true
        });

        const statusNote = districtAvailable ?
            `Menampilkan batas resmi Kecamatan di wilayah ${this.selectedRegency}. Klik salah satu kecamatan untuk melihat kode dan hierarki wilayah.` :
            `Dataset batas kecamatan untuk ${this.selectedRegency} sedang dalam tahap standarisasi topologi BIG (Level 2 Aktif).`;

        this.showRegionPanelInfo({
            level: `${props.admin_type === 'kota' ? 'Kota' : 'Kabupaten'} (Level 2)`,
            code: regCode,
            name: this.selectedRegency,
            parent: props.province_name,
            note: statusNote
        });
    }

    handleDistrictClick(props, lngLat) {
        this.currentLevel = 3;
        this.selectedDistrict = props.district_name;
        this.selectedRegency = props.regency_name;
        this.selectedProvince = props.province_name;
        this.updateHierarchyBreadcrumb();

        this.showRegionPanelInfo({
            level: 'Kecamatan (Level 3)',
            code: props.district_code,
            name: `Kecamatan ${props.district_name}`,
            parent: `${props.regency_name}, ${props.province_name}`,
            note: `Status Geometri: Poligon Terverifikasi (BIG & BPS Subdistricts). Kode Administrasi Kemendagri: ${props.district_code}.`
        });
    }

    showIndustrialIntelligencePanel(props) {
        const panel = document.getElementById('atlas-selected-panel');
        const levelBadge = document.getElementById('atlas-panel-level-badge');
        const verifyBadge = document.getElementById('atlas-verification-badge');
        const titleEl = document.getElementById('atlas-selected-title');
        const codeEl = document.getElementById('atlas-panel-code');
        const parentEl = document.getElementById('atlas-panel-parent');
        const descEl = document.getElementById('atlas-selected-desc');
        const specsWrap = document.getElementById('atlas-zone-specs-container');
        const areaEl = document.getElementById('atlas-spec-area');
        const statusEl = document.getElementById('atlas-spec-status');
        const mgmtEl = document.getElementById('atlas-spec-management');
        const tenantsEl = document.getElementById('atlas-spec-tenants');
        const provenanceEl = document.getElementById('atlas-provenance-text');

        if (!panel) return;

        // Update breadcrumb agar konsisten dengan lokasi fasilitas industri yang diklik!
        this.selectedProvince = props.province_name || null;
        this.selectedRegency = props.region_name || null;
        this.selectedDistrict = null;
        this.updateHierarchyBreadcrumb();

        const tenants = typeof props.anchor_tenants === 'string' ? JSON.parse(props.anchor_tenants) : (props.anchor_tenants || []);
        const indProv = typeof props.industry_provenance === 'string' ? JSON.parse(props.industry_provenance) : (props.industry_provenance || {});
        const geomProv = typeof props.geometry_provenance === 'string' ? JSON.parse(props.geometry_provenance) : (props.geometry_provenance || {});

        const typeLabels = {
            'kawasan_industri': 'Kawasan Industri (KI)',
            'kawasan_ekonomi_khusus': 'Kawasan Ekonomi Khusus (KEK)',
            'tech_park': 'Technology & Science Park',
            'maritime_hub': 'Sentra Dok & Pelabuhan Maritim'
        };

        const validLat = props.latitude ?? '-';
        const validLng = props.longitude ?? '-';
        const coordString = (validLat !== '-' && validLng !== '-') ? `[${Number(validLat).toFixed(4)}, ${Number(validLng).toFixed(4)}]` : 'Koordinat Wilayah';

        panel.classList.remove('hidden');
        if (levelBadge) levelBadge.textContent = typeLabels[props.zone_type] || 'Sentra Industri';
        
        if (verifyBadge) {
            const isOfficial = indProv.verification_status === 'official_kemenperin' || indProv.verification_status === 'verified_estate';
            verifyBadge.textContent = isOfficial ? '✓ Terverifikasi Resmi' : '⚠ Data Indikatif';
            verifyBadge.className = isOfficial ? 
                'text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200' :
                'text-[10px] font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200';
        }

        if (titleEl) titleEl.textContent = props.name;
        if (codeEl) codeEl.textContent = `Wilayah: ${props.region_name || 'Indonesia'}`;
        if (parentEl) parentEl.textContent = `Kategori: ${props.category.replace('_', ' ').toUpperCase()}`;

        if (descEl) {
            descEl.innerHTML = `
                <div class="space-y-1">
                    <div>Fasilitas industri dengan koordinat spasial: <strong class="font-mono text-slate-800 dark:text-slate-100">${coordString}</strong>.</div>
                    <div class="text-[11px] text-slate-400">Geometri: ${geomProv.geometry_type || 'Point WGS84'} • Akurasi: ${geomProv.positioning_accuracy || 'Verified'}</div>
                </div>
            `;
        }

        if (specsWrap) specsWrap.classList.remove('hidden');
        if (areaEl) areaEl.textContent = props.area_hectares ? `${props.area_hectares} Hektar` : 'Belum tersedia';
        if (statusEl) {
            statusEl.textContent = props.operational_status === 'operating' ? 'Beroperasi Penuh' : 'Tahap Pembangunan';
            statusEl.className = props.operational_status === 'operating' ? 'font-extrabold text-emerald-600' : 'font-extrabold text-amber-600';
        }
        if (mgmtEl) mgmtEl.textContent = props.management_company || 'Pemerintah Daerah & Pengelola Terkait';
        if (tenantsEl) {
            tenantsEl.innerHTML = tenants.length > 0 ?
                tenants.slice(0, 3).map(t => `<div class="truncate text-slate-700 dark:text-slate-300">• ${t}</div>`).join('') :
                '<div class="text-slate-400 italic">Tenant dalam proses verifikasi</div>';
        }
        if (provenanceEl) {
            provenanceEl.textContent = `Fakta: ${indProv.source_agency || 'Kemenperin SIINas'} (${indProv.dataset_edition || '2024'})`;
        }
    }

    showRegionPanelInfo(data) {
        const panel = document.getElementById('atlas-selected-panel');
        const levelBadge = document.getElementById('atlas-panel-level-badge');
        const verifyBadge = document.getElementById('atlas-verification-badge');
        const titleEl = document.getElementById('atlas-selected-title');
        const codeEl = document.getElementById('atlas-panel-code');
        const parentEl = document.getElementById('atlas-panel-parent');
        const descEl = document.getElementById('atlas-selected-desc');
        const specsWrap = document.getElementById('atlas-zone-specs-container');

        if (!panel) return;
        panel.classList.remove('hidden');
        if (specsWrap) specsWrap.classList.add('hidden');
        if (verifyBadge) {
            verifyBadge.textContent = '✓ Batas Geodesi BIG';
            verifyBadge.className = 'text-[10px] font-bold text-sky-700 bg-sky-50 px-2 py-0.5 rounded border border-sky-200';
        }
        if (levelBadge) levelBadge.textContent = data.level;
        if (titleEl) titleEl.textContent = data.name;
        if (codeEl) codeEl.textContent = `Kode: ${data.code}`;
        if (parentEl) parentEl.textContent = `Induk: ${data.parent}`;
        if (descEl) descEl.textContent = data.note;
    }

    updateHierarchyBreadcrumb() {
        const bc = document.getElementById('atlas-hierarchy-breadcrumb');
        if (!bc) return;

        let html = '<span class="text-slate-400">Indonesia</span>';
        if (this.selectedProvince) {
            html += ` <span class="text-slate-400">›</span> <span class="text-sky-300 font-bold">${this.selectedProvince}</span>`;
        }
        if (this.selectedRegency) {
            html += ` <span class="text-slate-400">›</span> <span class="text-amber-300 font-bold">${this.selectedRegency}</span>`;
        }
        if (this.selectedDistrict) {
            html += ` <span class="text-slate-400">›</span> <span class="text-orange-400 font-bold">Kec. ${this.selectedDistrict}</span>`;
        }
        bc.innerHTML = html;
    }

    resetView() {
        this.currentLevel = 0;
        this.selectedProvince = null;
        this.selectedRegency = null;
        this.selectedDistrict = null;
        this.updateHierarchyBreadcrumb();

        const panel = document.getElementById('atlas-selected-panel');
        if (panel) panel.classList.add('hidden');

        if (this.map && this.map.getSource('indonesia-districts')) {
            this.map.getSource('indonesia-districts').setData({ type: 'FeatureCollection', features: [] });
        }

        if (this.map) {
            this.map.flyTo({
                center: [118.0, -2.2],
                zoom: 4.35,
                speed: 1.2,
                essential: true
            });
        }
    }

    bindUIControls() {
        const resetBtn = document.getElementById('atlas-reset-btn');
        if (resetBtn) {
            resetBtn.addEventListener('click', () => this.resetView());
        }

        const filterBtns = document.querySelectorAll('[data-atlas-filter]');
        filterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const category = btn.getAttribute('data-atlas-filter');
                filterBtns.forEach(b => b.classList.remove('bg-[#C8460B]', 'text-white'));
                btn.classList.add('bg-[#C8460B]', 'text-white');

                // Filter marker HTML di peta
                this.markersOnMap.forEach(m => {
                    const el = m.getElement();
                    const markerCat = el.getAttribute('data-category');
                    if (category === 'all' || markerCat === category) {
                        el.style.display = 'block';
                    } else {
                        el.style.display = 'none';
                    }
                });
            });
        });
    }
}
