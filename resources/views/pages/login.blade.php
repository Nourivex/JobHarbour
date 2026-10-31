<!DOCTYPE html>
<html class="light" lang="id">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Masuk Pelabuhan - JobHarbour</title>
<link rel="icon" href="/favicon.ico"/>
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;family=Plus+Jakarta+Sans:wght@600;700;800&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com?plugins=forms"></script>
<script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            "primary-container": "#131b2e",
            "secondary": "#006398",
            "secondary-container": "#5bb8fe",
            "secondary-fixed": "#cce5ff",
            "surface": "#f7f9fb",
            "on-surface": "#191c1e",
            "on-surface-variant": "#45464d",
            "outline-variant": "#c6c6cd",
            "tertiary-fixed-dim": "#ffb95f",
            "error": "#ba1a1a"
          },
          fontFamily: {
            headline: ["Plus Jakarta Sans"],
            body: ["Inter"]
          }
        }
      }
    }
</script>
<style>
    body { font-family: 'Inter', sans-serif; }
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    .wave-bg { background: radial-gradient(circle at 20% 20%, rgba(91,184,254,.25), transparent 45%), radial-gradient(circle at 80% 80%, rgba(255,185,95,.18), transparent 40%), #131b2e; }
</style>
</head>
<body class="bg-surface text-on-surface min-h-screen">
<div class="min-h-screen grid lg:grid-cols-2">
    <!-- Panel kiri: branding -->
    <aside class="wave-bg hidden lg:flex flex-col justify-between p-12 text-white">
        <a href="/" class="flex items-center gap-3">
            <img src="/assets/Logo JobHarbour_withoutbg.png" alt="JobHarbour" class="h-10 w-auto" onerror="this.style.display='none'"/>
            <span class="font-headline text-2xl font-extrabold tracking-tight">JobHarbour</span>
        </a>
        <div class="space-y-6 max-w-md">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/20 text-xs font-semibold tracking-wide">
                <span class="material-symbols-outlined text-base">sailing</span> Pelabuhan Talenta Digital
            </span>
            <h1 class="font-headline text-4xl font-bold leading-tight">Berlabuh dulu, baru berlayar ke dunia kerja profesional.</h1>
            <p class="text-white/70 leading-relaxed">Satu akun untuk cari lowongan, kirim lamaran dengan skor kecocokan otomatis, dan pantau linimasa seleksi secara real-time.</p>
            <ul class="space-y-3 text-sm text-white/80">
                <li class="flex items-center gap-3"><span class="material-symbols-outlined text-secondary-container">radar</span> Mesin matchmaking Weighted Jaccard</li>
                <li class="flex items-center gap-3"><span class="material-symbols-outlined text-secondary-container">timeline</span> Pipeline Screening - Assessment - Interview - Offering</li>
                <li class="flex items-center gap-3"><span class="material-symbols-outlined text-secondary-container">shield_lock</span> Autentikasi JWT per peran</li>
            </ul>
        </div>
        <p class="text-xs text-white/40">Kelompok 4 - Pemrograman Web Service - Universitas Harkat Negeri, Tegal</p>
    </aside>

    <!-- Panel kanan: form -->
    <main class="flex items-center justify-center p-6 sm:p-12">
        <div class="w-full max-w-md">
            <a href="/" class="lg:hidden flex items-center gap-2 mb-8">
                <span class="material-symbols-outlined text-secondary">anchor</span>
                <span class="font-headline text-xl font-extrabold">JobHarbour</span>
            </a>

            <!-- Tab -->
            <div class="flex p-1 bg-slate-100 rounded-xl mb-8" role="tablist">
                <button id="tab-login" class="flex-1 py-2.5 text-sm font-semibold rounded-lg transition bg-white shadow text-primary-container" type="button">Masuk</button>
                <button id="tab-register" class="flex-1 py-2.5 text-sm font-semibold rounded-lg transition text-slate-500" type="button">Daftar Akun</button>
            </div>

            <!-- Form Login -->
            <form id="form-login" class="space-y-5" novalidate>
                <div>
                    <h2 class="font-headline text-2xl font-bold">Selamat datang kembali</h2>
                    <p class="text-sm text-on-surface-variant mt-1">Masuk untuk lanjut ke dermaga kamu.</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5" for="login-email">Email</label>
                    <input id="login-email" name="email" type="email" autocomplete="email" required class="w-full rounded-lg border-outline-variant focus:border-secondary focus:ring-secondary" placeholder="nama@email.com"/>
                    <p class="field-error text-xs text-error mt-1 hidden" data-field="email"></p>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5" for="login-password">Kata Sandi</label>
                    <input id="login-password" name="password" type="password" autocomplete="current-password" required class="w-full rounded-lg border-outline-variant focus:border-secondary focus:ring-secondary" placeholder="Minimal 6 karakter"/>
                    <p class="field-error text-xs text-error mt-1 hidden" data-field="password"></p>
                </div>
                <button type="submit" class="w-full py-3 rounded-lg bg-primary-container text-white font-semibold hover:bg-slate-800 transition flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-lg">login</span> Masuk
                </button>

                <div class="rounded-xl border border-dashed border-outline-variant p-4 bg-white">
                    <p class="text-xs font-semibold text-on-surface-variant mb-3 uppercase tracking-wide">Akun demo sekali klik (Seeder)</p>
                    <div class="grid grid-cols-1 gap-2">
                        <button type="button" class="demo-account text-left px-3 py-2 rounded-lg hover:bg-slate-50 border border-slate-200 text-sm flex items-center justify-between" data-email="budi@jobharbour.id">
                            <span><b>Budi Santoso</b> - Pelamar (Backend)</span><span class="text-xs text-slate-400">budi@jobharbour.id</span>
                        </button>
                        <button type="button" class="demo-account text-left px-3 py-2 rounded-lg hover:bg-slate-50 border border-slate-200 text-sm flex items-center justify-between" data-email="siti@jobharbour.id">
                            <span><b>Siti Nurhaliza</b> - Pelamar (Data/Cloud)</span><span class="text-xs text-slate-400">siti@jobharbour.id</span>
                        </button>
                        <button type="button" class="demo-account text-left px-3 py-2 rounded-lg hover:bg-slate-50 border border-slate-200 text-sm flex items-center justify-between" data-email="andi@jobharbour.id">
                            <span><b>Andi Pratama</b> - Pelamar (Frontend)</span><span class="text-xs text-slate-400">andi@jobharbour.id</span>
                        </button>
                        <button type="button" class="demo-account text-left px-3 py-2 rounded-lg hover:bg-slate-50 border border-slate-200 text-sm flex items-center justify-between" data-email="dewi@jobharbour.id">
                            <span><b>Dewi Lestari</b> - Pelamar (Fullstack)</span><span class="text-xs text-slate-400">dewi@jobharbour.id</span>
                        </button>
                        <button type="button" class="demo-account text-left px-3 py-2 rounded-lg hover:bg-sky-50 border border-sky-200 text-sm flex items-center justify-between" data-email="recruiter@jobharbour.id">
                            <span><b>Fahren Andrean</b> - Recruiter (Harbour Master)</span><span class="text-xs text-sky-600 font-medium">recruiter@jobharbour.id</span>
                        </button>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-2">Password semua akun demo: <code>password123</code></p>
                </div>
            </form>

            <!-- Form Register -->
            <form id="form-register" class="space-y-4 hidden" novalidate>
                <div>
                    <h2 class="font-headline text-2xl font-bold">Buat akun baru</h2>
                    <p class="text-sm text-on-surface-variant mt-1">Pilih peran kamu, lalu isi data singkat.</p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <label class="role-option cursor-pointer rounded-xl border-2 border-secondary bg-secondary-fixed/40 p-3 flex flex-col gap-1">
                        <input type="radio" name="role" value="applicant" class="sr-only" checked/>
                        <span class="material-symbols-outlined text-secondary">person_search</span>
                        <span class="text-sm font-semibold">Pelamar</span>
                        <span class="text-xs text-slate-500">Cari &amp; lamar kerja</span>
                    </label>
                    <label class="role-option cursor-pointer rounded-xl border-2 border-slate-200 p-3 flex flex-col gap-1">
                        <input type="radio" name="role" value="recruiter" class="sr-only"/>
                        <span class="material-symbols-outlined text-slate-500">badge</span>
                        <span class="text-sm font-semibold">Recruiter</span>
                        <span class="text-xs text-slate-500">Posting &amp; seleksi</span>
                    </label>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5" for="reg-name">Nama Lengkap</label>
                    <input id="reg-name" name="name" type="text" required class="w-full rounded-lg border-outline-variant focus:border-secondary focus:ring-secondary"/>
                    <p class="field-error text-xs text-error mt-1 hidden" data-field="name"></p>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5" for="reg-email">Email</label>
                    <input id="reg-email" name="email" type="email" required class="w-full rounded-lg border-outline-variant focus:border-secondary focus:ring-secondary"/>
                    <p class="field-error text-xs text-error mt-1 hidden" data-field="email"></p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold mb-1.5" for="reg-password">Kata Sandi</label>
                        <input id="reg-password" name="password" type="password" required class="w-full rounded-lg border-outline-variant focus:border-secondary focus:ring-secondary"/>
                        <p class="field-error text-xs text-error mt-1 hidden" data-field="password"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1.5" for="reg-location">Kota</label>
                        <input id="reg-location" name="location" type="text" class="w-full rounded-lg border-outline-variant focus:border-secondary focus:ring-secondary" placeholder="Tegal"/>
                    </div>
                </div>
                <div id="reg-skills-wrap">
                    <label class="block text-sm font-semibold mb-1.5" for="reg-skills">Keahlian <span class="font-normal text-slate-400">(pisahkan dengan koma)</span></label>
                    <input id="reg-skills" name="skills" type="text" class="w-full rounded-lg border-outline-variant focus:border-secondary focus:ring-secondary" placeholder="PHP, Laravel, MySQL"/>
                </div>
                <button type="submit" class="w-full py-3 rounded-lg bg-secondary text-white font-semibold hover:bg-sky-800 transition flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-lg">how_to_reg</span> Daftar &amp; Masuk
                </button>
            </form>

            <p class="text-center text-sm text-slate-500 mt-8"><a href="/catalog" class="text-secondary font-semibold hover:underline">Lihat lowongan dulu tanpa login &rarr;</a></p>
        </div>
    </main>
</div>

<script src="/js/jh-api.js"></script>
<script>
(function () {
    const params = new URLSearchParams(location.search);
    const next = params.get('next');

    // Kalau sudah login, langsung lempar ke halaman tujuan
    if (JH.session.isLoggedIn() && JH.session.user()) {
        location.replace(next || JH.homeFor(JH.session.user()));
        return;
    }

    const formLogin = document.getElementById('form-login');
    const formRegister = document.getElementById('form-register');
    const tabLogin = document.getElementById('tab-login');
    const tabRegister = document.getElementById('tab-register');

    function switchTab(which) {
        const isLogin = which === 'login';
        formLogin.classList.toggle('hidden', !isLogin);
        formRegister.classList.toggle('hidden', isLogin);
        tabLogin.className = 'flex-1 py-2.5 text-sm font-semibold rounded-lg transition ' + (isLogin ? 'bg-white shadow text-primary-container' : 'text-slate-500');
        tabRegister.className = 'flex-1 py-2.5 text-sm font-semibold rounded-lg transition ' + (!isLogin ? 'bg-white shadow text-primary-container' : 'text-slate-500');
    }
    tabLogin.addEventListener('click', () => switchTab('login'));
    tabRegister.addEventListener('click', () => switchTab('register'));
    if (params.get('mode') === 'register') switchTab('register');

    function clearErrors(form) {
        form.querySelectorAll('.field-error').forEach((el) => { el.textContent = ''; el.classList.add('hidden'); });
    }
    function showFieldErrors(form, errors) {
        if (!errors) return;
        Object.entries(errors).forEach(([field, msgs]) => {
            const el = form.querySelector(`.field-error[data-field="${field}"]`);
            if (el) { el.textContent = [].concat(msgs).join(' '); el.classList.remove('hidden'); }
        });
    }
    function afterAuth(user) {
        JH.toast('Halo ' + user.name + ', selamat berlabuh!');
        setTimeout(() => location.replace(next || JH.homeFor(user)), 500);
    }

    // Isi otomatis akun demo
    document.querySelectorAll('.demo-account').forEach((btn) => {
        btn.addEventListener('click', () => {
            document.getElementById('login-email').value = btn.dataset.email;
            document.getElementById('login-password').value = 'password123';
            formLogin.requestSubmit();
        });
    });

    formLogin.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearErrors(formLogin);
        const btn = formLogin.querySelector('button[type="submit"]');
        JH.setLoading(btn, true, 'Masuk...');
        try {
            const user = await JH.auth.login(formLogin.email.value.trim(), formLogin.password.value);
            afterAuth(user);
        } catch (err) {
            showFieldErrors(formLogin, err.errors);
            JH.showError(err);
            JH.setLoading(btn, false);
        }
    });

    // Pilihan role (styling radio card)
    const skillsWrap = document.getElementById('reg-skills-wrap');
    formRegister.querySelectorAll('.role-option').forEach((label) => {
        label.addEventListener('click', () => {
            formRegister.querySelectorAll('.role-option').forEach((l) => {
                l.className = 'role-option cursor-pointer rounded-xl border-2 border-slate-200 p-3 flex flex-col gap-1';
            });
            label.className = 'role-option cursor-pointer rounded-xl border-2 border-secondary bg-secondary-fixed/40 p-3 flex flex-col gap-1';
            skillsWrap.style.display = label.querySelector('input').value === 'recruiter' ? 'none' : '';
        });
    });

    formRegister.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearErrors(formRegister);
        const btn = formRegister.querySelector('button[type="submit"]');
        const role = formRegister.querySelector('input[name="role"]:checked').value;
        const skills = formRegister.skills.value.split(',').map((s) => s.trim()).filter(Boolean);
        JH.setLoading(btn, true, 'Mendaftarkan...');
        try {
            const user = await JH.auth.register({
                name: formRegister.name.value.trim(),
                email: formRegister.email.value.trim(),
                password: formRegister.password.value,
                location: formRegister.location.value.trim() || null,
                role,
                skills: role === 'applicant' ? skills : [],
            });
            afterAuth(user);
        } catch (err) {
            showFieldErrors(formRegister, err.errors);
            JH.showError(err);
            JH.setLoading(btn, false);
        }
    });
})();
</script>
</body>
</html>

