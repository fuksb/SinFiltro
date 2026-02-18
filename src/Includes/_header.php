<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$navItems = [
  ['href' => '/',           'icon' => 'fa-house',         'label' => 'Inicio'],
  ['href' => '/coche',      'icon' => 'fa-car-side',      'label' => 'Coches'],
  ['href' => '/hipoteca',   'icon' => 'fa-percent',       'label' => 'Hipotecas'],
  ['href' => '/luz',        'icon' => 'fa-bolt',          'label' => 'Luz'],
  ['href' => '/telco',      'icon' => 'fa-mobile-screen', 'label' => 'Telefonía'],
  ['href' => '/seguros',    'icon' => 'fa-umbrella',      'label' => 'Seguros'],
  ['href' => '/inversiones','icon' => 'fa-chart-pie',     'label' => 'Inversiones'],
];

// Helper para detectar página activa con URLs limpias
function getCleanPage($href) {
    if ($href === '/') return 'index.php';
    return ltrim($href, '/') . '.php';
}
?>
<style>
/* ── Page transitions ── */
@keyframes sf-fadeIn {
  from { opacity: 0; transform: translateY(7px); }
  to   { opacity: 1; transform: translateY(0); }
}
@keyframes sf-fadeOut {
  from { opacity: 1; }
  to   { opacity: 0; }
}
body { animation: sf-fadeIn 0.25s cubic-bezier(0.22,1,0.36,1) both; }
body.sf-exit { animation: sf-fadeOut 0.15s ease both; pointer-events: none; }

/* ── Sticky header ── */
header {
  position: sticky !important;
  top: 0 !important;
  z-index: 200 !important;
  transition: background 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease !important;
}
header.scrolled {
  background: rgba(8,12,20,0.95) !important;
  border-bottom-color: rgba(255,255,255,0.10) !important;
  box-shadow: 0 8px 32px rgba(0,0,0,0.4) !important;
}

/* ── Active nav link indicator ── */
.nav-link.active::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 50%;
  transform: translateX(-50%);
  width: 16px;
  height: 2px;
  border-radius: 2px;
  background: currentColor;
  opacity: 0.6;
}
.nav-link { position: relative; }

/* ── Hamburger button — hidden on desktop ── */
.nav-hamburger {
  display: none;
  flex-direction: column; gap: 5px;
  background: none; border: none;
  cursor: pointer; padding: 8px; margin: -8px;
  z-index: 500;
}
.nav-hamburger span {
  display: block; width: 22px; height: 2px;
  background: rgba(255,255,255,0.75);
  border-radius: 2px; transition: all 0.22s ease;
}
.nav-hamburger.open span:nth-child(1) { transform: translateY(7px) rotate(45deg); background: #fff; }
.nav-hamburger.open span:nth-child(2) { opacity: 0; transform: scaleX(0); }
.nav-hamburger.open span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); background: #fff; }

/* ── Mobile slide-down menu ── */
.nav-mobile-menu {
  position: fixed;
  top: 60px; left: 0; right: 0;
  background: rgba(8,12,20,0.98);
  backdrop-filter: blur(24px);
  -webkit-backdrop-filter: blur(24px);
  border-bottom: 1px solid rgba(255,255,255,0.08);
  z-index: 490;
  padding: 8px 0 12px;
  transform: translateY(-6px);
  opacity: 0;
  pointer-events: none;
  transition: transform 0.22s cubic-bezier(0.22,1,0.36,1), opacity 0.18s ease;
}
.nav-mobile-menu.open {
  transform: translateY(0);
  opacity: 1;
  pointer-events: auto;
}
.nav-mobile-menu .nav-link {
  padding: 13px 24px !important;
  border-radius: 0 !important;
  font-size: 0.9rem !important;
  border-bottom: 1px solid rgba(255,255,255,0.05);
  width: 100%;
}
.nav-mobile-menu .nav-link:last-child { border-bottom: none; }

/* ── Responsive breakpoint ── */
@media (max-width: 768px) {
  header { padding: 0 16px !important; height: 58px !important; }
  nav.nav-desktop { display: none !important; }
  .nav-hamburger  { display: flex; }
}
</style>

<header>
  <a class="logo" href="/">
    <div class="logo-icon">SF</div>
    <div class="logo-text">Sin<span>Filtros</span></div>
  </a>
  <nav class="nav-desktop">
    <?php foreach ($navItems as $item): ?>
    <a class="nav-link<?= $currentPage === getCleanPage($item['href']) ? ' active' : '' ?>"
       href="<?= htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8') ?>">
      <i class="fa <?= htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8') ?>"></i> <span><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></span>
    </a>
    <?php endforeach; ?>
  </nav>
  <button class="nav-hamburger" id="nav-hamburger" aria-label="Menú" aria-expanded="false">
    <span></span><span></span><span></span>
  </button>
</header>

<!-- Mobile menu (outside header, fixed) -->
<div class="nav-mobile-menu" id="nav-mobile-menu" role="navigation">
  <?php foreach ($navItems as $item): ?>
    <a class="nav-link<?= $currentPage === getCleanPage($item['href']) ? ' active' : '' ?>"
       href="<?= htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8') ?>">
    <i class="fa <?= htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8') ?>"></i> <?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?>
  </a>
  <?php endforeach; ?>
</div>

<script>
(function() {
  var burger = document.getElementById('nav-hamburger');
  var menu   = document.getElementById('nav-mobile-menu');

  // ── Hamburger toggle ──
  if (burger && menu) {
    burger.addEventListener('click', function(e) {
      e.stopPropagation();
      var isOpen = menu.classList.toggle('open');
      burger.classList.toggle('open', isOpen);
      burger.setAttribute('aria-expanded', isOpen);
      document.body.style.overflow = isOpen ? 'hidden' : '';
    });

    // Close on outside click
    document.addEventListener('click', function(e) {
      if (!burger.contains(e.target) && !menu.contains(e.target)) {
        menu.classList.remove('open');
        burger.classList.remove('open');
        burger.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
      }
    });

    // Close on Escape
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        menu.classList.remove('open');
        burger.classList.remove('open');
        burger.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
      }
    });
  }

  // ── Scroll-based header effect ──
  var header = document.querySelector('header');
  if (header) {
    var onScroll = function() {
      header.classList.toggle('scrolled', window.scrollY > 12);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  // ── Page transition on nav click ──
  function addTransition(a) {
    a.addEventListener('click', function(e) {
      var href = a.getAttribute('href');
      if (!href || href.charAt(0) === '#' || a.target === '_blank') return;
      e.preventDefault();
      document.body.classList.add('sf-exit');
      setTimeout(function() { window.location.href = href; }, 150);
    });
  }

  // Apply to nav links when DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', applyTransitions);
  } else {
    applyTransitions();
  }

  function applyTransitions() {
    document.querySelectorAll('a.nav-link, a.logo, .btn-back').forEach(addTransition);
  }
})();
</script>
