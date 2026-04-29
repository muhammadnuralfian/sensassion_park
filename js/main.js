/**
 * main.js — Logic Halaman Publik
 * Sensasion
 */

document.addEventListener('DOMContentLoaded', () => {
  initNavbar();
  initBackToTop();
  initFadeUp();
  loadWeather();
  loadFooter();
  setActiveNav();
});


function initNavbar() {
  window.addEventListener('scroll', () => {
    const nav = document.getElementById('mainNav');
    if (nav) nav.classList.toggle('scrolled', window.scrollY > 40);
  });

  
  const hamBtn = document.getElementById('hamBtn');
  const hamDrop = document.getElementById('hamDropdown');
  if (hamBtn && hamDrop) {
    hamBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      hamDrop.classList.toggle('open');
    });
    document.addEventListener('click', () => hamDrop.classList.remove('open'));
  }


  const mobileToggle = document.getElementById('mobileToggle');
  const mobileNav = document.getElementById('mobileNav');
  const mobileIcon = document.getElementById('mobileIcon');
  if (mobileToggle && mobileNav) {
    mobileToggle.addEventListener('click', () => {
      const open = mobileNav.classList.toggle('open');
      if (mobileIcon) mobileIcon.className = open ? 'bi bi-x-lg' : 'bi bi-list';
    });
  }
}

function setActiveNav() {
  const page = location.pathname.split('/').pop() || 'index.html';
  document.querySelectorAll('[data-page]').forEach(el => {
    if (el.dataset.page === page) el.classList.add('active');
  });
}

/* ===== BACK TO TOP ===== */
function initBackToTop() {
  const btn = document.getElementById('backTop');
  if (!btn) return;
  window.addEventListener('scroll', () => {
    btn.style.display = window.scrollY > 400 ? 'flex' : 'none';
  });
  btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
}

/* ===== FADE UP ===== */
function initFadeUp() {
  const observer = new IntersectionObserver((entries) => {
    entries.forEach((e, i) => {
      if (e.isIntersecting)
        setTimeout(() => e.target.classList.add('visible'), i * 80);
    });
  }, { threshold: 0.08 });
  document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));
}

/* ===== WEATHER ===== */
async function loadWeather() {
  const el = document.getElementById('weatherBlock');
  if (!el) return;
  const API_KEY = '5f42fc64cb700a9f126b135a565afa08';
  const CITY = 'Samarinda';
  try {
    const res = await fetch(`https://api.openweathermap.org/data/2.5/weather?q=${CITY}&appid=${API_KEY}&units=metric&lang=id`);
    const d = await res.json();
    const temp = Math.round(d.main.temp);
    const feels = Math.round(d.main.feels_like);
    const desc = d.weather[0].description;
    const hum = d.main.humidity;
    const wind = Math.round(d.wind.speed * 3.6);
    const code = d.weather[0].icon;
    const wIcon = getWeatherEmoji(code);
    el.innerHTML = `
      <div class="weather-card">
        <div class="row align-items-center g-3">
          <div class="col-auto">
            <div class="weather-icon-lg">${wIcon}</div>
          </div>
          <div class="col">
            <div class="weather-temp">${temp}&deg;C</div>
            <div style="font-size:.88rem;color:rgba(255,255,255,.65);text-transform:capitalize;">${desc}</div>
            <div style="font-size:.78rem;color:rgba(255,255,255,.4);margin-top:2px;"><i class="bi bi-geo-alt-fill" style="font-size:.7rem;"></i> ${d.name}, Kalimantan Timur</div>
          </div>
          <div class="col-auto ms-auto">
            <div class="d-flex gap-4">
              <div class="text-center">
                <div class="weather-detail-val">${hum}%</div>
                <div class="weather-detail-lbl">Kelembaban</div>
              </div>
              <div class="text-center">
                <div class="weather-detail-val">${wind} km/h</div>
                <div class="weather-detail-lbl">Angin</div>
              </div>
              <div class="text-center">
                <div class="weather-detail-val">${feels}&deg;</div>
                <div class="weather-detail-lbl">Terasa</div>
              </div>
            </div>
          </div>
        </div>
      </div>`;
  } catch (e) {
    el.innerHTML = `<div class="weather-card"><p style="color:rgba(255,255,255,.6);margin:0;">Data cuaca tidak tersedia.</p></div>`;
  }
}

function getWeatherEmoji(code) {
  if (!code) return '🌤';
  if (code.startsWith('01')) return '☀️';
  if (code.startsWith('02')) return '⛅';
  if (code.startsWith('03')||code.startsWith('04')) return '☁️';
  if (code.startsWith('09')||code.startsWith('10')) return '🌧';
  if (code.startsWith('11')) return '⛈';
  if (code.startsWith('13')) return '❄️';
  if (code.startsWith('50')) return '🌫';
  return '🌤';
}

/* ===== FOOTER DYNAMIC ===== */
function loadFooter() {
  const el = document.getElementById('footerContent');
  if (!el || typeof DB === 'undefined') return;
  const s = DB.get('settings');
  el.innerHTML = `
    <div class="row g-4">
      <div class="col-lg-4 col-md-6">
        <div class="footer-brand"><em>Sensasion</em></div>
        <p class="footer-desc" style="font-size:.86rem;color:rgba(255,255,255,.5);line-height:1.7;margin-bottom:18px;">${s.tagline}. Destinasi wisata keluarga terbaik di ${s.lokasi}.</p>
        <a href="https://wa.me/${s.whatsapp}?text=${encodeURIComponent('Halo Sensasion!')}" target="_blank" class="btn-primary-sns" style="font-size:.82rem;padding:9px 18px;">
          <i class="bi bi-whatsapp"></i> WhatsApp Kami
        </a>
      </div>
      <div class="col-lg-2 col-md-6">
        <div class="footer-heading">Menu</div>
        <ul class="footer-links">
          <li><a href="index.html">Beranda</a></li>
          <li><a href="detail.html">Detail</a></li>
          <li><a href="fasilitas.html">Fasilitas</a></li>
          <li><a href="galeri.html">Galeri</a></li>
          <li><a href="harga.html">Harga</a></li>
          <li><a href="event.html">Event</a></li>
          <li><a href="reservasi.html">Reservasi</a></li>
          <li><a href="kontak.html">Kontak</a></li>
        </ul>
      </div>
      <div class="col-lg-3 col-md-6">
        <div class="footer-heading">Informasi</div>
        <div class="footer-info-item"><i class="bi bi-geo-alt"></i><span>${s.lokasi}</span></div>
        <div class="footer-info-item"><i class="bi bi-clock"></i><span>${s.jam_buka}<br><small style="opacity:.55">Tutup: ${s.hari_libur}</small></span></div>
        <div class="footer-info-item"><i class="bi bi-instagram"></i><a href="https://instagram.com/${s.instagram.replace('@','')}" target="_blank" style="color:rgba(255,255,255,.82);transition:color .2s;" onmouseover="this.style.color='#2eb5ff'" onmouseout="this.style.color='rgba(255,255,255,.82)'">${s.instagram}</a></div>
        <div class="footer-info-item"><i class="bi bi-person-badge"></i><span>Pengelola: ${s.pengelola}</span></div>
      </div>
      <div class="col-lg-3 col-md-6">
        <div class="footer-heading">Ikuti Kami</div>
        <ul class="footer-links">
          <li><a href="https://instagram.com/${s.instagram.replace('@','')}" target="_blank"><i class="bi bi-instagram"></i> ${s.instagram}</a></li>
          <li><a href="https://wa.me/${s.whatsapp}" target="_blank"><i class="bi bi-whatsapp"></i> WhatsApp</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom mt-4">
      &copy; ${new Date().getFullYear()} <em style="color:var(--sky);font-style:normal;">Sensasion</em>. Berdiri sejak ${s.tahun_berdiri}.
    </div>`;
}
