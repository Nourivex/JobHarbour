/**
 * Admin Portal: Pengaturan platform. Hanya pengaturan yang memang ada di backend
 * (sembunyikan data demo), ditambah ringkasan pengumuman dan sumber lowongan yang dikelola di halamannya sendiri.
 */
import { admin } from '../../api.js';
import { bootAdmin } from '../../admin/shell.js';
import { mountDemoSetting } from '../../admin/ui.js';
import { plural } from '../../recruiter/ui.js';

const $ = (id) => document.getElementById(id);

document.addEventListener('DOMContentLoaded', async () => {
    const ctx = await bootAdmin({ page: 'admin-platform' });
    if (!ctx) return;
    mountDemoSetting($('pf-demo'));
    // Ringkasan status; bila gagal, teks bawaan tetap tampil
    admin.announcements().then((res) => {
        const live = (res.data || []).filter((a) => a.state === 'live').length;
        $('pf-ann').textContent = live ? `${plural(live, 'pengumuman')} sedang tampil di situs.` : 'Tidak ada pengumuman yang sedang tampil.';
    }).catch(() => {});
    admin.sources().then((res) => {
        const list = res.data?.sources || [];
        const bad = list.filter((s) => s.is_enabled && s.health_status === 'degraded').length;
        $('pf-src').textContent = list.length
            ? `${list.filter((s) => s.is_enabled).length} dari ${list.length} sumber aktif${bad ? `, ${bad} bermasalah` : ''}.`
            : 'Belum ada sumber lowongan otomatis.';
    }).catch(() => {});
});
