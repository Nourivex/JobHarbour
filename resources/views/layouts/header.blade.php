<!-- ========================================================================= -->
<!-- SHARED COMPONENT: TopNavBar with Live Auth & Interactive Navigation      -->
<!-- ========================================================================= -->
<header class="fixed top-0 left-0 right-0 w-full z-50 bg-surface-container-lowest/95 backdrop-blur-md shadow-sm border-b border-outline-variant/50">
  <div class="max-w-[1600px] mx-auto px-4 sm:px-8 h-16 flex items-center justify-between">
    <!-- Brand Anchor & Identity -->
    <div class="flex items-center gap-6">
      <a class="text-headline-sm font-headline-sm font-bold tracking-tight text-on-surface flex items-center gap-2 transition-all duration-150 active:scale-95" href="/">
        <div class="w-9 h-9 rounded-lg bg-primary-container text-on-primary flex items-center justify-center shadow-sm">
          <span class="material-symbols-outlined text-secondary-container" style="font-variation-settings: 'FILL' 1;">anchor</span>
        </div>
        <span class="flex flex-col">
          <span class="leading-none text-headline-sm font-headline-sm font-bold text-slate-900">JobHarbour</span>
          <span class="text-[10px] tracking-wider text-secondary font-semibold uppercase">Career Launchpad</span>
        </span>
      </a>

      <!-- Desktop Navigation Links -->
      <nav class="hidden md:flex items-center gap-5 ml-4">
        <!-- The Anchorage -->
        <a class="nav-item pb-1 text-sm font-semibold flex items-center gap-1.5 transition-colors {{ request()->is('/') || request()->is('catalog') ? 'text-secondary border-b-2 border-secondary' : 'text-slate-600 hover:text-slate-900' }}" href="/">
          <span class="material-symbols-outlined text-lg">explore</span>
          <span>The Anchorage</span>
        </a>
        <!-- My Dock -->
        <a class="nav-item pb-1 text-sm font-semibold flex items-center gap-1.5 transition-colors {{ request()->is('my-dock') ? 'text-secondary border-b-2 border-secondary' : 'text-slate-600 hover:text-slate-900' }}" href="/my-dock">
          <span class="material-symbols-outlined text-lg">anchor</span>
          <span>My Dock</span>
        </a>
        <!-- Captain's Ledger -->
        <a class="nav-item pb-1 text-sm font-semibold flex items-center gap-1.5 transition-colors {{ request()->is('profile') ? 'text-secondary border-b-2 border-secondary' : 'text-slate-600 hover:text-slate-900' }}" href="/profile">
          <span class="material-symbols-outlined text-lg">person</span>
          <span>Captain's Ledger</span>
        </a>
        <!-- Harbour Master -->
        <a class="nav-item pb-1 text-sm font-semibold flex items-center gap-1.5 transition-colors {{ request()->is('recruiter') ? 'text-secondary border-b-2 border-secondary' : 'text-slate-600 hover:text-slate-900' }}" href="/recruiter">
          <span class="material-symbols-outlined text-lg">dashboard</span>
          <span>Harbour Master</span>
          <span class="text-[9px] px-1.5 py-0.5 bg-amber-100 text-amber-800 rounded font-bold">PRO</span>
        </a>
        <!-- OpenAPI Docs -->
        <a class="nav-item pb-1 text-sm font-bold text-amber-600 hover:text-amber-700 flex items-center gap-1.5 transition-colors" href="/docs">
          <span class="material-symbols-outlined text-lg">api</span>
          <span>OpenAPI</span>
        </a>
      </nav>
    </div>

    <!-- Trailing Action Cluster (Live Auth) -->
    <div class="flex items-center gap-3">
      <!-- Guest State (Belum Login) -->
      <div id="headerGuestState" class="flex items-center gap-2">
        <button onclick="window.openLoginModal()" type="button" class="px-4 py-2 rounded-lg bg-surface-container-high hover:bg-slate-200 text-slate-800 text-xs font-bold transition-all flex items-center gap-1.5 shadow-sm active:scale-95">
          <span class="material-symbols-outlined text-sm text-secondary">login</span>
          <span>Masuk (Login)</span>
        </button>
        <a href="/profile" class="px-4 py-2 rounded-lg bg-secondary text-white text-xs font-bold hover:bg-on-secondary-container transition-all shadow-sm hidden sm:inline-flex items-center gap-1.5">
          <span>Daftar</span>
        </a>
      </div>

      <!-- Authenticated User State (Sudah Login) -->
      <div id="headerUserState" class="hidden items-center gap-3">
        <div class="flex items-center gap-2.5 pl-3 pr-2 py-1 rounded-full bg-slate-100 border border-slate-200 shadow-inner">
          <span class="material-symbols-outlined text-secondary text-sm">anchor</span>
          <div class="flex flex-col text-left">
            <span id="headerUserName" class="text-xs font-bold text-slate-800 leading-tight">Nama Pengguna</span>
            <span id="headerUserRole" class="text-[10px] text-secondary font-medium leading-none uppercase">Pelamar Aktif</span>
          </div>
          <div id="headerUserAvatar" class="w-7 h-7 rounded-full bg-primary-container text-on-primary flex items-center justify-center text-xs font-bold ring-1 ring-secondary">
            U
          </div>
        </div>

        <!-- Logout Button -->
        <button onclick="window.handleLogout()" title="Keluar / Logout" class="p-2 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors">
          <span class="material-symbols-outlined text-lg">logout</span>
        </button>
      </div>

      <!-- Quick Apply CTA Button -->
      <a href="/apply" class="px-4 py-2 rounded-lg bg-slate-900 text-white text-xs font-bold hover:bg-secondary transition-all duration-150 active:scale-95 shadow-sm hidden lg:flex items-center gap-1.5">
        <span class="material-symbols-outlined text-sm">send</span>
        <span>Lamar Sekarang</span>
      </a>
    </div>
  </div>
</header>

<!-- Global Login Modal -->
<div id="globalAuthModal" class="fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 hidden">
  <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-lg bg-sky-100 text-sky-600 flex items-center justify-center">
          <span class="material-symbols-outlined text-xl">vpn_key</span>
        </div>
        <div>
          <h3 class="font-bold text-slate-900 text-base">Masuk ke JobHarbour</h3>
          <p class="text-[11px] text-slate-500">Autentikasi JWT Stateless Web Service</p>
        </div>
      </div>
      <button onclick="window.closeLoginModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
        <span class="material-symbols-outlined text-xl">close</span>
      </button>
    </div>

    <!-- Quick Role Switcher Chips -->
    <div class="my-4 p-3 rounded-xl bg-slate-50 border border-slate-200/80">
      <span class="text-[11px] font-bold text-slate-500 uppercase block mb-1.5">Pilih Akun Cepat Pengujian:</span>
      <div class="grid grid-cols-2 gap-2">
        <button type="button" onclick="window.fillCredentials('budi@jobharbour.id', 'password')" class="p-2 rounded-lg bg-white border border-slate-200 text-left hover:border-sky-500 transition-all text-xs">
          <span class="font-bold text-slate-800 block">Pelamar (Budi)</span>
          <span class="text-[10px] text-slate-500">budi@jobharbour.id</span>
        </button>
        <button type="button" onclick="window.fillCredentials('recruiter@jobharbour.id', 'password')" class="p-2 rounded-lg bg-white border border-slate-200 text-left hover:border-sky-500 transition-all text-xs">
          <span class="font-bold text-slate-800 block">Rekruter (Fahren)</span>
          <span class="text-[10px] text-slate-500">recruiter@jobharbour.id</span>
        </button>
      </div>
    </div>

    <form id="globalLoginForm" onsubmit="event.preventDefault(); window.submitLogin();" class="space-y-3.5">
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Email Pengguna</label>
        <input id="modalEmail" type="email" required value="budi@jobharbour.id" class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none"/>
      </div>
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kata Sandi</label>
        <input id="modalPassword" type="password" required value="password" class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none"/>
      </div>
      <button id="modalLoginSubmitBtn" type="submit" class="w-full py-3 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-sm shadow-md transition-all active:scale-95 flex items-center justify-center gap-2 mt-2">
        <span class="material-symbols-outlined text-base">login</span>
        <span>Masuk Sekarang (POST /api/auth/login)</span>
      </button>
    </form>
  </div>
</div>

<script>
window.openLoginModal = function() {
  document.getElementById('globalAuthModal').classList.remove('hidden');
};

window.closeLoginModal = function() {
  document.getElementById('globalAuthModal').classList.add('hidden');
};

window.fillCredentials = function(email, pass) {
  document.getElementById('modalEmail').value = email;
  document.getElementById('modalPassword').value = pass;
};

window.updateAuthHeaderUI = function() {
  const token = localStorage.getItem('jh_token');
  const userJson = localStorage.getItem('jh_user');
  const guestDiv = document.getElementById('headerGuestState');
  const userDiv = document.getElementById('headerUserState');

  if (token && userJson) {
    try {
      const user = JSON.parse(userJson);
      if (guestDiv) guestDiv.classList.add('hidden');
      if (userDiv) {
        userDiv.classList.remove('hidden');
        userDiv.classList.add('flex');
        document.getElementById('headerUserName').textContent = user.name || 'Pengguna';
        document.getElementById('headerUserRole').textContent = (user.role === 'recruiter' ? 'Rekruter Perusahaan' : 'Pelamar Aktif');
        const initials = (user.name || 'U').split(' ').map(n=>n[0]).slice(0,2).join('').toUpperCase();
        document.getElementById('headerUserAvatar').textContent = initials;
      }
    } catch(e) {
      console.error(e);
    }
  } else {
    if (guestDiv) guestDiv.classList.remove('hidden');
    if (userDiv) {
      userDiv.classList.add('hidden');
      userDiv.classList.remove('flex');
    }
  }
};

window.submitLogin = async function() {
  const email = document.getElementById('modalEmail').value;
  const password = document.getElementById('modalPassword').value;
  const btn = document.getElementById('modalLoginSubmitBtn');

  btn.disabled = true;
  btn.innerHTML = '<span class="material-symbols-outlined animate-spin text-sm">sync</span> Mengautentikasi...';

  try {
    const res = await fetch('/api/auth/login', {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ email, password })
    });
    const result = await res.json();

    if (res.status === 200 && result.data && result.data.token) {
      localStorage.setItem('jh_token', result.data.token);
      localStorage.setItem('jh_user', JSON.stringify(result.data.user || {}));
      window.updateAuthHeaderUI();
      window.closeLoginModal();
      alert('🎉 Login Berhasil! Selamat datang, ' + (result.data.user.name || '') + ' (' + result.data.user.role + ')');
    } else {
      alert('⛔ Gagal Login (' + res.status + '):\\n' + (result.message || 'Kredensial tidak cocok.'));
    }
  } catch (err) {
    console.error(err);
    alert('Terjadi kesalahan saat menghubungi API Auth Service.');
  } finally {
    btn.disabled = false;
    btn.innerHTML = '<span class="material-symbols-outlined text-base">login</span><span>Masuk Sekarang (POST /api/auth/login)</span>';
  }
};

window.handleLogout = function() {
  if (confirm('Apakah Anda yakin ingin keluar dari sesi JobHarbour?')) {
    localStorage.removeItem('jh_token');
    localStorage.removeItem('jh_user');
    window.updateAuthHeaderUI();
    alert('Sesi telah diakhiri. Token JWT telah dihapus.');
    window.location.reload();
  }
};

document.addEventListener('DOMContentLoaded', function() {
  window.updateAuthHeaderUI();
});
</script>
