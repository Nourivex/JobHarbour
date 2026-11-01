import JH, { session, auth, util } from './api.js';

/**
 * Inisialisasi status sesi pengguna pada navigasi desktop & ponsel.
 */
function bindSessionUI(root = document) {
    const user = session.user();
    const loggedIn = session.isLoggedIn() && user;

    root.querySelectorAll('[data-jh-user-name]').forEach((el) => {
        el.textContent = loggedIn ? user.name : 'Tamu';
    });

    root.querySelectorAll('[data-jh-user-email]').forEach((el) => {
        el.textContent = loggedIn ? user.email : '';
    });

    root.querySelectorAll('[data-jh-user-role]').forEach((el) => {
        if (!loggedIn) {
            el.textContent = '';
            return;
        }
        const roleNames = {
            applicant: 'Pelamar Kerja',
            recruiter: 'Recruiter',
            admin: 'Administrator',
        };
        el.textContent = roleNames[user.role] || user.role;
    });

    root.querySelectorAll('[data-jh-user-initials]').forEach((el) => {
        el.textContent = loggedIn ? util.initials(user.name) : '?';
    });

    root.querySelectorAll('[data-jh-guest-only]').forEach((el) => {
        el.style.display = loggedIn ? 'none' : '';
    });

    root.querySelectorAll('[data-jh-auth-only]').forEach((el) => {
        el.style.display = loggedIn ? '' : 'none';
    });

    root.querySelectorAll('[data-jh-role-applicant]').forEach((el) => {
        el.style.display = loggedIn && user.role === 'applicant' ? '' : 'none';
    });

    root.querySelectorAll('[data-jh-role-recruiter]').forEach((el) => {
        el.style.display = loggedIn && (user.role === 'recruiter' || user.role === 'admin') ? '' : 'none';
    });

    root.querySelectorAll('[data-jh-logout]').forEach((el) => {
        el.addEventListener('click', async (e) => {
            e.preventDefault();
            const confirmed = await JH.confirm({
                title: 'Konfirmasi Keluar',
                message: 'Apakah Anda yakin ingin keluar dari akun JobHarbour?',
                confirmText: 'Keluar',
                cancelText: 'Batal',
                danger: true,
            });
            if (confirmed) {
                auth.logout('/login');
            }
        });
    });
}

/**
 * Inisialisasi dropdown profil pengguna di navbar atas.
 */
function initUserDropdown() {
    const trigger = document.getElementById('user-menu-button');
    const menu = document.getElementById('user-menu-dropdown');
    if (!trigger || !menu) return;

    const toggle = (force) => {
        const isOpen = force !== undefined ? force : menu.classList.contains('hidden');
        if (isOpen) {
            menu.classList.remove('hidden');
            trigger.setAttribute('aria-expanded', 'true');
        } else {
            menu.classList.add('hidden');
            trigger.setAttribute('aria-expanded', 'false');
        }
    };

    trigger.addEventListener('click', (e) => {
        e.stopPropagation();
        toggle();
    });

    document.addEventListener('click', (e) => {
        if (!menu.contains(e.target) && !trigger.contains(e.target)) {
            toggle(false);
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !menu.classList.contains('hidden')) {
            toggle(false);
            trigger.focus();
        }
    });
}

/**
 * Scroll reveal ringan menggunakan IntersectionObserver.
 */
function initScrollReveal(root = document) {
    const revealElements = root.querySelectorAll('[data-reveal]:not(.is-visible)');
    if (!revealElements.length || !('IntersectionObserver' in window)) return;

    document.documentElement.classList.add('reveal-ready');

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.12, rootMargin: '0px 0px -40px 0px' }
    );

    revealElements.forEach((el) => {
        // Jeda bertahap hanya antar saudara dalam satu grup, maksimal 240ms
        const siblings = [...el.parentElement.children].filter((c) => c.hasAttribute('data-reveal'));
        const order = siblings.indexOf(el);
        el.style.setProperty('--reveal-delay', `${Math.min(order, 4) * 60}ms`);
        observer.observe(el);
    });
}

/**
 * Animasi angka naik dari 0 ke nilai akhir (dipakai untuk statistik).
 */
function countUp(el, target, duration = 900) {
    if (!el) return;
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduce || target <= 0) {
        el.textContent = target.toLocaleString('id-ID');
        return;
    }
    const start = performance.now();
    const tick = (now) => {
        const t = Math.min((now - start) / duration, 1);
        const eased = 1 - Math.pow(1 - t, 3);
        el.textContent = Math.round(target * eased).toLocaleString('id-ID');
        if (t < 1) requestAnimationFrame(tick);
    };
    requestAnimationFrame(tick);
}

/**
 * Tombol mode malam. Pilihan disimpan di localStorage (key jh_theme).
 */
function initThemeToggle() {
    const root = document.documentElement;
    const sync = () => {
        const dark = root.classList.contains('dark');
        document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
            btn.setAttribute('aria-pressed', String(dark));
            btn.setAttribute('aria-label', dark ? 'Aktifkan mode terang' : 'Aktifkan mode malam');
        });
    };
    document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const dark = root.classList.toggle('dark');
            try {
                localStorage.setItem('jh_theme', dark ? 'dark' : 'light');
            } catch (e) {
                /* penyimpanan tidak tersedia, mode tetap berlaku sampai halaman ditutup */
            }
            sync();
        });
    });
    sync();
}

/**
 * Navbar beranda: transparan di atas hero, berubah navy setelah halaman di-scroll.
 */
function initHeroNav() {
    const nav = document.getElementById('site-nav');
    if (!nav || nav.dataset.heroNav !== '1') return;
    let solid = null;
    const update = () => {
        const next = window.scrollY > 24;
        if (next === solid) return; // hanya sentuh DOM saat status berubah
        solid = next;
        nav.classList.toggle('is-solid', next);
        nav.classList.toggle('is-hero', !next);
    };
    window.addEventListener('scroll', update, { passive: true });
    update();
}

document.addEventListener('DOMContentLoaded', () => {
    bindSessionUI();
    initUserDropdown();
    initThemeToggle();
    initHeroNav();
    initScrollReveal();
});

export { bindSessionUI, initUserDropdown, initScrollReveal, countUp };
