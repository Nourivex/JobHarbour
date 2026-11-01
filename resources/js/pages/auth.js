import JH, { auth, util, toast, showError, setLoading } from '../api.js';

document.addEventListener('DOMContentLoaded', () => {
    // 1. Tangani URL query param: ?tab=register atau ?next=...
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab');
    const nextUrl = urlParams.get('next');

    const tabLoginBtn = document.getElementById('tab-login-btn');
    const tabRegisterBtn = document.getElementById('tab-register-btn');
    const loginFormWrap = document.getElementById('login-form-wrap');
    const registerFormWrap = document.getElementById('register-form-wrap');

    function switchTab(tab) {
        if (tab === 'register') {
            tabRegisterBtn.classList.add('border-[#C8460B]', 'text-[#C8460B]');
            tabRegisterBtn.classList.remove('border-transparent', 'text-[#627080]');
            tabLoginBtn.classList.remove('border-[#C8460B]', 'text-[#C8460B]');
            tabLoginBtn.classList.add('border-transparent', 'text-[#627080]');

            loginFormWrap.classList.add('hidden');
            registerFormWrap.classList.remove('hidden');
        } else {
            tabLoginBtn.classList.add('border-[#C8460B]', 'text-[#C8460B]');
            tabLoginBtn.classList.remove('border-transparent', 'text-[#627080]');
            tabRegisterBtn.classList.remove('border-[#C8460B]', 'text-[#C8460B]');
            tabRegisterBtn.classList.add('border-transparent', 'text-[#627080]');

            loginFormWrap.classList.remove('hidden');
            registerFormWrap.classList.add('hidden');
        }
        clearFieldErrors();
    }

    if (tabParam === 'register') {
        switchTab('register');
    }

    tabLoginBtn?.addEventListener('click', () => switchTab('login'));
    tabRegisterBtn?.addEventListener('click', () => switchTab('register'));

    // 2. Helper bersihkan dan tampilkan error per-field
    function clearFieldErrors() {
        document.querySelectorAll('[data-field-error]').forEach((el) => {
            el.textContent = '';
            el.classList.add('hidden');
        });
        document.querySelectorAll('input').forEach((input) => {
            input.classList.remove('border-[#B42318]');
        });
    }

    function showFieldErrors(errors) {
        clearFieldErrors();
        if (!errors || typeof errors !== 'object') return;
        Object.entries(errors).forEach(([field, messages]) => {
            const errEl = document.querySelector(`[data-field-error="${field}"]`);
            const inputEl = document.querySelector(`[name="${field}"]`);
            if (errEl) {
                const text = Array.isArray(messages) ? messages[0] : String(messages);
                errEl.textContent = text;
                errEl.classList.remove('hidden');
            }
            if (inputEl) {
                inputEl.classList.add('border-[#B42318]');
            }
        });
    }

    // 3. Tampilkan / Sembunyikan Kata Sandi
    document.querySelectorAll('[data-toggle-password]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-toggle-password');
            const input = document.getElementById(targetId);
            if (!input) return;
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            const iconEye = btn.querySelector('[data-icon-eye]');
            const iconEyeOff = btn.querySelector('[data-icon-eye-off]');
            if (iconEye && iconEyeOff) {
                iconEye.classList.toggle('hidden', isPassword);
                iconEyeOff.classList.toggle('hidden', !isPassword);
            }
        });
    });

    // 4. Tombol Demo Account
    document.querySelectorAll('[data-demo-email]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const email = btn.getAttribute('data-demo-email');
            const pass = btn.getAttribute('data-demo-password') || 'password123';
            switchTab('login');
            const emailInput = document.getElementById('login-email');
            const passInput = document.getElementById('login-password');
            if (emailInput) emailInput.value = email;
            if (passInput) passInput.value = pass;
            toast(`Mengisi akun demo ${email}`, 'info', 2000);
        });
    });

    // 5. Submit Form Login
    const loginForm = document.getElementById('login-form');
    loginForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearFieldErrors();

        const btn = loginForm.querySelector('button[type="submit"]');
        const email = document.getElementById('login-email')?.value.trim();
        const password = document.getElementById('login-password')?.value;

        if (!email || !password) {
            toast('Harap isi alamat email dan kata sandi.', 'warning');
            return;
        }

        setLoading(btn, true, 'Sedang Masuk...');
        try {
            const user = await auth.login(email, password);
            toast(`Selamat datang kembali, ${user.name}!`, 'success');

            setTimeout(() => {
                if (nextUrl) {
                    window.location.href = decodeURIComponent(nextUrl);
                } else {
                    window.location.href = JH.homeFor(user);
                }
            }, 600);
        } catch (err) {
            setLoading(btn, false);
            if (err.errors) {
                showFieldErrors(err.errors);
            } else {
                toast(err.message || 'Alamat email atau kata sandi tidak valid.', 'error');
            }
        }
    });

    // 6. Kelola input tag skill pada form registrasi
    const registerSkills = [];
    const skillInput = document.getElementById('reg-skill-input');
    const skillChipsContainer = document.getElementById('reg-skills-chips');
    const btnAddSkill = document.getElementById('reg-btn-add-skill');

    function renderSkillChips() {
        if (!skillChipsContainer) return;
        if (!registerSkills.length) {
            skillChipsContainer.innerHTML = '<span class="text-xs text-[#627080]">Belum ada keahlian ditambahkan (opsional).</span>';
            return;
        }
        skillChipsContainer.innerHTML = registerSkills.map((s, idx) => `
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-[#FFF3EA] text-[#8A2E05] border border-[#C8460B]/20">
                <span>${util.escapeHtml(s)}</span>
                <button type="button" data-remove-skill="${idx}" class="hover:text-[#B42318] cursor-pointer" aria-label="Hapus skill ${util.escapeHtml(s)}">
                    &times;
                </button>
            </span>
        `).join('');

        skillChipsContainer.querySelectorAll('[data-remove-skill]').forEach((b) => {
            b.addEventListener('click', () => {
                const i = Number(b.getAttribute('data-remove-skill'));
                registerSkills.splice(i, 1);
                renderSkillChips();
            });
        });
    }

    function addSkillFromInput() {
        if (!skillInput) return;
        const val = skillInput.value.trim();
        if (!val) return;
        const lower = val.toLowerCase();
        if (!registerSkills.some((s) => s.toLowerCase() === lower)) {
            registerSkills.push(val);
            renderSkillChips();
        }
        skillInput.value = '';
        skillInput.focus();
    }

    btnAddSkill?.addEventListener('click', addSkillFromInput);
    skillInput?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            addSkillFromInput();
        }
    });

    // 7. Submit Form Register
    const registerForm = document.getElementById('register-form');
    registerForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearFieldErrors();

        const btn = registerForm.querySelector('button[type="submit"]');
        const name = document.getElementById('reg-name')?.value.trim();
        const email = document.getElementById('reg-email')?.value.trim();
        const password = document.getElementById('reg-password')?.value;
        const role = document.querySelector('input[name="role"]:checked')?.value || 'applicant';
        const phone = document.getElementById('reg-phone')?.value.trim();
        const location = document.getElementById('reg-location')?.value.trim();

        const payload = {
            name,
            email,
            password,
            role,
            phone: phone || null,
            location: location || null,
            skills: registerSkills,
        };

        setLoading(btn, true, 'Mendaftarkan Akun...');
        try {
            const user = await auth.register(payload);
            toast('Pendaftaran akun berhasil! Selamat datang di JobHarbour.', 'success');

            setTimeout(() => {
                if (nextUrl) {
                    window.location.href = decodeURIComponent(nextUrl);
                } else {
                    window.location.href = JH.homeFor(user);
                }
            }, 650);
        } catch (err) {
            setLoading(btn, false);
            if (err.errors) {
                showFieldErrors(err.errors);
            } else {
                toast(err.message || 'Pendaftaran gagal.', 'error');
            }
        }
    });
});
