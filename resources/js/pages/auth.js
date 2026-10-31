import JH, { auth, jobs, util, toast } from '../api.js';

const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const params = new URLSearchParams(window.location.search);
const nextUrl = params.get('next');

const shell = document.getElementById('auth-shell');
const panelsWrap = document.getElementById('auth-panels');
const panels = {
    login: document.getElementById('panel-login'),
    register: document.getElementById('panel-register'),
};
const tabs = {
    login: document.getElementById('tab-login-btn'),
    register: document.getElementById('tab-register-btn'),
};
let mode = 'login';
let switching = false;

// ------------------------------------------------------------------ perpindahan Masuk / Daftar
function setModeAttributes(next) {
    shell.dataset.mode = next;
    Object.entries(tabs).forEach(([key, tab]) => {
        if (!tab) return;
        const on = key === next;
        tab.setAttribute('aria-selected', String(on));
        tab.tabIndex = on ? 0 : -1;
    });
    document.querySelectorAll('[data-visual]').forEach((el) => {
        el.setAttribute('aria-hidden', String(el.dataset.visual !== next));
    });
    const url = new URL(window.location.href);
    if (next === 'register') url.searchParams.set('tab', 'register');
    else url.searchParams.delete('tab');
    history.replaceState(null, '', url);
}

function switchMode(next, animate = true) {
    if (next === mode || switching) return;
    const from = panels[mode];
    const to = panels[next];
    const dir = next === 'register' ? 1 : -1;
    mode = next;
    setModeAttributes(next);
    clearAllErrors();

    if (!animate || reduceMotion) {
        from.hidden = true;
        to.hidden = false;
        return;
    }

    // Kunci tinggi area form, lalu animasikan ke tinggi panel baru supaya kartu tidak melompat
    switching = true;
    panelsWrap.style.height = `${panelsWrap.offsetHeight}px`;
    panelsWrap.classList.add('is-animating');
    from.style.setProperty('--dir', dir);
    from.classList.add('is-leaving');

    setTimeout(() => {
        from.hidden = true;
        from.classList.remove('is-leaving');
        to.hidden = false;
        to.style.setProperty('--dir', dir);
        to.classList.add('is-entering');
        panelsWrap.style.height = `${to.offsetHeight}px`;
        setTimeout(() => {
            panelsWrap.style.height = '';
            panelsWrap.classList.remove('is-animating');
            positionBeacon();
            to.classList.remove('is-entering');
            switching = false;
        }, 340);
    }, 150);
}

function initModeSwitch() {
    Object.entries(tabs).forEach(([key, tab]) => {
        tab?.addEventListener('click', () => switchMode(key));
        tab?.addEventListener('keydown', (e) => {
            if (e.key !== 'ArrowRight' && e.key !== 'ArrowLeft') return;
            const other = key === 'login' ? 'register' : 'login';
            switchMode(other);
            tabs[other].focus();
        });
    });
    document.querySelectorAll('[data-switch]').forEach((btn) => {
        btn.addEventListener('click', () => {
            switchMode(btn.dataset.switch);
            window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' });
        });
    });
    // Tombol kembali di ponsel: dari Daftar kembali ke Masuk, dari Masuk ke beranda
    document.getElementById('mobile-back')?.addEventListener('click', (e) => {
        if (mode === 'register') {
            e.preventDefault();
            switchMode('login');
        }
    });

    if (params.get('tab') === 'register') {
        panels.login.hidden = true;
        panels.register.hidden = false;
        mode = 'register';
        setModeAttributes('register');
    }
}

// ------------------------------------------------------------------ validasi per kolom
const VALIDATORS = {
    email: (v) => (!v ? 'Alamat email wajib diisi.' : /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v) ? '' : 'Format email belum benar, contoh: nama@domain.com.'),
    password: (v) => (!v ? 'Kata sandi wajib diisi.' : v.length < 6 ? 'Kata sandi minimal 6 karakter.' : ''),
    required: (v) => (v ? '' : 'Kolom ini wajib diisi.'),
    phone: (v) => (!v || /^[0-9+\-\s]{8,20}$/.test(v) ? '' : 'Nomor HP hanya berisi angka, 8 sampai 20 digit.'),
};

function setFieldState(field, message, showValid = true) {
    const error = field.querySelector('[data-field-error]');
    field.classList.toggle('is-invalid', Boolean(message));
    field.classList.toggle('is-valid', !message && showValid);
    const input = field.querySelector('input');
    input?.setAttribute('aria-invalid', String(Boolean(message)));
    if (error) {
        error.textContent = message || '';
        error.classList.toggle('is-shown', Boolean(message));
    }
}

function validateField(field) {
    const rule = field.dataset.validate;
    const input = field.querySelector('input');
    if (!rule || !input) return true;
    const value = input.value.trim();
    const message = VALIDATORS[rule](rule === 'password' ? input.value : value);
    // Kolom opsional yang kosong tidak diberi tanda centang
    setFieldState(field, message, Boolean(value));
    return !message;
}

function validateForm(form) {
    let firstInvalid = null;
    form.querySelectorAll('.auth-field[data-validate]').forEach((field) => {
        if (!validateField(field) && !firstInvalid) firstInvalid = field.querySelector('input');
    });
    return firstInvalid;
}

function initLiveValidation() {
    document.querySelectorAll('.auth-field[data-validate]').forEach((field) => {
        const input = field.querySelector('input');
        input.addEventListener('blur', () => {
            if (input.value !== '' || field.classList.contains('is-invalid')) validateField(field);
        });
        input.addEventListener('input', () => {
            // Setelah sempat salah, kolom dicek ulang sambil mengetik supaya pesan hilang begitu benar
            if (field.classList.contains('is-invalid') || field.classList.contains('is-valid')) validateField(field);
        });
    });
}

function clearAllErrors() {
    document.querySelectorAll('.auth-field').forEach((f) => f.classList.remove('is-invalid'));
    document.querySelectorAll('[data-field-error]').forEach((el) => {
        el.textContent = '';
        el.classList.remove('is-shown');
    });
}

function showServerErrors(form, errors) {
    let first = null;
    Object.entries(errors || {}).forEach(([name, messages]) => {
        const error = form.querySelector(`[data-field-error="${name}"]`);
        const field = error?.closest('.auth-field');
        const text = Array.isArray(messages) ? messages[0] : String(messages);
        if (field) {
            setFieldState(field, text, false);
            if (!first) first = field.querySelector('input');
        } else if (error) {
            error.textContent = text;
            error.classList.add('is-shown');
        }
    });
    first?.focus();
}

// ------------------------------------------------------------------ tombol kirim
function setSubmitState(btn, state, text) {
    const label = btn.querySelector('.auth-submit-label');
    if (!btn.dataset.idle) btn.dataset.idle = label.innerHTML;
    btn.classList.remove('is-loading', 'is-success');
    btn.disabled = state === 'loading' || state === 'success';
    btn.setAttribute('aria-busy', String(state === 'loading'));
    if (state === 'loading') {
        btn.classList.add('is-loading');
        label.innerHTML = `<span class="auth-spinner" aria-hidden="true"></span>${text}`;
    } else if (state === 'success') {
        btn.classList.add('is-success');
        label.innerHTML = `<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 5 5 9-10"/></svg>${text}`;
    } else {
        label.innerHTML = btn.dataset.idle;
    }
}

function shake(el) {
    if (reduceMotion) return;
    el.classList.remove('is-shaking');
    void el.offsetWidth; // mulai ulang animasi
    el.classList.add('is-shaking');
    el.addEventListener('animationend', () => el.classList.remove('is-shaking'), { once: true });
}

function redirectAfterAuth(user) {
    const target = nextUrl ? decodeURIComponent(nextUrl) : JH.homeFor(user);
    setTimeout(() => { window.location.href = target; }, reduceMotion ? 200 : 700);
}

// ------------------------------------------------------------------ form masuk
function initLogin() {
    const form = document.getElementById('login-form');
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = form.querySelector('.auth-submit');
        const firstInvalid = validateForm(form);
        if (firstInvalid) {
            shake(btn);
            firstInvalid.focus();
            return;
        }
        setSubmitState(btn, 'loading', 'Memeriksa akun...');
        try {
            const user = await auth.login(document.getElementById('login-email').value.trim(), document.getElementById('login-password').value);
            setSubmitState(btn, 'success', `Halo, ${(user.name || '').split(' ')[0]}! Mengalihkan...`);
            redirectAfterAuth(user);
        } catch (err) {
            setSubmitState(btn, 'idle');
            shake(btn);
            if (err.errors) showServerErrors(form, err.errors);
            else {
                const field = form.querySelector('[data-validate="password"]');
                setFieldState(field, err.message || 'Email atau kata sandi salah.', false);
                form.querySelector('[data-validate="email"]').classList.add('is-invalid');
            }
        }
    });

    // Akun demo: isi kolom, beri kilatan singkat, lalu fokus ke tombol Masuk
    document.querySelectorAll('[data-demo-email]').forEach((btn) => {
        btn.addEventListener('click', () => {
            switchMode('login');
            const email = document.getElementById('login-email');
            const pass = document.getElementById('login-password');
            email.value = btn.dataset.demoEmail;
            pass.value = 'password123';
            [email, pass].forEach((input) => {
                const box = input.closest('.auth-input');
                box.classList.remove('is-filled');
                void box.offsetWidth;
                box.classList.add('is-filled');
                validateField(input.closest('.auth-field'));
            });
            document.querySelectorAll('[data-demo-email]').forEach((b) => b.classList.toggle('is-picked', b === btn));
            form.querySelector('.auth-submit').focus({ preventScroll: true });
        });
    });

    // Di ponsel, pilihan akun demo dibuka lewat tombol "Coba Akun Demo"
    const toggle = document.getElementById('demo-toggle');
    const options = document.getElementById('demo-options');
    toggle?.addEventListener('click', () => {
        if (window.matchMedia('(min-width: 768px)').matches) return;
        const open = toggle.getAttribute('aria-expanded') !== 'true';
        toggle.setAttribute('aria-expanded', String(open));
        options.classList.toggle('is-open', open);
    });
}

// ------------------------------------------------------------------ form daftar
function passwordStrength(pw) {
    if (!pw) return { level: 0, label: '-' };
    if (pw.length < 6) return { level: 1, label: 'Lemah' };
    let points = 1;
    if (pw.length >= 10) points += 1;
    if (/[a-z]/.test(pw) && /[A-Z]/.test(pw)) points += 1;
    if (/\d/.test(pw)) points += 1;
    if (/[^A-Za-z0-9]/.test(pw)) points += 1;
    if (points <= 2) return { level: 2, label: 'Sedang' };
    if (points === 3) return { level: 3, label: 'Kuat' };
    return { level: 4, label: 'Sangat kuat' };
}

function initRegister() {
    const form = document.getElementById('register-form');
    const skills = [];
    const chips = document.getElementById('reg-skills-chips');
    const skillInput = document.getElementById('reg-skill-input');

    // Indikator kekuatan sandi
    const meter = document.getElementById('pw-strength');
    const meterLabel = document.getElementById('pw-strength-label');
    document.getElementById('reg-password').addEventListener('input', (e) => {
        const s = passwordStrength(e.target.value);
        meter.dataset.level = String(s.level);
        meterLabel.textContent = s.level ? s.label : 'Belum diisi';
        meterLabel.dataset.level = String(s.level);
    });

    // Chip skill
    const renderChips = (popIndex = -1) => {
        chips.innerHTML = skills.map((s, i) => `
            <span class="skill-chip ${i === popIndex ? 'is-new' : ''}">${util.escapeHtml(s)}
                <button type="button" data-remove-skill="${i}" aria-label="Hapus ${util.escapeHtml(s)}">&times;</button>
            </span>`).join('');
    };
    const addSkill = () => {
        const value = skillInput.value.trim().replace(/,$/, '');
        if (!value) return;
        if (!skills.some((s) => s.toLowerCase() === value.toLowerCase())) {
            skills.push(value);
            renderChips(skills.length - 1);
        }
        skillInput.value = '';
    };
    skillInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            addSkill();
        } else if (e.key === 'Backspace' && !skillInput.value && skills.length) {
            skills.pop();
            renderChips();
        }
    });
    // Memilih dari daftar saran langsung menambahkan chip
    skillInput.addEventListener('change', addSkill);
    chips.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-remove-skill]');
        if (!btn) return;
        const chip = btn.parentElement;
        const remove = () => {
            skills.splice(Number(btn.dataset.removeSkill), 1);
            renderChips();
            skillInput.focus();
        };
        if (reduceMotion) return remove();
        chip.classList.add('is-removing');
        setTimeout(remove, 140);
    });
    document.getElementById('reg-skills-box').addEventListener('click', (e) => {
        if (e.target.id === 'reg-skills-box') skillInput.focus();
    });

    // Saran kota dan skill diambil dari lowongan yang sedang dibuka
    jobs.list({ per_page: 50, status: 'open' }).then((res) => {
        const list = Array.isArray(res.data) ? res.data : [];
        const cities = [...new Set(list.map((j) => j.location).filter(Boolean))];
        const skillNames = [...new Set(list.flatMap((j) => (j.requirements || []).map((r) => util.skillName(r))).filter(Boolean))].sort();
        document.getElementById('city-suggestions').innerHTML = cities.map((c) => `<option value="${util.escapeHtml(c)}"></option>`).join('');
        document.getElementById('skill-suggestions').innerHTML = skillNames.map((s) => `<option value="${util.escapeHtml(s)}"></option>`).join('');
    }).catch(() => { /* saran opsional, abaikan jika gagal */ });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = form.querySelector('.auth-submit');
        addSkill();
        let firstInvalid = validateForm(form);
        const terms = document.getElementById('reg-terms');
        const termsError = form.querySelector('[data-field-error="terms"]');
        termsError.textContent = terms.checked ? '' : 'Centang persetujuan untuk melanjutkan.';
        termsError.classList.toggle('is-shown', !terms.checked);
        if (!terms.checked && !firstInvalid) firstInvalid = terms;
        if (firstInvalid) {
            shake(btn);
            firstInvalid.focus();
            return;
        }

        const payload = {
            name: document.getElementById('reg-name').value.trim(),
            email: document.getElementById('reg-email').value.trim(),
            password: document.getElementById('reg-password').value,
            role: form.querySelector('input[name="role"]:checked')?.value || 'applicant',
            phone: document.getElementById('reg-phone').value.trim() || null,
            location: document.getElementById('reg-location').value.trim() || null,
            skills,
        };

        setSubmitState(btn, 'loading', 'Membuat akun...');
        try {
            const user = await auth.register(payload);
            setSubmitState(btn, 'success', 'Akun dibuat! Mengalihkan...');
            redirectAfterAuth(user);
        } catch (err) {
            setSubmitState(btn, 'idle');
            shake(btn);
            if (err.errors) showServerErrors(form, err.errors);
            else toast(err.message || 'Pendaftaran gagal. Coba lagi.', 'error');
        }
    });
}

// ------------------------------------------------------------------ tampilkan / sembunyikan sandi
function initPasswordToggles() {
    document.querySelectorAll('[data-toggle-password]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const input = document.getElementById(btn.dataset.togglePassword);
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.setAttribute('aria-pressed', String(show));
            btn.setAttribute('aria-label', show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
        });
    });
}

// ------------------------------------------------------------------ cahaya mercusuar di panel gambar
function positionBeacon() {
    const beacon = document.querySelector('.auth-beacon');
    const panel = beacon?.parentElement;
    if (!beacon || !panel || !panel.offsetWidth) return;
    // Gambar selebar panel dan menempel di bawah, jadi posisi lampu dihitung dari tepi bawah
    const w = panel.offsetWidth;
    const imgH = w * (1050 / 960);
    const top = panel.offsetHeight - imgH;
    beacon.style.setProperty('--beacon-x', `${0.744 * w}px`);
    beacon.style.setProperty('--beacon-y', `${top + 0.526 * imgH}px`);
}

document.addEventListener('DOMContentLoaded', () => {
    initModeSwitch();
    initLiveValidation();
    initPasswordToggles();
    initLogin();
    initRegister();
    positionBeacon();
    window.addEventListener('resize', positionBeacon, { passive: true });
});
