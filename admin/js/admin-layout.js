/**
 * admin-layout.js — Sidebar hover-expand + auto-logout
 * Sensasion Admin Panel
 */


(function () {
  const isLogin = location.pathname.includes('login.html');
  if (isLogin) return;

  
  const loggedLS = localStorage.getItem('sns_admin_logged') === 'true';
  const loggedSS = sessionStorage.getItem('sns_admin_logged') === 'true';

  if (!loggedLS || !loggedSS) {
    
    localStorage.removeItem('sns_admin_logged');
    localStorage.removeItem('sns_admin_user');
    sessionStorage.removeItem('sns_admin_logged');
    location.replace('login.html');
    return;
  }

  
  
  window.history.pushState({ adminPage: true }, '', window.location.href);
  window.addEventListener('popstate', function(e) {
    const stillLogged = localStorage.getItem('sns_admin_logged') === 'true'
                     && sessionStorage.getItem('sns_admin_logged') === 'true';
    if (!stillLogged) {
      location.replace('login.html');
    } else {
      window.history.pushState({ adminPage: true }, '', window.location.href);
    }
  });
})();


const AUTO_LOGOUT_MINUTES = 30;
const WARN_BEFORE_SECONDS = 60;
const TIMEOUT_MS = AUTO_LOGOUT_MINUTES * 60 * 1000;
const WARNING_MS = TIMEOUT_MS - WARN_BEFORE_SECONDS * 1000;

const Idle = {
  timer: null, warnTimer: null, countdown: null, dispTimer: null,
  lastActive: Date.now(), warningShown: false,
};
const ACTIVITY_EVENTS = ['mousemove','mousedown','keydown','touchstart','click','scroll'];

function resetIdleTimer() {
  if (location.pathname.includes('login.html')) return;
  if (localStorage.getItem('sns_admin_logged') !== 'true') return;
  if (sessionStorage.getItem('sns_admin_logged') !== 'true') return;
  Idle.lastActive = Date.now();
  if (Idle.warningShown) { hideIdleWarning(); Idle.warningShown = false; }
  clearTimeout(Idle.timer); clearTimeout(Idle.warnTimer); clearInterval(Idle.countdown);
  Idle.warnTimer = setTimeout(() => { Idle.warningShown = true; showIdleWarning(); startCountdown(); }, WARNING_MS);
  Idle.timer = setTimeout(forceLogout, TIMEOUT_MS);
}

function startCountdown() {
  clearInterval(Idle.countdown);
  let secs = WARN_BEFORE_SECONDS;
  updateCountdownEl(secs);
  Idle.countdown = setInterval(() => { secs--; updateCountdownEl(secs); if (secs <= 0) clearInterval(Idle.countdown); }, 1000);
}
function updateCountdownEl(sec) {
  const el = document.getElementById('idleCountdown');
  if (el) el.textContent = Math.max(0, sec);
}

function showIdleWarning() {
  if (document.getElementById('idleWarningOverlay')) return;
  const st = document.createElement('style');
  st.textContent = `
    @keyframes idleFadeIn{from{opacity:0;transform:scale(.95)}to{opacity:1;transform:scale(1)}}
    @keyframes idlePulse{0%,100%{transform:scale(1)}50%{transform:scale(1.1)}}
    #idleWarningOverlay{position:fixed;inset:0;z-index:99999;background:rgba(8,21,42,0.75);backdrop-filter:blur(7px);display:flex;align-items:center;justify-content:center;animation:idleFadeIn .3s ease}
    #idleWarningBox{background:#fff;border-radius:24px;padding:36px 32px;max-width:360px;width:90%;text-align:center;box-shadow:0 28px 80px rgba(0,0,0,.38)}
    .idle-icon{width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#f59e0b,#fbbf24);display:flex;align-items:center;justify-content:center;margin:0 auto 18px;font-size:2rem;color:#fff;animation:idlePulse 1.4s ease-in-out infinite}
    .idle-title{font-size:1.15rem;color:#08152a;margin-bottom:8px;font-weight:800}
    .idle-desc{color:#5a7a96;font-size:.86rem;margin-bottom:12px;line-height:1.5}
    #idleCountdown{font-size:3.8rem;font-weight:900;line-height:1;color:#dc2626}
    #btnStayLoggedIn{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:13px;border-radius:14px;border:none;cursor:pointer;background:linear-gradient(135deg,#0c7ec4,#2eb5ff);color:#fff;font-weight:700;font-size:.92rem;margin-bottom:10px}
    #btnLogoutNow{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:10px;border-radius:14px;cursor:pointer;background:rgba(239,68,68,.08);color:#dc2626;border:1.5px solid rgba(239,68,68,.22);font-weight:600;font-size:.85rem}`;
  document.head.appendChild(st);
  const ov = document.createElement('div');
  ov.id = 'idleWarningOverlay';
  ov.innerHTML = `<div id="idleWarningBox">
    <div class="idle-icon"><i class="bi bi-shield-lock"></i></div>
    <h5 class="idle-title">Sesi Hampir Berakhir</h5>
    <p class="idle-desc">Anda tidak aktif. Sesi akan otomatis berakhir dalam</p>
    <div style="display:flex;align-items:baseline;justify-content:center;gap:6px;margin:8px auto 16px">
      <span id="idleCountdown">${WARN_BEFORE_SECONDS}</span>
      <span style="font-size:1rem;font-weight:600;color:#5a7a96">detik</span>
    </div>
    <button id="btnStayLoggedIn" onclick="stayLoggedIn()"><i class="bi bi-check-circle"></i> Saya Masih Di Sini</button>
    <button id="btnLogoutNow" onclick="forceLogout()"><i class="bi bi-box-arrow-right"></i> Logout Sekarang</button>
  </div>`;
  document.body.appendChild(ov);
}
function hideIdleWarning() {
  const el = document.getElementById('idleWarningOverlay');
  if (!el) return;
  el.style.transition = 'opacity .22s'; el.style.opacity = '0';
  setTimeout(() => el?.remove(), 230);
}
function stayLoggedIn() { clearInterval(Idle.countdown); Idle.warningShown = false; hideIdleWarning(); resetIdleTimer(); }
function forceLogout() {
  clearTimeout(Idle.timer); clearTimeout(Idle.warnTimer); clearInterval(Idle.countdown); clearInterval(Idle.dispTimer);
  localStorage.removeItem('sns_admin_logged'); localStorage.removeItem('sns_admin_user');
  sessionStorage.removeItem('sns_admin_logged');
  localStorage.setItem('sns_logout_reason', 'idle');
  location.replace('login.html');
}
function initAutoLogout() {
  if (location.pathname.includes('login.html')) return;
  ACTIVITY_EVENTS.forEach(e => document.addEventListener(e, resetIdleTimer, { passive: true }));
  resetIdleTimer();
}
function startSessionTimerDisplay() {
  clearInterval(Idle.dispTimer);
  updateSessionDisplay();
  Idle.dispTimer = setInterval(updateSessionDisplay, 5000);
}
function updateSessionDisplay() {
  const ind = document.getElementById('sessionIndicator');
  const dot = document.getElementById('sessionDot');
  const lbl = document.getElementById('sessionLabel');
  if (!ind) { clearInterval(Idle.dispTimer); return; }
  const left = Math.max(0, TIMEOUT_MS - (Date.now() - Idle.lastActive));
  const isWarn = left <= WARN_BEFORE_SECONDS * 1000;
  ind.style.background  = isWarn ? 'rgba(239,68,68,.1)'  : 'rgba(34,197,94,.1)';
  ind.style.borderColor = isWarn ? 'rgba(239,68,68,.22)' : 'rgba(34,197,94,.22)';
  ind.style.color       = isWarn ? '#dc2626' : '#15803d';
  if (dot) dot.style.background = isWarn ? '#ef4444' : '#22c55e';
  if (lbl) lbl.textContent = isWarn ? 'Hampir Habis' : 'Aktif';
}


const ADMIN_MENU = [
  { section: 'Menu Utama' },
  { href: 'index.html',      icon: 'bi-speedometer2',    label: 'Dashboard'       },
  { href: 'reservasi.html',  icon: 'bi-calendar-check',  label: 'Data Reservasi'  },
  { href: 'scan-qr.html',    icon: 'bi-qr-code-scan',    label: 'Scan QR'         },
  { href: 'users.html',      icon: 'bi-people',           label: 'Manajemen User'  },
  { section: 'Konten' },
  { href: 'fasilitas.html',  icon: 'bi-grid-3x3-gap',    label: 'Fasilitas'       },
  { href: 'ulasan.html',     icon: 'bi-chat-quote',       label: 'Ulasan'          },
  { href: 'event.html',      icon: 'bi-calendar-event',   label: 'Event'           },
  { href: 'galeri.html',     icon: 'bi-images',           label: 'Galeri'          },
  { href: 'harga.html',      icon: 'bi-tags',             label: 'Harga'           },
  { section: 'Sistem' },
  { href: 'settings.html',   icon: 'bi-gear',             label: 'Pengaturan'      },
];


function renderAdminLayout(activePage, pageTitle) {
  const page  = activePage || (location.pathname.split('/').pop() || 'index.html');
  const title = pageTitle  || ADMIN_MENU.filter(m => m.href).find(m => m.href === page)?.label || 'Admin';
  const user  = (() => { try { const u = JSON.parse(localStorage.getItem('sns_admin_user') || '{}'); return typeof u === 'object' ? (u.nama || 'Admin') : (u || 'Admin'); } catch { return 'Admin'; } })();
  const pinned = localStorage.getItem('sns_sidebar_pinned') === 'true';

  
  let sideLinksHTML = '';
  ADMIN_MENU.forEach(m => {
    if (m.section) {
      sideLinksHTML += `<div class="sidebar-section-label">${m.section}</div>`;
    } else {
      const active = page === m.href ? ' active' : '';
      sideLinksHTML += `
        <a href="${m.href}" class="sidebar-link${active}" title="${m.label}">
          <span class="sidebar-link-icon"><i class="bi ${m.icon}"></i></span>
          <span class="sidebar-link-label">${m.label}</span>
        </a>`;
    }
  });

  const sessionBadge = `
    <div id="sessionIndicator" style="display:flex;align-items:center;gap:5px;padding:5px 11px;border-radius:10px;background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.22);font-size:.72rem;font-weight:700;color:#15803d;cursor:default;user-select:none;">
      <span id="sessionDot" style="width:7px;height:7px;border-radius:50%;background:#22c55e;display:inline-block;animation:sessionPulse 2s ease infinite;"></span>
      <span id="sessionLabel">Aktif</span>
    </div>
    <style>@keyframes sessionPulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.45;transform:scale(1.35)}}</style>`;

  const wrapper = document.createElement('div');
  wrapper.className = 'admin-wrapper';
  wrapper.id = 'adminWrapper';

  
  const userData = (() => { try { return JSON.parse(localStorage.getItem('sns_admin_user') || '{}'); } catch { return {}; } })();
  const fotoUrl = userData.foto_profil || null;
  const avatarStyle = fotoUrl
    ? `style="width:32px;height:32px;border-radius:50%;object-fit:cover;border:2px solid rgba(12,126,196,.3);cursor:pointer;" onclick="openFotoProfilModal()" title="Ganti foto profil"`
    : `style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#0c7ec4,#2eb5ff);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:.82rem;flex-shrink:0;cursor:pointer;" onclick="openFotoProfilModal()" title="Ganti foto profil"`;

  const avatarHTML = fotoUrl
    ? `<img src="${fotoUrl}" alt="Foto" ${avatarStyle}>`
    : `<span ${avatarStyle}>${user.split(' ').map(w=>w[0]).join('').slice(0,2).toUpperCase()}</span>`;

  wrapper.innerHTML = `
    <aside class="admin-sidebar${pinned ? ' pinned' : ''}" id="adminSidebar">
      <div class="sidebar-brand">
        <div class="sidebar-brand-icon"><i class="bi bi-water"></i></div>
        <div class="sidebar-brand-text">
          <em>Sensasion</em> Admin
          <span class="sidebar-brand-sub">Panel Kontrol</span>
        </div>
        <button class="sidebar-pin" id="sidebarPin" onclick="togglePin(event)" title="${pinned ? 'Lepas Pin Sidebar' : 'Pin Sidebar'}">
          <i class="bi ${pinned ? 'bi-pin-fill' : 'bi-pin'}"></i>
        </button>
      </div>

      <nav class="sidebar-nav">
        ${sideLinksHTML}
      </nav>

      <div class="sidebar-footer">
        <a href="../index.html" class="sidebar-link" title="Website">
          <span class="sidebar-link-icon"><i class="bi bi-arrow-left-circle"></i></span>
          <span class="sidebar-link-label">Ke Website</span>
        </a>
        <a href="#" class="sidebar-link" onclick="doLogout(); return false;" title="Logout">
          <span class="sidebar-link-icon" style="color:#ef4444"><i class="bi bi-box-arrow-right"></i></span>
          <span class="sidebar-link-label" style="color:#ef4444">Logout</span>
        </a>
      </div>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <div class="admin-main">
      <header class="admin-topbar">
        <button class="topbar-toggle" onclick="openSidebar()"><i class="bi bi-list"></i></button>
        <div class="topbar-title">${title}</div>
        <div class="d-flex align-items-center gap-2 ms-auto flex-wrap">
          ${sessionBadge}
          <span class="topbar-badge" style="gap:7px;">${avatarHTML} ${user}</span>
          <button onclick="doLogout()"
            style="background:rgba(239,68,68,0.08);color:#ef4444;border:1px solid rgba(239,68,68,0.2);padding:5px 12px;border-radius:10px;font-size:.78rem;font-weight:700;cursor:pointer;white-space:nowrap;display:flex;align-items:center;gap:5px;">
            <i class="bi bi-box-arrow-right"></i> Keluar
          </button>
        </div>
      </header>
      <div class="admin-content" id="adminContent"></div>
    </div>`;

  document.body.innerHTML = '';
  document.body.appendChild(wrapper);
  initAutoLogout();
  startSessionTimerDisplay();
}


function openSidebar() {
  document.getElementById('adminSidebar')?.classList.add('open');
  document.getElementById('sidebarOverlay')?.classList.add('open');
}
function closeSidebar() {
  document.getElementById('adminSidebar')?.classList.remove('open');
  document.getElementById('sidebarOverlay')?.classList.remove('open');
}
function togglePin(e) {
  e.preventDefault(); e.stopPropagation();
  const sb = document.getElementById('adminSidebar');
  const isPinned = sb.classList.toggle('pinned');
  localStorage.setItem('sns_sidebar_pinned', isPinned);
  const btn = document.getElementById('sidebarPin');
  if (btn) btn.innerHTML = `<i class="bi ${isPinned ? 'bi-pin-fill' : 'bi-pin'}"></i>`;
}


function doLogout() {
  if (!confirm('Yakin ingin keluar dari admin panel?')) return;
  clearTimeout(Idle.timer); clearTimeout(Idle.warnTimer); clearInterval(Idle.countdown); clearInterval(Idle.dispTimer);
  localStorage.removeItem('sns_admin_logged'); localStorage.removeItem('sns_admin_user'); localStorage.removeItem('sns_logout_reason');
  sessionStorage.removeItem('sns_admin_logged');
  location.replace('login.html');
}


function showAdminAlert(msg, type = 'success') {
  const old = document.getElementById('adminAlert');
  if (old) old.remove();
  const el = document.createElement('div');
  el.id = 'adminAlert';
  el.className = `alert-box alert-${type}`;
  el.innerHTML = `<i class="bi ${type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle'}"></i> ${msg}`;
  const content = document.getElementById('adminContent');
  if (content) content.prepend(el);
  setTimeout(() => el?.remove(), 3500);
}
function openModal(html, id = 'adminModal') {
  let ov = document.getElementById(id);
  if (ov) ov.remove();
  ov = document.createElement('div');
  ov.className = 'modal-overlay'; ov.id = id;
  ov.innerHTML = html;
  document.body.appendChild(ov);
  ov.addEventListener('click', e => { if (e.target === ov) closeModal(id); });
}
function closeModal(id = 'adminModal') { document.getElementById(id)?.remove(); }

function openFotoProfilModal() {
  const userData = (() => { try { return JSON.parse(localStorage.getItem('sns_admin_user') || '{}'); } catch { return {}; } })();
  if (!userData.id) {
    alert('Data user tidak ditemukan. Silakan login ulang.');
    return;
  }
  const fotoSrc = userData.foto_profil || null;
  const initials = (userData.nama || 'A').split(' ').map(w=>w[0]).join('').slice(0,2).toUpperCase();
  const previewHTML = fotoSrc
    ? `<img id="fpPreview" src="${fotoSrc}" style="width:96px;height:96px;border-radius:50%;object-fit:cover;border:3px solid rgba(12,126,196,.25);">`
    : `<div id="fpPreview" style="width:96px;height:96px;border-radius:50%;background:linear-gradient(135deg,#0c7ec4,#2eb5ff);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.8rem;font-weight:700;">${initials}</div>`;

  openModal(`<div class="modal-box" style="max-width:380px;text-align:center;">
    <h3 class="modal-title" style="text-align:left;"><i class="bi bi-person-circle" style="color:var(--teal);margin-right:8px;"></i>Foto Profil</h3>
    <div style="display:flex;flex-direction:column;align-items:center;gap:14px;padding:8px 0 20px;">
      ${previewHTML}
      <div>
        <div style="font-weight:700;color:var(--navy);font-size:.95rem;">${userData.nama || 'Admin'}</div>
        <div style="font-size:.78rem;color:var(--muted);">${userData.email || ''}</div>
      </div>
      <label style="cursor:pointer;background:rgba(12,126,196,.1);color:var(--teal);border:1px solid rgba(12,126,196,.25);padding:8px 18px;border-radius:10px;font-size:.84rem;font-weight:700;display:inline-flex;align-items:center;gap:7px;">
        <i class="bi bi-camera"></i> Pilih Foto Baru
        <input type="file" id="fpInput" accept="image/jpeg,image/png,image/webp" style="display:none;" onchange="previewFotoProfil(this)">
      </label>
      <div style="font-size:.73rem;color:var(--muted);">Max 5MB · JPG, PNG, WebP</div>
    </div>
    <div class="modal-actions">
      <button class="btn-cancel" onclick="closeModal()">Batal</button>
      <button class="btn-save" id="fpSaveBtn" onclick="saveFotoProfil(${userData.id})" disabled style="opacity:.5;cursor:not-allowed;">
        <i class="bi bi-check-lg"></i> Simpan
      </button>
    </div>
  </div>`);
}

function previewFotoProfil(input) {
  const file = input.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = (e) => {
    const prev = document.getElementById('fpPreview');
    if (!prev) return;
    if (prev.tagName === 'IMG') {
      prev.src = e.target.result;
    } else {
      const img = document.createElement('img');
      img.id = 'fpPreview';
      img.src = e.target.result;
      img.style.cssText = 'width:96px;height:96px;border-radius:50%;object-fit:cover;border:3px solid rgba(12,126,196,.25);';
      prev.replaceWith(img);
    }
    const btn = document.getElementById('fpSaveBtn');
    if (btn) { btn.disabled = false; btn.style.opacity = '1'; btn.style.cursor = 'pointer'; }
  };
  reader.readAsDataURL(file);
}

async function saveFotoProfil(userId) {
  const input = document.getElementById('fpInput');
  if (!input || !input.files.length) return;
  const btn = document.getElementById('fpSaveBtn');
  btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Mengupload...';
  btn.disabled = true;
  try {
    const fd = new FormData();
    fd.append('foto', input.files[0]);
    fd.append('user_id', userId);
    const res  = await fetch('../api/foto_profil.php', { method: 'POST', body: fd });
    const data = await res.json();
    if (!data.ok) throw new Error(data.error);
    
    const u = JSON.parse(localStorage.getItem('sns_admin_user') || '{}');
    u.foto_profil = data.foto_url;
    localStorage.setItem('sns_admin_user', JSON.stringify(u));
    closeModal();
    showAdminAlert('Foto profil berhasil diperbarui! Halaman akan dimuat ulang.');
    setTimeout(() => location.reload(), 1200);
  } catch(e) {
    btn.innerHTML = '<i class="bi bi-check-lg"></i> Simpan';
    btn.disabled = false;
    alert('Gagal upload: ' + e.message);
  }
}
