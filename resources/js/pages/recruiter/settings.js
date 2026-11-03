import { profile, session, toast } from '../../api.js';
import { bootShell } from '../../recruiter/shell.js';
import { bootAdmin } from '../../admin/shell.js';
import { data } from '../../recruiter/data.js';
import { initMenus, setBusy } from '../../recruiter/components.js';
import { initials, formatDate, showFieldErrors, clearFieldErrors, errorMessage } from '../../recruiter/ui.js';

const $ = (id) => document.getElementById(id);
const TABS = ['account', 'security', 'appearance'];

function switchTab(name) {
    TABS.forEach((t) => {
        const on = t === name;
        $(`st-tab-${t}`).setAttribute('aria-selected', String(on));
        $(`st-tab-${t}`).tabIndex = on ? 0 : -1;
        $(`st-${t}`).classList.toggle('hidden', !on);
        if (on) {
            $(`st-${t}`).classList.remove('ws-panel-enter');
            void $(`st-${t}`).offsetWidth;
            $(`st-${t}`).classList.add('ws-panel-enter');
        }
    });
    history.replaceState(null, '', `#${name}`);
}

function strength(pw) {
    let s = 0;
    if (pw.length >= 8) s += 1;
    if (pw.length >= 12) s += 1;
    if (/[A-Z]/.test(pw) && /[a-z]/.test(pw)) s += 1;
    if (/\d/.test(pw) && /[^A-Za-z0-9]/.test(pw)) s += 1;
    return Math.min(s, 4);
}

function fillAccount(user) {
    const f = $('st-account-form');
    f.elements.name.value = user.name || '';
    f.elements.phone.value = user.phone || '';
    f.elements.location.value = user.location || '';
    f.elements.bio.value = user.bio || '';
    $('st-email').value = user.email || '';
    $('st-avatar').textContent = initials(user.name);
    $('st-display-name').textContent = user.name || '-';
    $('st-meta').textContent = `${user.role === 'admin' ? 'Administrator' : 'Recruiter'}${user.created_at ? ` · bergabung ${formatDate(user.created_at)}` : ''}`;
}

document.addEventListener('DOMContentLoaded', async () => {
    // Halaman yang sama dipakai di Admin Portal (layout admin) dan Portal Recruiter
    const ctx = document.body.classList.contains('ws-admin')
        ? await bootAdmin({ page: 'admin-settings' })
        : await bootShell({ page: 'settings' });
    if (!ctx) return;
    initMenus();
    fillAccount(ctx.user);

    TABS.forEach((t) => $(`st-tab-${t}`).addEventListener('click', () => switchTab(t)));
    $('st-tabs').addEventListener('keydown', (e) => {
        if (!['ArrowRight', 'ArrowLeft'].includes(e.key)) return;
        const i = TABS.findIndex((t) => $(`st-tab-${t}`).getAttribute('aria-selected') === 'true');
        const n = TABS[(i + (e.key === 'ArrowRight' ? 1 : -1) + TABS.length) % TABS.length];
        switchTab(n);
        $(`st-tab-${n}`).focus();
    });
    const hash = location.hash.slice(1);
    if (TABS.includes(hash)) switchTab(hash);

    // Akun
    const accountForm = $('st-account-form');
    accountForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearFieldErrors(accountForm);
        const payload = {
            name: accountForm.elements.name.value.trim(),
            phone: accountForm.elements.phone.value.trim() || null,
            location: accountForm.elements.location.value.trim() || null,
            bio: accountForm.elements.bio.value.trim() || null,
        };
        if (!payload.name) {
            showFieldErrors(accountForm, { errors: { name: 'Nama wajib diisi.' } });
            accountForm.elements.name.focus();
            return;
        }
        const btn = e.submitter;
        setBusy(btn, true, 'Menyimpan...');
        try {
            await profile.update(payload);
            data.invalidate('me');
            const user = await data.me();
            session.setUser({ ...session.user(), ...user });
            fillAccount(user);
            document.getElementById('ws-user-name').textContent = user.name;
            document.getElementById('ws-user-avatar').textContent = initials(user.name);
            toast('Informasi akun disimpan.', 'success');
        } catch (err) {
            if (!showFieldErrors(accountForm, err)) toast(errorMessage(err, 'Informasi akun gagal disimpan.'), 'error');
        }
        setBusy(btn, false);
    });

    // Keamanan
    const pwForm = $('st-password-form');
    const bars = [...$('st-strength').children];
    pwForm.elements.password.addEventListener('input', (e) => {
        const s = e.target.value ? strength(e.target.value) : 0;
        const colors = ['#B42318', '#B45309', '#1B65A6', '#15803D'];
        bars.forEach((b, i) => { b.style.background = i < s ? colors[s - 1] : ''; });
        $('st-strength-text').textContent = e.target.value ? ['Sangat lemah', 'Lemah', 'Cukup', 'Kuat', 'Sangat kuat'][s] : '';
    });
    $('st-show').addEventListener('change', (e) => {
        ['current_password', 'password', 'password_confirmation'].forEach((n) => { pwForm.elements[n].type = e.target.checked ? 'text' : 'password'; });
    });
    pwForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearFieldErrors(pwForm);
        const v = (n) => pwForm.elements[n].value;
        const problems = {};
        if (!v('current_password')) problems.current_password = 'Isi kata sandi saat ini.';
        if (v('password').length < 8) problems.password = 'Kata sandi baru minimal 8 karakter.';
        else if (v('password') === v('current_password')) problems.password = 'Kata sandi baru harus berbeda.';
        if (v('password') !== v('password_confirmation')) problems.password_confirmation = 'Konfirmasi tidak sama dengan kata sandi baru.';
        if (Object.keys(problems).length) {
            showFieldErrors(pwForm, { errors: problems });
            pwForm.querySelector('.is-invalid')?.focus();
            return;
        }
        const btn = e.submitter;
        setBusy(btn, true, 'Menyimpan...');
        try {
            await profile.changePassword({ current_password: v('current_password'), password: v('password'), password_confirmation: v('password_confirmation') });
            pwForm.reset();
            bars.forEach((b) => { b.style.background = ''; });
            $('st-strength-text').textContent = '';
            toast('Kata sandi berhasil diganti.', 'success');
        } catch (err) {
            if (!showFieldErrors(pwForm, err)) toast(errorMessage(err, 'Kata sandi gagal diganti.'), 'error');
            pwForm.querySelector('.is-invalid')?.focus();
        }
        setBusy(btn, false);
    });

    // Tampilan
    const theme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
    document.querySelector(`input[name="theme"][value="${theme}"]`).checked = true;
    document.querySelectorAll('input[name="theme"]').forEach((r) => r.addEventListener('change', () => {
        document.documentElement.classList.toggle('dark', r.value === 'dark');
        try { localStorage.setItem('jh_theme', r.value); } catch (err) { /* abaikan */ }
    }));
});
