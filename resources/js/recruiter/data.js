/**
 * Data bersama Portal Recruiter. Setiap halaman memanggil fungsi yang dibutuhkan saja;
 * hasilnya disimpan sementara selama halaman terbuka supaya tidak memanggil API dua kali.
 */
import { auth, jobs, applications, pipelines, company as companyApi } from '../api.js';
import { stageOf, nextStageOf } from './ui.js';

const cache = {};

function once(key, loader) {
    if (!cache[key]) cache[key] = loader().catch((err) => { delete cache[key]; throw err; });
    return cache[key];
}

export const data = {
    me: () => once('me', () => auth.me()),
    company: () => once('company', async () => (await companyApi.show()).data),
    jobs: () => once('jobs', async () => {
        const res = await jobs.mine();
        return Array.isArray(res.data) ? res.data : [];
    }),
    applications: () => once('apps', async () => {
        const res = await applications.list();
        return (Array.isArray(res.data) ? res.data : []).filter((a) => a.status !== 'canceled');
    }),
    async application(id) {
        const res = await applications.show(id);
        return res.data;
    },
    invalidate(...keys) {
        (keys.length ? keys : Object.keys(cache)).forEach((k) => delete cache[k]);
    },
};

export function jobById(list, id) {
    return list.find((j) => Number(j.id) === Number(id)) || null;
}

export function appsOfJob(apps, jobId) {
    return apps.filter((a) => Number(a.job_vacancy_id) === Number(jobId));
}

/** Sebaran kandidat per tahap untuk satu kumpulan lamaran. */
export function stageCounts(apps) {
    const counts = { screening: 0, assessment: 0, interview: 0, offering: 0, hired: 0, rejected: 0 };
    apps.forEach((a) => { counts[stageOf(a)] += 1; });
    return counts;
}

export function averageScore(apps) {
    if (!apps.length) return null;
    return Math.round(apps.reduce((s, a) => s + (Number(a.match_score) || 0), 0) / apps.length);
}

/**
 * Pindahkan kandidat. Hanya maju satu tahap atau ditolak, sesuai aturan pipeline.
 * Hired dikirim sebagai tahap offering dengan status passed.
 */
export async function moveCandidate(app, target, extra = {}) {
    const from = stageOf(app);
    if (from === 'hired' || from === 'rejected') throw new Error('Kandidat sudah berada di tahap akhir.');
    if (target !== 'rejected' && nextStageOf(from) !== target) {
        throw new Error('Tahap hanya bisa maju satu langkah atau ditolak.');
    }
    const payload = target === 'hired'
        ? { stage: 'offering', status: 'passed', ...extra }
        : { stage: target, status: target === 'rejected' ? 'rejected' : 'in_progress', ...extra };
    await pipelines.updateStatus(app.id, payload);
    const fresh = await applications.show(app.id);
    Object.assign(app, fresh.data);
    return app;
}
