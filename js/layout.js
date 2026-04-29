/**
 * layout.js — Render Navbar & Footer ke semua halaman
 * Sensasion
 */

function renderNavbar(activePage) {
  const pages = [
    { href: 'fasilitas.html', label: 'Fasilitas',  icon: 'bi-grid-3x3-gap' },
    { href: 'galeri.html',    label: 'Galeri',     icon: 'bi-images' },
    { href: 'harga.html',     label: 'Harga',      icon: 'bi-tags' },
    { href: 'event.html',     label: 'Event',      icon: 'bi-calendar-event' },
    { href: 'reservasi.html',     label: 'Reservasi',   icon: 'bi-calendar-check' },
    { href: 'cek-reservasi.html', label: 'Cek Status',  icon: 'bi-search-heart' },
    { href: 'kontak.html',        label: 'Kontak',      icon: 'bi-envelope' },
  ];

  const hamItems = pages.map(p => `
    <a href="${p.href}" class="ham-item${activePage===p.href?' active':''}" data-page="${p.href}">
      <i class="bi ${p.icon}"></i>${p.label}
    </a>`).join('');

  const mobileItems = [
    { href: 'index.html',    label: 'Beranda',   icon: 'bi-house' },
    { href: 'detail.html',   label: 'Detail',    icon: 'bi-info-circle' },
    ...pages
  ].map(p => `
    <a href="${p.href}" class="mobile-nav-link${activePage===p.href?' active':''}" data-page="${p.href}">
      <i class="bi ${p.icon}"></i>${p.label}
    </a>`).join('');

  return `
  <nav class="navbar-custom" id="mainNav">
    <div class="container">
      <div class="d-flex align-items-center justify-content-between">
        <a href="index.html" class="nav-brand">
          <img
            src="public/assets/logo.png"
            alt="Logo Sensasion"
            class="nav-logo"
            onerror="this.style.display='none'"
          />
          <em>Sensasion</em>
        </a>

        <!-- Desktop: Beranda + Detail visible, sisanya di hamburger -->
        <div class="d-none d-md-flex align-items-center gap-2">
          <a href="index.html" class="nav-link-custom${activePage==='index.html'?' active':''}" data-page="index.html">Beranda</a>
          <a href="detail.html" class="nav-link-custom${activePage==='detail.html'?' active':''}" data-page="detail.html">Detail</a>

          <!-- Hamburger dropdown -->
          <div class="position-relative ms-1">
            <button class="ham-btn" id="hamBtn" aria-label="Menu lainnya">
              <i class="bi bi-list" id="hamIcon"></i>
              <span style="font-size:.82rem;font-weight:600;">Lainnya</span>
            </button>
            <div class="ham-dropdown" id="hamDropdown">
              ${hamItems}
            </div>
          </div>
        </div>

        <!-- Mobile toggle -->
        <button class="ham-btn d-md-none" id="mobileToggle" aria-label="Buka menu">
          <i class="bi bi-list" id="mobileIcon"></i>
        </button>
      </div>

      <!-- Mobile menu -->
      <div class="mobile-nav" id="mobileNav">
        ${mobileItems}
      </div>
    </div>
  </nav>`;
}

function renderFooter() {
  return `
  <footer class="footer">
    <div class="container" id="footerContent">
      <!-- Diisi oleh main.js -->
    </div>
  </footer>
  <button id="backTop" aria-label="Kembali ke atas">
    <i class="bi bi-chevron-up"></i>
  </button>`;
}


document.addEventListener('DOMContentLoaded', () => {
  const navEl = document.getElementById('navbar-placeholder');
  const footEl = document.getElementById('footer-placeholder');
  const page = location.pathname.split('/').pop() || 'index.html';
  if (navEl) navEl.outerHTML = renderNavbar(page);
  if (footEl) footEl.outerHTML = renderFooter();
});
