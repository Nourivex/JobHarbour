import { pipelines, applications, toast } from '../../api.js';
import { bootShell, setNavCounts } from '../../recruiter/shell.js';
import { data } from '../../recruiter/data.js';
import { initMenus, openDialog, setBusy } from '../../recruiter/components.js';
import { esc, icon, hydrateIcons, stageOf, stageBadge, scoreRing, emptyState, errorMessage } from '../../recruiter/ui.js';

const $ = (id) => document.getElementById(id);
const HOUR = 3600000;
const DAY = 86400000;
const state = { apps: [], filter: 'upcoming', editing: null };

/** Ambil jadwal wawancara terakhir dari linimasa lamaran beserta keadaannya sekarang. */
function interviewsOf(apps) {
    const now = Date.now();
    const out = [];
    apps.forEach((app) => {
        const stages = app.pipeline_timeline?.stages || [];
        const entry = [...stages].reverse().find((s) => s.stage === 'interview' && s.interview_schedule?.datetime);
        if (!entry) return;
        const at = new Date(entry.interview_schedule.datetime).getTime();
        if (Number.isNaN(at)) return;
        const current = stageOf(app) === 'interview';
        let kind;
        if (!current) kind = 'history';
        else if (at < now - HOUR) kind = 'attention';
        else kind = 'upcoming';
        out.push({ app, schedule: entry.interview_schedule, at, kind, notes: entry.notes });
    });
    return out;
}

function groupUpcoming(list) {
    const start = new Date();
    start.setHours(0, 0, 0, 0);
    const today = start.getTime();
    const groups = [['Hari ini', []], ['Besok', []], ['7 hari ke depan', []], ['Setelahnya', []]];
    list.forEach((it) => {
        const d = Math.floor((it.at - today) / DAY);
        groups[d <= 0 ? 0 : d === 1 ? 1 : d <= 7 ? 2 : 3][1].push(it);
    });
    return groups.filter(([, items]) => items.length);
}

function row(it) {
    const { app, schedule, at, kind } = it;
    const d = new Date(at);
    const time = d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    const day = d.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long' });
    const url = schedule.meeting_url || '';
    const isLink = /^https?:\/\//i.test(url);
    const next = stageOf(app);
    return `
        <li class="ws-iv ${kind === 'history' ? 'is-past' : ''}">
            <span class="ws-day" aria-hidden="true"><span class="ws-day-month">${d.toLocaleDateString('id-ID', { month: 'short' })}</span><span class="ws-day-num">${d.getDate()}</span></span>
            <div class="min-w-0 flex-1">
                <p class="font-semibold truncate"><a href="/recruiter/candidates/${app.id}" class="ws-row-link">${esc(app.applicant?.name || 'Kandidat')}</a></p>
                <p class="ws-meta truncate">${esc(app.job_vacancy?.title || '-')}</p>
                <p class="mt-1 text-sm text-[var(--ws-text-2)] flex flex-wrap items-center gap-x-3 gap-y-1">
                    <span class="inline-flex items-center gap-1.5">${icon('clock', 'w-3.5 h-3.5')}${day}, ${time}</span>
                    <span class="inline-flex items-center gap-1.5">${icon(schedule.type === 'offline' ? 'map-pin' : 'video', 'w-3.5 h-3.5')}${schedule.type === 'offline' ? 'Tatap muka' : 'Online'}</span>
                    ${schedule.interviewer ? `<span class="inline-flex items-center gap-1.5">${icon('user-round', 'w-3.5 h-3.5')}${esc(schedule.interviewer)}</span>` : ''}
                </p>
            </div>
            <div class="hidden sm:block">${scoreRing(app.match_score, 'sm')}</div>
            <div class="flex flex-wrap items-center justify-end gap-2 w-full sm:w-auto">
                ${kind === 'history' ? stageBadge(next) : ''}
                ${kind === 'attention' ? '<span class="ws-pill ws-pill-warning">Jadwal sudah lewat</span>' : ''}
                ${kind !== 'history' && isLink ? `<a href="${esc(url)}" target="_blank" rel="noopener" class="ws-btn ws-btn-sm ws-btn-secondary">${icon('external-link')}Buka tautan</a>` : ''}
                ${kind !== 'history' && url && !isLink ? `<span class="ws-meta max-w-[180px] truncate" title="${esc(url)}">${esc(url)}</span>` : ''}
                ${kind !== 'history' ? `<button type="button" class="ws-btn ws-btn-sm ws-btn-ghost" data-reschedule="${app.id}">${icon('calendar-clock')}Jadwal ulang</button>` : ''}
                ${kind === 'attention' ? `<a href="/recruiter/pipeline?job=${app.job_vacancy_id}&candidate=${app.id}" class="ws-btn ws-btn-sm ws-btn-primary">Putuskan hasil</a>` : ''}
            </div>
        </li>`;
}

function render() {
    const all = interviewsOf(state.apps);
    const counts = { upcoming: 0, attention: 0, history: 0 };
    all.forEach((it) => { counts[it.kind] += 1; });
    document.querySelectorAll('#iv-filter [data-filter]').forEach((b) => {
        b.setAttribute('aria-pressed', String(b.dataset.filter === state.filter));
        const n = counts[b.dataset.filter];
        b.innerHTML = `${{ upcoming: 'Akan datang', attention: 'Perlu tindak lanjut', history: 'Riwayat' }[b.dataset.filter]}${n ? ` <span class="ws-count">${n}</span>` : ''}`;
    });
    $('iv-lead').textContent = counts.upcoming
        ? `${counts.upcoming} wawancara terjadwal${counts.attention ? `, ${counts.attention} sudah lewat dan menunggu keputusan` : ''}.`
        : counts.attention ? `${counts.attention} wawancara sudah lewat dan menunggu keputusan.` : 'Jadwal dibuat saat kandidat dimajukan ke tahap Interview.';

    const list = all.filter((it) => it.kind === state.filter)
        .sort((a, b) => (state.filter === 'upcoming' ? a.at - b.at : b.at - a.at));

    if (!list.length) {
        const copy = {
            upcoming: ['Belum ada wawancara terjadwal', 'Majukan kandidat ke tahap Interview dari halaman Kandidat untuk membuat jadwal.', '<a href="/recruiter/pipeline?stage=assessment" class="ws-btn ws-btn-primary">Lihat kandidat di Assessment</a>'],
            attention: ['Tidak ada yang tertunda', 'Semua wawancara yang sudah lewat sudah diputuskan hasilnya.', ''],
            history: ['Belum ada riwayat', 'Wawancara yang sudah diputuskan (maju ke Offering atau ditolak) muncul di sini.', ''],
        }[state.filter];
        $('iv-list').innerHTML = emptyState({ iconName: 'calendar-days', title: copy[0], text: copy[1], action: copy[2] });
    } else if (state.filter === 'upcoming') {
        $('iv-list').innerHTML = groupUpcoming(list).map(([label, items]) => `
            <p class="ws-group-label">${label}<span class="ws-count">${items.length}</span></p>
            <ul>${items.map(row).join('')}</ul>`).join('');
    } else {
        $('iv-list').innerHTML = `<ul>${list.map(row).join('')}</ul>`;
    }
    setNavCounts(state.apps);
    hydrateIcons($('iv-list'));
    hydrateIcons(document.getElementById('iv-filter'));
}

// ------------------------------------------------------------------ jadwal ulang
const dialog = $('iv-dialog');
const form = dialog.querySelector('form');

function toLocalInput(iso) {
    const d = new Date(iso);
    if (Number.isNaN(d.getTime())) return '';
    const pad = (n) => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
}

function openReschedule(app) {
    const it = interviewsOf([app])[0];
    if (!it) return;
    state.editing = app;
    form.reset();
    form.querySelector('[data-error-for="datetime"]').textContent = '';
    $('iv-dialog-sub').textContent = `${app.applicant?.name || 'Kandidat'} · ${app.job_vacancy?.title || ''}`;
    form.datetime.value = toLocalInput(it.schedule.datetime);
    form.type.value = it.schedule.type === 'offline' ? 'offline' : 'online';
    form.interviewer.value = it.schedule.interviewer || '';
    form.meeting_url.value = it.schedule.meeting_url || '';
    openDialog(dialog);
    form.datetime.focus();
}

form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const app = state.editing;
    const err = form.querySelector('[data-error-for="datetime"]');
    err.textContent = '';
    if (!form.datetime.value) {
        err.textContent = 'Pilih tanggal dan jam wawancara.';
        form.datetime.focus();
        return;
    }
    const btn = form.querySelector('[type="submit"]');
    setBusy(btn, true, 'Menyimpan...');
    try {
        // Tahap tetap Interview: backend menggabungkan jadwal baru ke entri tahap yang sama
        await pipelines.updateStatus(app.id, {
            stage: 'interview',
            status: 'in_progress',
            interview_schedule: {
                datetime: form.datetime.value,
                type: form.type.value,
                interviewer: form.interviewer.value.trim() || null,
                meeting_url: form.meeting_url.value.trim() || null,
            },
        });
        Object.assign(app, (await applications.show(app.id)).data);
        dialog.close();
        toast('Jadwal wawancara diperbarui.', 'success');
        render();
    } catch (ex) {
        toast(errorMessage(ex, 'Jadwal gagal disimpan.'), 'error', 5000);
    }
    setBusy(btn, false);
});

document.addEventListener('DOMContentLoaded', async () => {
    const ctx = await bootShell({ page: 'interviews' });
    if (!ctx) return;
    initMenus();

    $('iv-filter').addEventListener('click', (e) => {
        const b = e.target.closest('[data-filter]');
        if (!b) return;
        state.filter = b.dataset.filter;
        render();
    });
    $('iv-list').addEventListener('click', (e) => {
        const b = e.target.closest('[data-reschedule]');
        if (!b) return;
        const app = state.apps.find((a) => String(a.id) === b.dataset.reschedule);
        if (app) openReschedule(app);
    });

    try {
        state.apps = await data.applications();
        if (!interviewsOf(state.apps).some((it) => it.kind === 'upcoming') && interviewsOf(state.apps).some((it) => it.kind === 'attention')) {
            state.filter = 'attention';
        }
        render();
    } catch (err) {
        $('iv-list').innerHTML = emptyState({ iconName: 'circle-alert', title: 'Jadwal gagal dimuat', text: esc(errorMessage(err)), action: '<button type="button" class="ws-btn ws-btn-secondary" onclick="location.reload()">Muat ulang</button>' });
    }
});
