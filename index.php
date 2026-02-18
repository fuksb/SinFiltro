<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SinFiltros — La IA que te lo dice sin filtros</title>
  <meta name="description" content="Las empresas ocultan miles de euros en letra pequeña. Nosotros te los enseñamos en 15 segundos. Gratis. Sin registro." />

  <!-- Open Graph / Social sharing -->
  <meta property="og:type"        content="website" />
  <meta property="og:url"         content="https://sinfiltros.es/" />
  <meta property="og:title"       content="SinFiltros — La IA que te lo dice sin filtros" />
  <meta property="og:description" content="Las empresas ocultan miles de euros en letra pequeña. Nosotros te los enseñamos en 15 segundos. Coches, hipotecas, luz, móvil, seguros e inversiones." />
  <meta property="og:image"       content="https://sinfiltros.es/og-image.png" />
  <meta name="twitter:card"       content="summary_large_image" />
  <meta name="twitter:title"      content="SinFiltros — La IA que te lo dice sin filtros" />
  <meta name="twitter:description" content="Las empresas ocultan miles de euros en letra pequeña. Nosotros te los enseñamos en 15 segundos." />
  <meta name="twitter:image"      content="https://sinfiltros.es/og-image.png" />

  <!-- Favicon SVG inline -->
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 40 40'><defs><linearGradient id='g' x1='0' y1='0' x2='1' y2='1'><stop offset='0' stop-color='%238B5CF6'/><stop offset='1' stop-color='%23F97316'/></linearGradient></defs><rect width='40' height='40' rx='10' fill='url(%23g)'/><text x='50%' y='55%' dominant-baseline='middle' text-anchor='middle' font-family='system-ui,sans-serif' font-weight='900' font-size='16' fill='white'>SF</text></svg>" />

  <!-- Schema.org structured data -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "WebApplication",
    "name": "SinFiltros",
    "url": "https://sinfiltros.es",
    "description": "Herramientas de IA brutalmente honestas para analizar coches, hipotecas, energía, telefonía, seguros e inversiones. Sin letra pequeña. Sin rodeos.",
    "applicationCategory": "FinanceApplication",
    "offers": { "@type": "Offer", "price": "0", "priceCurrency": "EUR" },
    "operatingSystem": "Any"
  }
  </script>

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,400&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --purple:   #8B5CF6;
      --purple-d: #6D28D9;
      --orange:   #F97316;
      --cyan:     #06B6D4;
      --green:    #10B981;
      --red:      #EF4444;
      --navy:     #080C14;
      --dark:     #0F172A;
      --glass:    rgba(255,255,255,0.04);
      --glass-b:  rgba(255,255,255,0.08);
      --text:     #E2E8F0;
      --muted:    rgba(255,255,255,0.45);
    }

    html { scroll-behavior: smooth; }
    body { font-family: 'Inter', sans-serif; background: var(--navy); color: var(--text); min-height: 100vh; overflow-x: hidden; }

    /* ── ANIMATED BACKGROUND ── */
    .bg-canvas {
      position: fixed; inset: 0; z-index: 0; pointer-events: none;
      overflow: hidden;
    }
    .orb {
      position: absolute; border-radius: 50%; filter: blur(90px); opacity: 0.18;
    }
    .orb-1 { width: 500px; height: 500px; background: var(--purple); top: -100px; right: -80px; animation: floatA 9s ease-in-out infinite; }
    .orb-2 { width: 350px; height: 350px; background: var(--orange); bottom: 10%; left: -60px; animation: floatB 12s ease-in-out infinite; }
    .orb-3 { width: 280px; height: 280px; background: var(--cyan); top: 40%; right: 20%; animation: floatC 7s ease-in-out infinite; }

    @keyframes floatA { 0%,100%{transform:translate(0,0);} 50%{transform:translate(-40px,30px);} }
    @keyframes floatB { 0%,100%{transform:translate(0,0);} 50%{transform:translate(30px,-40px);} }
    @keyframes floatC { 0%,100%{transform:translate(0,0);} 50%{transform:translate(-20px,25px);} }

    /* ── HEADER ── */
    header {
      position: relative; z-index: 100;
      padding: 16px 32px;
      display: flex; align-items: center; justify-content: space-between;
      border-bottom: 1px solid var(--glass-b);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      background: rgba(8,12,20,0.8);
    }

    .logo {
      display: flex; align-items: center; gap: 12px;
      text-decoration: none; color: white;
    }
    .logo-icon {
      width: 40px; height: 40px;
      background: linear-gradient(135deg, var(--purple), var(--orange));
      border-radius: 12px;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.1rem; font-weight: 900;
      box-shadow: 0 0 20px rgba(139,92,246,0.4);
    }
    .logo-text { font-size: 1.3rem; font-weight: 900; letter-spacing: -0.03em; }
    .logo-text span {
      background: linear-gradient(90deg, var(--purple), var(--orange));
      -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
    }

    nav { display: flex; gap: 4px; }
    .nav-link {
      padding: 6px 12px; border-radius: 8px;
      font-size: 0.78rem; font-weight: 600;
      text-decoration: none; color: rgba(255,255,255,0.5);
      transition: color 0.15s, background 0.15s;
      display: flex; align-items: center; gap: 6px;
      white-space: nowrap;
    }
    .nav-link:hover { background: rgba(255,255,255,0.08); color: white; }
    .nav-link.active { background: rgba(139,92,246,0.18); color: #C4B5FD; }
    .nav-link i { font-size: 0.82rem; }

    /* ── HERO ── */
    .hero {
      position: relative; z-index: 10;
      text-align: center;
      padding: 80px 24px 100px;
    }

    /* Live counter strip */
    .live-strip {
      display: inline-flex; align-items: center; gap: 10px;
      background: rgba(16,185,129,0.08);
      border: 1px solid rgba(16,185,129,0.2);
      border-radius: 30px;
      padding: 8px 18px;
      margin-bottom: 20px;
      font-size: 0.78rem; font-weight: 600;
      color: #34D399;
    }
    .live-dot {
      width: 7px; height: 7px; background: #34D399; border-radius: 50%;
      animation: livePulse 1.8s ease-in-out infinite;
      flex-shrink: 0;
    }
    @keyframes livePulse { 0%,100%{opacity:1;box-shadow:0 0 0 0 rgba(52,211,153,0.4);} 50%{opacity:0.7;box-shadow:0 0 0 5px rgba(52,211,153,0);} }

    .hero-tag {
      display: inline-flex; align-items: center; gap: 8px;
      background: rgba(139,92,246,0.12);
      border: 1px solid rgba(139,92,246,0.3);
      color: #A78BFA;
      font-size: 0.75rem; font-weight: 700;
      padding: 8px 18px; border-radius: 30px;
      margin-bottom: 36px;
      letter-spacing: 0.08em; text-transform: uppercase;
    }
    .hero-tag .pulse {
      width: 6px; height: 6px; background: #A78BFA; border-radius: 50%;
      animation: pulse 2s ease-in-out infinite;
    }
    @keyframes pulse { 0%,100%{opacity:1;transform:scale(1);} 50%{opacity:0.5;transform:scale(1.4);} }

    .hero h1 {
      font-size: clamp(2.8rem, 7vw, 5.5rem);
      font-weight: 900; line-height: 1.02;
      letter-spacing: -0.05em;
      margin-bottom: 28px;
    }
    .hero h1 .line1 { display: block; color: white; }
    .hero h1 .line2 {
      display: block;
      background: linear-gradient(90deg, var(--orange) 0%, #FBBF24 40%, var(--purple) 100%);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
      background-size: 200% auto;
      animation: shimmer 4s linear infinite;
    }
    @keyframes shimmer { 0%{background-position:0% center;} 100%{background-position:200% center;} }

    .hero p {
      font-size: clamp(1.05rem, 2.5vw, 1.25rem);
      color: rgba(255,255,255,0.5); max-width: 560px; margin: 0 auto 16px; line-height: 1.75;
    }

    .hero-proof {
      display: flex; align-items: center; justify-content: center; gap: 24px;
      flex-wrap: wrap;
      margin-top: 20px;
      font-size: 0.8rem; color: rgba(255,255,255,0.35); font-weight: 600;
    }
    .hero-proof span { display: flex; align-items: center; gap: 6px; }
    .hero-proof i { color: rgba(255,255,255,0.25); font-size: 0.75rem; }

    .hero-cta-hint {
      font-size: 0.85rem; color: rgba(255,255,255,0.3); margin-top: 24px;
      font-weight: 500; letter-spacing: 0.02em;
      animation: bounceDown 2s ease-in-out infinite;
    }
    @keyframes bounceDown { 0%,100%{transform:translateY(0);} 50%{transform:translateY(6px);} }

    /* ── TOOLS SECTION ── */
    .tools-section {
      position: relative; z-index: 10;
      max-width: 1080px; margin: 0 auto 80px; padding: 0 24px;
    }

    .section-label {
      text-align: center;
      font-size: 0.72rem; font-weight: 700;
      letter-spacing: 0.15em; text-transform: uppercase;
      color: rgba(255,255,255,0.3); margin-bottom: 32px;
    }

    .tools-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 20px;
    }

    .tool-card {
      position: relative;
      background: var(--glass);
      border: 1px solid rgba(255,255,255,0.06);
      border-radius: 24px; padding: 32px;
      text-decoration: none; color: inherit;
      transition: transform 0.3s cubic-bezier(0.22,1,0.36,1), box-shadow 0.3s, border-color 0.3s, background 0.3s;
      display: flex; flex-direction: column; gap: 20px;
      overflow: hidden;
      cursor: pointer;
    }
    /* Top accent line (hidden by default, appears on hover) */
    .tool-card::before {
      content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px;
      background: var(--card-accent, var(--purple));
      opacity: 0; transition: opacity 0.3s;
    }
    /* Inner glow */
    .tool-card::after {
      content: ''; position: absolute; inset: 0; border-radius: 24px;
      background: radial-gradient(ellipse at 50% -20%, var(--card-glow, rgba(139,92,246,0.1)), transparent 65%);
      opacity: 0; transition: opacity 0.3s;
      pointer-events: none;
    }
    .tool-card:hover::before { opacity: 1; }
    .tool-card:hover::after  { opacity: 1; }
    .tool-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 28px 80px rgba(0,0,0,0.5), 0 0 0 1px rgba(255,255,255,0.1);
      border-color: rgba(255,255,255,0.12);
      background: rgba(255,255,255,0.05);
    }
    .tool-card.coming-soon { opacity: 0.38; cursor: default; pointer-events: none; }
    .tool-card.coming-soon:hover { transform: none; box-shadow: none; }

    .card-top { display: flex; align-items: flex-start; justify-content: space-between; }

    .tool-icon {
      width: 56px; height: 56px; border-radius: 16px;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.5rem;
      box-shadow: 0 4px 20px var(--icon-shadow, rgba(139,92,246,0.3));
      transition: transform 0.25s, box-shadow 0.25s;
    }
    .tool-card:hover .tool-icon {
      transform: scale(1.08);
      box-shadow: 0 8px 32px var(--icon-shadow, rgba(139,92,246,0.5));
    }

    .tool-badge {
      font-size: 0.65rem; font-weight: 800;
      padding: 4px 10px; border-radius: 20px;
      text-transform: uppercase; letter-spacing: 0.08em;
    }
    .badge-live { background: rgba(16,185,129,0.15); color: #34D399; border: 1px solid rgba(16,185,129,0.3); }
    .badge-new  {
      background: rgba(139,92,246,0.15); color: #A78BFA; border: 1px solid rgba(139,92,246,0.3);
      animation: badgePulse 2.5s ease-in-out infinite;
    }
    .badge-soon { background: rgba(255,255,255,0.05); color: rgba(255,255,255,0.3); border: 1px solid rgba(255,255,255,0.08); }
    @keyframes badgePulse { 0%,100%{box-shadow:none;} 50%{box-shadow:0 0 10px rgba(139,92,246,0.3);} }

    .tool-content h3 {
      font-size: 1.15rem; font-weight: 800; color: white;
      margin-bottom: 8px; letter-spacing: -0.02em;
    }
    .tool-content p { font-size: 0.875rem; color: var(--muted); line-height: 1.6; }

    .tool-footer {
      display: flex; align-items: center; justify-content: space-between;
      padding-top: 4px; border-top: 1px solid var(--glass-b);
    }
    .tool-cta {
      font-size: 0.82rem; font-weight: 700; color: var(--card-accent, var(--purple));
      display: flex; align-items: center; gap: 6px;
      transition: gap 0.2s, opacity 0.2s;
    }
    .tool-card:hover .tool-cta { gap: 12px; opacity: 1; }
    .tool-cta i { transition: transform 0.2s; }
    .tool-card:hover .tool-cta i { transform: translateX(3px); }
    .tool-stats { font-size: 0.72rem; color: rgba(255,255,255,0.25); font-weight: 500; }

    /* ── MANIFESTO ── */
    .manifesto {
      position: relative; z-index: 10;
      max-width: 800px; margin: 0 auto 60px; padding: 0 24px;
    }

    .manifesto-inner {
      background: linear-gradient(135deg, rgba(139,92,246,0.05), rgba(249,115,22,0.03), rgba(255,255,255,0.03));
      border: 1px solid rgba(139,92,246,0.15);
      border-radius: 28px; padding: 48px;
      text-align: center;
      position: relative; overflow: hidden;
    }
    .manifesto-inner::before {
      content: ''; position: absolute;
      top: -40px; left: 50%; transform: translateX(-50%);
      width: 300px; height: 80px;
      background: radial-gradient(ellipse, rgba(139,92,246,0.15), transparent 70%);
      pointer-events: none;
    }

    .manifesto h2 {
      font-size: clamp(1.6rem, 4vw, 2.4rem);
      font-weight: 900; color: white;
      letter-spacing: -0.03em; margin-bottom: 20px;
    }
    .manifesto h2 em {
      font-style: normal;
      background: linear-gradient(90deg, var(--orange), var(--purple));
      -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
    }

    .manifesto p { font-size: 1rem; color: var(--muted); line-height: 1.75; margin-bottom: 12px; }
    .manifesto p:last-of-type { margin-bottom: 36px; }

    .stats-row {
      display: flex; justify-content: center; gap: 0; flex-wrap: wrap;
      border: 1px solid var(--glass-b); border-radius: 16px; overflow: hidden;
      margin-bottom: 32px;
    }
    .stat {
      flex: 1; min-width: 120px;
      padding: 20px 16px; text-align: center;
      border-right: 1px solid var(--glass-b);
    }
    .stat:last-child { border-right: none; }
    .stat strong { display: block; font-size: 2rem; font-weight: 900; color: white; margin-bottom: 4px; letter-spacing: -0.03em; }
    .stat span   { font-size: 0.68rem; color: rgba(255,255,255,0.35); text-transform: uppercase; letter-spacing: 0.1em; font-weight: 700; }

    /* ── EMAIL CAPTURE ── */
    .email-capture {
      position: relative; z-index: 10;
      max-width: 600px; margin: 0 auto 80px; padding: 0 24px;
    }
    .email-capture-inner {
      background: linear-gradient(135deg, rgba(139,92,246,0.1), rgba(249,115,22,0.08));
      border: 1px solid rgba(139,92,246,0.25);
      border-radius: 24px; padding: 36px; text-align: center;
    }
    .email-capture h3 {
      font-size: 1.1rem; font-weight: 800; color: white; margin-bottom: 8px; letter-spacing: -0.02em;
    }
    .email-capture p { font-size: 0.875rem; color: var(--muted); margin-bottom: 20px; line-height: 1.6; }
    .email-form { display: flex; gap: 10px; flex-wrap: wrap; }
    .email-input {
      flex: 1; min-width: 200px;
      padding: 12px 16px; border-radius: 12px;
      border: 1.5px solid rgba(255,255,255,0.1);
      background: rgba(255,255,255,0.05);
      color: white; font-family: 'Inter', sans-serif; font-size: 0.9rem;
      outline: none; transition: border-color 0.2s;
    }
    .email-input::placeholder { color: rgba(255,255,255,0.3); }
    .email-input:focus { border-color: var(--purple); }
    .email-btn {
      padding: 12px 22px; border-radius: 12px;
      background: linear-gradient(135deg, var(--purple-d, #6D28D9), var(--purple));
      color: white; border: none; font-family: 'Inter', sans-serif;
      font-size: 0.9rem; font-weight: 700; cursor: pointer;
      transition: opacity 0.15s, transform 0.15s;
      white-space: nowrap;
    }
    .email-btn:hover { opacity: 0.88; transform: translateY(-1px); }
    .email-ok {
      display: none; font-size: 0.9rem; color: #34D399; font-weight: 600;
      padding-top: 8px;
    }

    /* ── FOOTER ── */
    footer {
      position: relative; z-index: 10;
      border-top: 1px solid var(--glass-b);
      background: rgba(8,12,20,0.8);
      padding: 32px 40px;
      display: flex; align-items: center; justify-content: space-between;
      flex-wrap: wrap; gap: 12px;
    }
    .footer-brand { font-size: 0.9rem; font-weight: 800; color: white; letter-spacing: -0.02em; }
    .footer-brand span {
      background: linear-gradient(90deg, var(--purple), var(--orange));
      -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
    }
    .footer-note { font-size: 0.75rem; color: rgba(255,255,255,0.25); max-width: 400px; line-height: 1.5; }
    .footer-links { display: flex; gap: 16px; }
    .footer-links a { font-size: 0.78rem; color: rgba(255,255,255,0.3); text-decoration: none; transition: color 0.15s; }
    .footer-links a:hover { color: var(--purple); }

    /* ── RESPONSIVE ── */
    @media (max-width: 768px) {
      header { padding: 16px 20px; }
      .hero { padding: 60px 20px 80px; }
      .tools-grid { grid-template-columns: 1fr; }
      .manifesto-inner { padding: 32px 24px; }
      footer { flex-direction: column; gap: 16px; padding: 24px 20px; }
      .email-form { flex-direction: column; }
      .hero-proof { gap: 14px; }
    }
  </style>
</head>
<body>

<!-- BACKGROUND ORBS -->
<div class="bg-canvas">
  <div class="orb orb-1"></div>
  <div class="orb orb-2"></div>
  <div class="orb orb-3"></div>
</div>

<!-- HEADER -->
<?php include '_header.php'; ?>

<!-- HERO -->
<div class="hero">
  <?php
    $counter = (int) @file_get_contents(__DIR__ . '/data/counter.txt');
    if ($counter > 0):
      $formatted = number_format($counter, 0, ',', '.');
  ?>
  <div class="live-strip">
    <div class="live-dot"></div>
    <span><?= $formatted ?> análisis realizados — y contando</span>
  </div>
  <?php endif; ?>

  <div class="hero-tag">
    <div class="pulse"></div>
    IA Brutalmente Honesta · Gratis · Sin registro
  </div>
  <h1>
    <span class="line1">Las empresas te ocultan</span>
    <span class="line2">miles de euros</span>
  </h1>
  <p>Nosotros te los enseñamos en 15 segundos. Coches, hipotecas, luz, móvil, seguros e inversiones — analizados sin piedad.</p>

  <div class="hero-proof">
    <span><i class="fa fa-lock"></i> Sin registro</span>
    <span><i class="fa fa-euro-sign"></i> 100% gratis</span>
    <span><i class="fa fa-robot"></i> Claude AI</span>
    <span><i class="fa fa-bolt"></i> Resultado en &lt;15 seg</span>
  </div>

  <div class="hero-cta-hint">↓ Elige tu herramienta</div>
</div>

<!-- TOOLS -->
<div class="tools-section">
  <div class="section-label">Herramientas disponibles</div>
  <div class="tools-grid">

    <!-- COCHES -->
    <a class="tool-card" href="coche.php" style="--card-accent:#8B5CF6;--card-glow:rgba(139,92,246,0.08);">
      <div class="card-top">
        <div class="tool-icon" style="background:linear-gradient(135deg,rgba(139,92,246,0.25),rgba(139,92,246,0.05));color:#A78BFA;--icon-shadow:rgba(139,92,246,0.4);">
          <i class="fa fa-car-side"></i>
        </div>
        <span class="tool-badge badge-live">Activo</span>
      </div>
      <div class="tool-content">
        <h3>Descifrador de<br/>Coches</h3>
        <p>Pega el enlace del anuncio. Detectamos fraudes en segunda mano, calculamos el TAE real de la financiación y te decimos si te timan.</p>
      </div>
      <div class="tool-footer">
        <div class="tool-cta" style="color:#A78BFA;">Descifrar oferta <i class="fa fa-arrow-right"></i></div>
        <div class="tool-stats">Gratis · Sin registro</div>
      </div>
    </a>

    <!-- HIPOTECAS -->
    <a class="tool-card" href="hipoteca.php" style="--card-accent:#F97316;--card-glow:rgba(249,115,22,0.08);">
      <div class="card-top">
        <div class="tool-icon" style="background:linear-gradient(135deg,rgba(249,115,22,0.25),rgba(249,115,22,0.05));color:#FB923C;--icon-shadow:rgba(249,115,22,0.4);">
          <i class="fa fa-percent"></i>
        </div>
        <span class="tool-badge badge-live">Activo</span>
      </div>
      <div class="tool-content">
        <h3>Analizador<br/>de Hipotecas</h3>
        <p>¿Buena hipoteca o humo? Calculamos el coste real a 30 años, destripamos las vinculaciones y te decimos cuánto pagas de más.</p>
      </div>
      <div class="tool-footer">
        <div class="tool-cta" style="color:#FB923C;">Analizar hipoteca <i class="fa fa-arrow-right"></i></div>
        <div class="tool-stats">Gratis · Sin registro</div>
      </div>
    </a>

    <!-- LUZ & GAS -->
    <a class="tool-card" href="luz.php" style="--card-accent:#FBBF24;--card-glow:rgba(251,191,36,0.08);">
      <div class="card-top">
        <div class="tool-icon" style="background:linear-gradient(135deg,rgba(251,191,36,0.25),rgba(251,191,36,0.05));color:#FCD34D;--icon-shadow:rgba(251,191,36,0.4);">
          <i class="fa fa-bolt"></i>
        </div>
        <span class="tool-badge badge-live">Activo</span>
      </div>
      <div class="tool-content">
        <h3>Analizador de<br/>Luz &amp; Gas</h3>
        <p>¿PVPC o tarifa fija? ¿Te merece la pena tu compañía actual? Detectamos cláusulas abusivas y permanencias ocultas.</p>
      </div>
      <div class="tool-footer">
        <div class="tool-cta" style="color:#FCD34D;">Analizar contrato <i class="fa fa-arrow-right"></i></div>
        <div class="tool-stats">Gratis · Sin registro</div>
      </div>
    </a>

    <!-- TELEFONÍA -->
    <a class="tool-card" href="telco.php" style="--card-accent:#06B6D4;--card-glow:rgba(6,182,212,0.08);">
      <div class="card-top">
        <div class="tool-icon" style="background:linear-gradient(135deg,rgba(6,182,212,0.25),rgba(6,182,212,0.05));color:#22D3EE;--icon-shadow:rgba(6,182,212,0.4);">
          <i class="fa fa-mobile-screen"></i>
        </div>
        <span class="tool-badge badge-live">Activo</span>
      </div>
      <div class="tool-content">
        <h3>Descifrador de<br/>Telefonía &amp; Internet</h3>
        <p>Ofertas de móvil e internet analizadas sin piedad: coste real, permanencias, velocidad real vs anunciada y penalizaciones.</p>
      </div>
      <div class="tool-footer">
        <div class="tool-cta" style="color:#22D3EE;">Analizar oferta <i class="fa fa-arrow-right"></i></div>
        <div class="tool-stats">Gratis · Sin registro</div>
      </div>
    </a>

    <!-- SEGUROS -->
    <a class="tool-card" href="seguros.php" style="--card-accent:#10B981;--card-glow:rgba(16,185,129,0.08);">
      <div class="card-top">
        <div class="tool-icon" style="background:linear-gradient(135deg,rgba(16,185,129,0.25),rgba(16,185,129,0.05));color:#34D399;--icon-shadow:rgba(16,185,129,0.4);">
          <i class="fa fa-umbrella"></i>
        </div>
        <span class="tool-badge badge-live">Activo</span>
      </div>
      <div class="tool-content">
        <h3>Analizador<br/>de Seguros</h3>
        <p>Pega tu póliza y descubrimos qué cubre de verdad, qué exclusiones te esconden y si estás pagando de más por coberturas inútiles.</p>
      </div>
      <div class="tool-footer">
        <div class="tool-cta" style="color:#34D399;">Analizar póliza <i class="fa fa-arrow-right"></i></div>
        <div class="tool-stats">Gratis · Sin registro</div>
      </div>
    </a>

    <!-- INVERSIONES -->
    <a class="tool-card" href="inversiones.php" style="--card-accent:#EC4899;--card-glow:rgba(236,72,153,0.08);">
      <div class="card-top">
        <div class="tool-icon" style="background:linear-gradient(135deg,rgba(236,72,153,0.25),rgba(236,72,153,0.05));color:#F472B6;--icon-shadow:rgba(236,72,153,0.4);">
          <i class="fa fa-chart-pie"></i>
        </div>
        <span class="tool-badge badge-live">Activo</span>
      </div>
      <div class="tool-content">
        <h3>Detector de Comisiones<br/>en Inversiones</h3>
        <p>Fondos, planes de pensiones, depósitos. Calculamos cuánto te comen las comisiones a 10, 20 y 30 años y qué alternativas baratas existen.</p>
      </div>
      <div class="tool-footer">
        <div class="tool-cta" style="color:#F472B6;">Calcular impacto <i class="fa fa-arrow-right"></i></div>
        <div class="tool-stats">Gratis · Sin registro</div>
      </div>
    </a>

  </div>
</div>

<!-- MANIFESTO -->
<div class="manifesto">
  <div class="manifesto-inner">
    <h2>¿Por qué <em>SinFiltros</em>?</h2>
    <p>En España, las empresas llenan sus contratos de letra pequeña, cláusulas confusas y promociones diseñadas para que no entiendas lo que firmas. Un coche "desde 199€/mes" puede costarte 8.000€ más. Una hipoteca con "TIN 3,40%" puede salirte a un TAE real del 5% con los seguros vinculados.</p>
    <p>SinFiltros usa inteligencia artificial para hacer lo que nadie más hace: explicarte en lenguaje brutalmente claro exactamente lo que te cobran, lo que te ocultan y lo que deberías saber antes de firmar.</p>
    <div class="stats-row">
      <div class="stat">
        <strong>0€</strong>
        <span>Siempre gratis</span>
      </div>
      <div class="stat">
        <strong>&lt;15s</strong>
        <span>Resultado inmediato</span>
      </div>
      <div class="stat">
        <strong>IA</strong>
        <span>Claude AI</span>
      </div>
      <div class="stat">
        <strong>6</strong>
        <span>Sectores</span>
      </div>
    </div>
    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
      <a href="coche.php" style="display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:14px;background:linear-gradient(135deg,#6D28D9,#8B5CF6);color:white;font-weight:800;font-size:0.95rem;text-decoration:none;transition:transform 0.15s,box-shadow 0.15s;box-shadow:0 6px 24px rgba(139,92,246,0.4);" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform=''">
        <i class="fa fa-car-side"></i> Analizar un coche
      </a>
      <a href="hipoteca.php" style="display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:14px;background:linear-gradient(135deg,#C2410C,#F97316);color:white;font-weight:800;font-size:0.95rem;text-decoration:none;transition:transform 0.15s,box-shadow 0.15s;box-shadow:0 6px 24px rgba(249,115,22,0.4);" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform=''">
        <i class="fa fa-percent"></i> Analizar una hipoteca
      </a>
    </div>
  </div>
</div>

<!-- EMAIL CAPTURE -->
<div class="email-capture" id="email-section">
  <div class="email-capture-inner">
    <h3><i class="fa fa-bell" style="color:#A78BFA;margin-right:8px;"></i>Avísame cuando lleguen nuevas herramientas</h3>
    <p>Luz & gas, telefonía, seguros e inversiones — en cuanto estén listos te avisamos. Sin spam. Solo una notificación.</p>
    <div class="email-form" id="email-form">
      <input type="email" class="email-input" id="email-input" placeholder="tu@email.com" autocomplete="email" />
      <button class="email-btn" onclick="submitEmail()"><i class="fa fa-paper-plane"></i> Avisarme</button>
    </div>
    <div class="email-ok" id="email-ok"><i class="fa fa-circle-check"></i> ¡Apuntado! Te avisamos cuando esté listo.</div>
  </div>
</div>

<!-- FOOTER -->
<?php include '_footer.php'; ?>

<script>
  // Email capture
  function submitEmail() {
    const email = document.getElementById('email-input').value.trim();
    if (!email || !email.includes('@')) {
      document.getElementById('email-input').style.borderColor = '#EF4444';
      return;
    }
    // Store in localStorage to avoid asking again
    localStorage.setItem('sf_email_captured', '1');
    document.getElementById('email-form').style.display = 'none';
    document.getElementById('email-ok').style.display = 'block';
  }

  // Hide email section if already captured
  if (localStorage.getItem('sf_email_captured')) {
    const sec = document.getElementById('email-section');
    if (sec) sec.style.display = 'none';
  }

  // Allow Enter key in email input
  document.getElementById('email-input').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') submitEmail();
  });
</script>

</body>
</html>
