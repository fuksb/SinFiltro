<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Descifrador de Coches — SinFiltros</title>
  <meta name="description" content="Pega la oferta del concesionario y descubre lo que realmente pagas: TAE real, coste total, trampas ocultas. IA brutalmente honesta." />
  <meta name="robots" content="index, follow" />
  <link rel="canonical" href="https://sinfiltros.es/coche.php" />
  
  <!-- Open Graph / Social sharing -->
  <meta property="og:type"         content="website" />
  <meta property="og:url"          content="https://sinfiltros.es/coche.php" />
  <meta property="og:title"        content="¿Te están timando con el coche? — SinFiltros" />
  <meta property="og:description"  content="Pega el enlace del anuncio y descubre el TAE real, el coste total y las trampas ocultas. Gratis. En 15 segundos." />
  <meta property="og:image"        content="https://sinfiltros.es/og-image.png" />
  <meta name="twitter:card"        content="summary_large_image" />
  <meta name="twitter:title"       content="¿Te están timando con el coche? — SinFiltros" />
  <meta name="twitter:description" content="Pega el enlace del anuncio y descubre el TAE real, el coste total y las trampas ocultas. Gratis. En 15 segundos." />
  <meta name="twitter:image"       content="https://sinfiltros.es/og-image.png" />

  <!-- Favicon -->
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 40 40'><defs><linearGradient id='g' x1='0' y1='0' x2='1' y2='1'><stop offset='0' stop-color='%238B5CF6'/><stop offset='1' stop-color='%23F97316'/></linearGradient></defs><rect width='40' height='40' rx='10' fill='url(%23g)'/><text x='50%' y='55%' dominant-baseline='middle' text-anchor='middle' font-family='system-ui,sans-serif' font-weight='900' font-size='16' fill='white'>SF</text></svg>" />
  <!-- Schema.org structured data -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "SoftwareApplication",
    "name": "Descifrador de Coches — SinFiltros",
    "url": "https://sinfiltros.es/coche.php",
    "description": "Analiza ofertas de coches con IA. Descubre el TAE real, coste total y trampas ocultas en 15 segundos. Gratis.",
    "applicationCategory": "FinanceApplication",
    "offers": { "@type": "Offer", "price": "0", "priceCurrency": "EUR" },
    "operatingSystem": "Any"
  }
  </script>

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="assets/sf.css">
  <style>
    :root {
      /* Defaults in sf.css match coche (purple) — overrides not needed */
      --result-bg1: #F5F3FF;
      --result-bg2: #FDF4FF;
    }
    /* ── TAE comparison ── */
    .tae-compare {
      display: flex; align-items: center; gap: 8px; margin-top: 16px;
      padding: 12px 16px; background: #FFF7ED; border: 1px solid #FED7AA;
      border-radius: 10px;
    }
    .tae-compare .tae-item { flex: 1; text-align: center; }
    .tae-compare .tae-val { font-size: 1.1rem; font-weight: 900; display: block; }
    .tae-compare .tae-lbl { font-size: 0.68rem; font-weight: 600; color: var(--mid); text-transform: uppercase; letter-spacing: 0.05em; }
    .tae-compare .tae-vs  { font-size: 0.8rem; font-weight: 800; color: var(--light); }
    .tae-compare .tae-item.real .tae-val { color: var(--red); }
    .tae-compare .tae-item.anun .tae-val { color: var(--orange); }

    /* ── Fraude alert banner ── */
    .fraude-banner {
      margin: 0 32px; padding: 20px 22px;
      background: linear-gradient(135deg, #450A0A, #7F1D1D);
      border: 1px solid #EF4444; border-radius: 16px 16px 0 0; margin-bottom: -1px;
    }
    .fraude-banner-hdr { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
    .fraude-banner-hdr .fraude-icon {
      width: 36px; height: 36px; background: rgba(239,68,68,0.3);
      border-radius: 10px; display: flex; align-items: center; justify-content: center;
      font-size: 1rem; color: #FCA5A5; flex-shrink: 0;
    }
    .fraude-banner-hdr .fraude-title { font-size: 0.9rem; font-weight: 800; color: #FECACA; letter-spacing: -0.01em; }
    .fraude-banner-hdr .fraude-score {
      margin-left: auto; background: #EF4444; color: white;
      padding: 4px 10px; border-radius: 20px; font-size: 0.72rem; font-weight: 800;
    }
    .fraude-item {
      display: flex; gap: 10px; align-items: flex-start;
      padding: 8px 0; border-top: 1px solid rgba(239,68,68,0.2);
      font-size: 0.875rem; color: #FCA5A5; line-height: 1.5;
    }
    .fraude-item:first-child { border-top: none; }
    .fraude-item i { color: #F87171; margin-top: 2px; font-size: 0.85rem; flex-shrink: 0; }
    /* ── Costes adicionales ── */
    .costes-block {
      margin: 0 32px; padding: 22px 24px;
      background: linear-gradient(135deg, #FFF7ED, #FFFBEB);
      border: 1px solid #FED7AA; border-radius: 14px;
    }
    .costes-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px; margin-bottom: 12px; }
    .coste-item { background: white; border: 1px solid #FED7AA; border-radius: 10px; padding: 12px; text-align: center; }
    .coste-val  { font-size: 1.05rem; font-weight: 900; color: #C2410C; display: block; margin-bottom: 3px; }
    .coste-lbl  { font-size: 0.68rem; font-weight: 600; color: #92400E; text-transform: uppercase; letter-spacing: 0.05em; }
    .coste-total-item {
      grid-column: 1 / -1; background: linear-gradient(135deg, #92400E, #B45309);
      border-color: transparent; border-radius: 10px; padding: 14px;
      display: flex; align-items: center; justify-content: space-between;
    }
    .coste-total-item .coste-val { color: white; font-size: 1.2rem; }
    .coste-total-item .coste-lbl { color: rgba(255,255,255,0.7); }
    .coste-nota {
      font-size: 0.78rem; color: #92400E; line-height: 1.5;
      padding: 10px 12px; background: rgba(251,191,36,0.15); border-radius: 8px;
    }

    @media (max-width: 600px) {
      .fraude-banner { margin: 0 20px; }
      .costes-block { margin: 0 20px; }
    }

    /* ── Preguntas v2 ── */
    .pregunta-item {
      background: rgba(139,92,246,0.07); border: 1px solid rgba(139,92,246,0.2);
      border-radius: 14px; overflow: hidden; margin-bottom: 10px;
    }
    .pregunta-header {
      display: flex; align-items: flex-start; gap: 14px;
      padding: 16px 18px 12px;
    }
    .pregunta-num {
      width: 28px; height: 28px; border-radius: 50%; border: 2px solid #7C3AED;
      color: #7C3AED; font-size: 0.8rem; font-weight: 800;
      display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 1px;
    }
    .pregunta-text {
      font-size: 0.95rem; font-weight: 700; color: #E2E8F0; line-height: 1.45;
    }
    .pregunta-why {
      padding: 0 18px 10px 60px;
      font-size: 0.78rem; color: rgba(255,255,255,0.5); line-height: 1.5; font-style: italic;
    }
    .pregunta-answers {
      display: grid; grid-template-columns: 1fr 1fr; border-top: 1px solid rgba(255,255,255,0.07);
    }
    .pregunta-answer {
      padding: 12px 16px; display: flex; gap: 10px; align-items: flex-start;
    }
    .pregunta-answer.good { background: rgba(16,185,129,0.08); border-right: 1px solid rgba(255,255,255,0.06); }
    .pregunta-answer.bad  { background: rgba(239,68,68,0.08); }
    .pregunta-answer i    { font-size: 0.85rem; margin-top: 2px; flex-shrink: 0; }
    .pregunta-answer.good i { color: #34D399; }
    .pregunta-answer.bad  i { color: #F87171; }
    .pregunta-answer-body strong { display: block; font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 3px; }
    .pregunta-answer.good strong { color: #34D399; }
    .pregunta-answer.bad  strong { color: #F87171; }
    .pregunta-answer-body span { font-size: 0.8rem; color: rgba(255,255,255,0.72); line-height: 1.45; }
    @media (max-width: 600px) {
      .pregunta-answers { grid-template-columns: 1fr; }
      .pregunta-answer.good { border-right: none; border-bottom: 1px solid rgba(255,255,255,0.06); }
      .pregunta-why { padding-left: 18px; }
    }

    /* ── VIN card ── */
    .vin-card {
      background: linear-gradient(135deg, #0F172A, #1E293B);
      border: 1px solid rgba(139,92,246,0.35); border-radius: 14px;
      overflow: hidden;
    }
    .vin-card-header {
      display: flex; align-items: flex-start; gap: 14px;
      padding: 18px 22px 14px; border-bottom: 1px solid rgba(255,255,255,0.07);
    }
    .vin-card-header > i {
      font-size: 1.4rem; color: #8B5CF6; margin-top: 3px; flex-shrink: 0;
    }
    .vin-card-title { font-size: 0.78rem; font-weight: 700; color: rgba(255,255,255,0.55); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 4px; }
    .vin-card-num   { font-size: 1.05rem; font-weight: 800; color: white; letter-spacing: 0.08em; font-family: 'Courier New', monospace; }
    .vin-grid {
      display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
      gap: 1px; background: rgba(255,255,255,0.07); border-bottom: 1px solid rgba(255,255,255,0.07);
    }
    .vin-cell { background: #0F172A; padding: 14px 18px; }
    .vin-cell-val { font-size: 0.9rem; font-weight: 700; color: #E2E8F0; margin-bottom: 3px; }
    .vin-cell-lbl { font-size: 0.66rem; font-weight: 600; color: rgba(255,255,255,0.4); text-transform: uppercase; letter-spacing: 0.05em; }
    .vin-check-row { display: flex; align-items: center; gap: 8px; padding: 10px 22px; font-size: 0.78rem; font-weight: 600; }
    .vin-check-row.ok  { color: #34D399; border-bottom: 1px solid rgba(255,255,255,0.07); }
    .vin-check-row.err { color: #F87171; border-bottom: 1px solid rgba(255,255,255,0.07); }
    /* Freemium CTA */
    .vin-cta {
      display: flex; align-items: center; gap: 14px; flex-wrap: wrap;
      padding: 16px 22px; background: linear-gradient(135deg, rgba(139,92,246,0.12), rgba(249,115,22,0.08));
    }
    .vin-cta-text { display: flex; align-items: center; gap: 10px; flex: 1; min-width: 200px; }
    .vin-cta-text > i { color: #8B5CF6; font-size: 1.1rem; flex-shrink: 0; }
    .vin-cta-text strong { display: block; font-size: 0.85rem; font-weight: 800; color: white; }
    .vin-cta-text span   { font-size: 0.75rem; color: rgba(255,255,255,0.55); line-height: 1.4; }
    .vin-cta-btn {
      display: inline-flex; align-items: center; gap: 8px;
      padding: 10px 18px; border-radius: 10px; font-size: 0.82rem; font-weight: 700;
      background: linear-gradient(135deg, #7C3AED, #F97316);
      color: white; text-decoration: none; white-space: nowrap;
      transition: opacity 0.2s;
    }
    .vin-cta-btn:hover { opacity: 0.85; }
    .vin-error { padding: 12px 16px; background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); border-radius: 10px; font-size: 0.82rem; color: #FCA5A5; }

    /* ── Comparativa honesta v2 ── */
    .comp-cards { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .comp-card {
      background: #F8FAFC; border: 1px solid #E2E8F0;
      border-radius: 14px; overflow: hidden; display: flex; flex-direction: column;
    }
    .comp-card-header {
      display: flex; align-items: center; gap: 10px;
      padding: 14px 18px; border-bottom: 1px solid #E2E8F0; background: #F1F5F9;
    }
    .comp-card-header > i { font-size: 1rem; color: #F97316; flex-shrink: 0; }
    .comp-card-title { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #64748B; }
    .comp-card-body { padding: 18px 18px 20px; flex: 1; display: flex; flex-direction: column; gap: 10px; }
    .comp-card-amount { font-size: 1.9rem; font-weight: 900; letter-spacing: -0.03em; line-height: 1; }
    .comp-card-amount.caro     { color: #DC2626; }
    .comp-card-amount.normal   { color: #EA580C; }
    .comp-card-amount.barato, .comp-card-amount.positivo { color: #059669; }
    .comp-card-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 20px; font-size: 0.64rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; align-self: flex-start; }
    .comp-card-badge.caro     { background: rgba(220,38,38,0.1);  color: #DC2626; }
    .comp-card-badge.normal   { background: rgba(234,88,12,0.1);  color: #EA580C; }
    .comp-card-badge.barato, .comp-card-badge.positivo { background: rgba(5,150,105,0.1); color: #059669; }
    .comp-card-desc { font-size: 0.82rem; color: #475569; line-height: 1.55; }
    @media (max-width: 600px) { .comp-cards { grid-template-columns: 1fr; } }

    /* ── PRINT (coche-specific extras) ── */
    @media print {
      * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
      @page { size: A4; margin: 0; }
      .print-header {
        justify-content: space-between; padding: 16px 22mm;
        background: #5B21B6 !important; border-bottom: none;
      }
      .print-header-left { display: flex; flex-direction: column; gap: 2px; }
      .print-logo { font-size: 1.5rem; letter-spacing: -0.5px; line-height: 1; color: white !important; }
      .print-subtitle { font-size: 0.78rem; color: rgba(255,255,255,0.72) !important; font-weight: 500; margin-top: 3px; }
      .print-header-right { text-align: right; }
      .print-date { font-size: 0.72rem; color: rgba(255,255,255,0.65) !important; line-height: 1.6; }
      .print-url { font-size: 0.65rem; color: rgba(255,255,255,0.45) !important; }
      .result-header   { padding: 18px 22mm !important; background: #F5F3FF !important; border-bottom: 1px solid #DDD6FE; page-break-after: avoid; }
      .numbers-block   { padding: 16px 22mm !important; border-bottom: 1px solid #E2E8F0; page-break-inside: avoid; }
      #costes-section  { padding: 14px 22mm 0 !important; page-break-inside: avoid; }
      .result-body     { padding: 16px 22mm 20mm !important; display: flex !important; flex-direction: column !important; gap: 18px !important; }
      .fraude-banner   { margin: 12px 22mm !important; page-break-inside: avoid; }
      .tipo-badge { background: #EDE9FE !important; color: #6D28D9 !important; border: 1px solid #DDD6FE !important; }
      .pregunta-num { border: 2px solid #7C3AED !important; color: #7C3AED !important; }
      .print-footer {
        display: block !important; text-align: center;
        font-size: 0.62rem; color: #94A3B8 !important;
        padding: 8px 22mm 14mm; border-top: 1px solid #E2E8F0; margin-top: 8px;
      }
    }
    .print-footer { display: none; }
  </style>
</head>
<body>

<!-- HEADER -->
<?php include __DIR__ . '/../src/Includes/_header.php'; ?>

<!-- HERO -->
<div class="hero">
  <div class="hero-tag"><i class="fa fa-car-side"></i> Coches — Financiación &amp; Segunda mano</div>
  <h1>¿Te están<br /><span>timando?</span></h1>
  <p>Pega el enlace del anuncio o el texto de la oferta. Detectamos fraudes, calculamos el coste real y te decimos sin rodeos si merece la pena.</p>
</div>

<!-- MAIN CARD -->
<div class="main">
  <div class="card" id="main-card">

    <!-- FORM -->
    <div id="form-section">
      <div class="card-header">
        <h2><i class="fa fa-magnifying-glass-dollar" style="color:var(--purple);margin-right:8px;"></i>Analiza cualquier oferta o anuncio de coche</h2>
        <p>Pega el enlace del anuncio, el texto de la oferta del concesionario o el email de financiación.</p>
      </div>
      <div class="card-body">
        <div class="textarea-wrap">
          <textarea
            id="offer-text"
            placeholder="Opciones:&#10;• Pega la URL del anuncio (Coches.net, Milanuncios, AutoScout24…)&#10;• Pega el texto de la oferta del concesionario&#10;• Pega el email de financiación&#10;&#10;Ejemplo: &quot;Volkswagen Golf por 299€/mes, 3.000€ entrada, 48 cuotas, pago final 9.500€. TAE 7,99%&quot;"
            maxlength="3000"
          ></textarea>
          <div id="url-indicator" style="display:none; margin-top:8px; padding:10px 14px; background:#EFF6FF; border:1px solid #BFDBFE; border-radius:10px; font-size:0.82rem; color:#1D4ED8; display:none; align-items:center; gap:8px;">
            <i class="fa fa-link"></i>
            <span>Enlace detectado — intentaremos obtener el contenido del anuncio automáticamente</span>
          </div>
        </div>
        <!-- upload zone A -->
        <div class="upload-zone" id="upload-zone-a">
          <label for="file-input-a">
            <i class="fa fa-paperclip"></i>
            <span>Adjuntar factura o contrato (PDF o imagen · máx. 6MB)</span>
          </label>
          <input type="file" id="file-input-a" accept=".pdf,.jpg,.jpeg,.png,.webp" style="display:none">
          <div class="file-chip" id="file-chip-a" style="display:none">
            <i class="fa fa-file-lines"></i>
            <span id="file-name-a"></span>
            <button onclick="clearFile('a')">✕</button>
          </div>
        </div>

        <!-- compare toggle -->
        <button class="btn-compare" id="btn-compare" onclick="toggleCompare()">
          <i class="fa fa-code-compare"></i> Comparar dos ofertas
        </button>

        <!-- section B (hidden) -->
        <div id="compare-b" style="display:none">
          <div class="compare-divider">OFERTA B</div>
          <div style="margin-top:8px">
            <textarea id="offer-text-b" rows="4" placeholder="Pega aquí la segunda oferta, o adjunta el archivo..." class="textarea-b-input" style="width:100%;padding:12px 14px;border-radius:10px;border:1px solid rgba(139,92,246,0.45);background:rgba(139,92,246,0.06);color:rgba(255,255,255,0.9);font-family:'Inter',sans-serif;font-size:0.9rem;resize:vertical;outline:none;box-sizing:border-box"></textarea>
          </div>
          <div class="upload-zone" id="upload-zone-b">
            <label for="file-input-b">
              <i class="fa fa-paperclip"></i>
              <span>Adjuntar Oferta B (PDF o imagen · máx. 6MB)</span>
            </label>
            <input type="file" id="file-input-b" accept=".pdf,.jpg,.jpeg,.png,.webp" style="display:none">
            <div class="file-chip" id="file-chip-b" style="display:none">
              <i class="fa fa-file-lines"></i>
              <span id="file-name-b"></span>
              <button onclick="clearFile('b')">✕</button>
            </div>
          </div>
        </div>
        <div class="char-count"><span id="char-num">0</span> / 3000 caracteres</div>

        <!-- Toggle structured fields -->
        <button class="expand-toggle" id="expand-toggle" onclick="toggleFields()">
          <i class="fa fa-sliders"></i>
          Añadir datos concretos (opcional, para mayor precisión)
          <i class="fa fa-chevron-down chevron"></i>
        </button>

        <div class="structured-fields" id="structured-fields">
          <div class="fields-label">Datos de la oferta</div>
          <div class="fields-grid">
            <div class="field-group">
              <label for="f-precio">Precio al contado</label>
              <div class="input-with-suffix">
                <input type="number" id="f-precio" placeholder="25000" min="0" max="999999" />
                <span class="input-suffix">€</span>
              </div>
              <span class="field-hint">Precio del coche sin financiar</span>
            </div>
            <div class="field-group">
              <label for="f-entrada">Entrada inicial</label>
              <div class="input-with-suffix">
                <input type="number" id="f-entrada" placeholder="3000" min="0" />
                <span class="input-suffix">€</span>
              </div>
            </div>
            <div class="field-group">
              <label for="f-cuota">Cuota mensual</label>
              <div class="input-with-suffix">
                <input type="number" id="f-cuota" placeholder="299" min="0" step="0.01" />
                <span class="input-suffix">€/mes</span>
              </div>
            </div>
            <div class="field-group">
              <label for="f-meses">Número de meses</label>
              <input type="number" id="f-meses" placeholder="48" min="1" max="120" />
            </div>
            <div class="field-group">
              <label for="f-residual">Pago final / Valor residual</label>
              <div class="input-with-suffix">
                <input type="number" id="f-residual" placeholder="9500" min="0" />
                <span class="input-suffix">€</span>
              </div>
              <span class="field-hint">VFG, balloon o cuota final</span>
            </div>
            <div class="field-group">
              <label for="f-tae">TAE anunciada</label>
              <div class="input-with-suffix">
                <input type="number" id="f-tae" placeholder="7.99" min="0" max="100" step="0.01" />
                <span class="input-suffix">%</span>
              </div>
              <span class="field-hint">La que pone en el contrato</span>
            </div>
            <div class="field-group" style="grid-column: 1 / -1;">
              <label for="f-vin">Número de bastidor (VIN)</label>
              <input type="text" id="f-vin" placeholder="Ej: VSSZZZ6JZ9R123456" maxlength="17"
                style="text-transform:uppercase; letter-spacing:0.08em; font-family:'Courier New',monospace;"
                oninput="this.value=this.value.toUpperCase().replace(/[IOQ]/g,'')" />
              <span class="field-hint">17 caracteres — descifra fabricante, año real y país · la IA lo cruzará con el anuncio</span>
            </div>
          </div>
        </div>

        <div id="error-msg"></div>

        <button class="btn-analyze" id="btn-analyze" onclick="analyze()">
          <i class="fa fa-magnifying-glass-dollar"></i> Descifrar oferta ahora
        </button>
      </div>
    </div>

    <!-- LOADING -->
    <div id="loading">
      <div class="scan-ring"></div>
      <p>Analizando la oferta...</p>
      <small>Calculando TAE real, coste total y detectando trampas...</small>
    </div>

    <!-- RESULTS -->
    <!-- Ganador banner (compare mode) -->
    <div class="ganador-banner" id="ganador-banner">
      <div class="ganador-label" id="ganador-label"></div>
      <div class="ganador-motivo" id="ganador-motivo"></div>
      <ul class="diferencias-list" id="diferencias-list"></ul>
    </div>

    <!-- Compare tabs -->
    <div class="compare-tabs" id="compare-tabs" style="display:none;">
      <button class="compare-tab active" id="tab-a" onclick="switchTab('a')">
        <span class="compare-tab-label">Oferta A</span>
        <span class="compare-tab-score" id="tab-score-a"></span>
      </button>
      <button class="compare-tab" id="tab-b" onclick="switchTab('b')">
        <span class="compare-tab-label">Oferta B</span>
        <span class="compare-tab-score" id="tab-score-b"></span>
      </button>
    </div>

        <div id="results">

      <!-- Print-only header -->
      <div class="print-header">
        <div class="print-header-left">
          <div class="print-logo">SinFiltros</div>
          <div class="print-subtitle">Informe de análisis — Coches &amp; Financiación</div>
        </div>
        <div class="print-header-right">
          <div class="print-date" id="print-date"></div>
          <div class="print-url">sinfiltros.es/coche.php</div>
        </div>
      </div>

      <!-- EL NÚMERO QUE DUELE -->
      <div class="numero-duele" id="numero-duele">
        <div class="numero-duele-icon">🔥</div>
        <div>
          <span class="numero-duele-amount" id="numero-duele-amount"></span>
          <span class="numero-duele-label" id="numero-duele-label"></span>
        </div>
      </div>

      <!-- RESUMEN DE LA OFERTA -->
      <div class="resumen-oferta" id="resumen-oferta" style="display:none;">
        <div class="resumen-oferta-icon"><i class="fa fa-file-lines"></i></div>
        <div class="resumen-oferta-text" id="resumen-oferta-text"></div>
      </div>

      <!-- Score + veredicto -->
      <div class="result-header">
        <div class="score-row">
          <div class="transparency-gauge">
            <svg viewBox="0 0 110 110" width="110" height="110">
              <circle class="gauge-track" cx="55" cy="55" r="44" />
              <circle class="gauge-bar" id="gauge-bar" cx="55" cy="55" r="44"
                stroke-dasharray="276.46"
                stroke-dashoffset="276.46"
              />
            </svg>
            <div class="gauge-label">
              <span class="gauge-num" id="gauge-num">0</span>
              <span class="gauge-txt">Transparencia</span>
            </div>
          </div>
          <div class="score-meta">
            <div class="tipo-badge" id="tipo-badge"><i class="fa fa-tag"></i> —</div>
            <div class="veredicto" id="result-veredicto"></div>
            <div class="transparency-label" id="transparency-label"></div>
          </div>
        </div>
      </div>

      <!-- FRAUDE BANNER (hidden by default) -->
      <div id="fraude-banner" class="fraude-banner" style="display:none;">
        <div class="fraude-banner-hdr">
          <div class="fraude-icon"><i class="fa fa-skull-crossbones"></i></div>
          <div class="fraude-title">ALERTAS DE FRAUDE DETECTADAS</div>
          <div class="fraude-score" id="fraude-score-badge">Riesgo: —%</div>
        </div>
        <div id="fraude-items"></div>
      </div>

      <!-- Real numbers -->
      <div class="numbers-block">
        <div class="block-title"><i class="fa fa-calculator" style="color:var(--purple);"></i> Lo que realmente pagas</div>
        <div class="numbers-grid" id="numbers-grid"></div>
        <div id="tae-compare" style="display:none;" class="tae-compare">
          <div class="tae-item anun">
            <span class="tae-val" id="tae-anun-val">—%</span>
            <span class="tae-lbl">TAE anunciada</span>
          </div>
          <div class="tae-vs">vs</div>
          <div class="tae-item real">
            <span class="tae-val" id="tae-real-val">—%</span>
            <span class="tae-lbl">TAE real calculada</span>
          </div>
        </div>
      </div>

      <!-- COSTES ADICIONALES (segunda mano) -->
      <div id="costes-section" style="display:none; padding: 20px 32px 0;">
        <div class="costes-block">
          <div class="block-title" style="margin-bottom:14px;"><i class="fa fa-receipt" style="color:#C2410C;"></i> Gastos adicionales a tener en cuenta</div>
          <div class="costes-grid" id="costes-grid"></div>
          <div class="coste-nota" id="coste-nota"></div>
        </div>
      </div>

      <!-- VIN DECODE -->
      <div id="vin-section" style="display:none; padding: 20px 32px 0;">
        <div class="block-title" style="margin-bottom:12px;"><i class="fa fa-barcode" style="color:var(--purple);"></i> Decodificación del bastidor (VIN)</div>
        <div class="vin-card">
          <div class="vin-card-header">
            <i class="fa fa-barcode"></i>
            <div>
              <div class="vin-card-title">Número de bastidor</div>
              <div class="vin-card-num" id="vin-display"></div>
            </div>
          </div>
          <div class="vin-grid" id="vin-grid"></div>
          <div id="vin-check-row"></div>
          <div class="vin-cta">
            <div class="vin-cta-text">
              <i class="fa fa-file-shield"></i>
              <div>
                <strong>¿Quieres el historial completo?</strong>
                <span>ITV, propietarios anteriores, km reales, accidentes — informe completo por ~1,50€</span>
              </div>
            </div>
            <a id="vin-cta-link" href="#" target="_blank" rel="noopener noreferrer" class="vin-cta-btn">
              Ver informe completo <i class="fa fa-arrow-right"></i>
            </a>
          </div>
        </div>
      </div>
      <div id="vin-error-section" style="display:none; padding: 20px 32px 0;">
        <div class="vin-error" id="vin-error-msg"></div>
      </div>

      <!-- Body sections -->
      <div class="result-body">

        <!-- ASESOR HONESTO (promoted) -->
        <div id="asesor-section" style="display:none;">
          <div class="asesor-honesto-card">
            <div class="asesor-honesto-icon"><i class="fa fa-lightbulb"></i></div>
            <div class="asesor-honesto-content">
              <div class="asesor-honesto-title">Qué haría un asesor honesto</div>
              <div class="asesor-honesto-text" id="asesor-text"></div>
            </div>
          </div>
        </div>

        <!-- TRAMPAS -->
        <div id="trampas-section">
          <div class="section-hdr"><i class="fa fa-triangle-exclamation" style="color:var(--red);"></i> Trampas y puntos de atención</div>
          <div class="trampa-list" id="trampa-list"></div>
        </div>

        <!-- VENTAJAS -->
        <div id="ventajas-section" style="display:none;">
          <div class="section-hdr"><i class="fa fa-circle-check" style="color:var(--green);"></i> Ventajas reales de esta oferta</div>
          <ul class="ventaja-list" id="ventaja-list"></ul>
        </div>

        <!-- COMPARATIVA -->
        <div id="comparativa-section" style="display:none;">
          <div class="section-hdr"><i class="fa fa-scale-balanced" style="color:#F97316;"></i> Comparativa honesta</div>
          <div class="comparativa-block">
            <div class="comp-grid" id="comp-grid"></div>
            <div class="recomendacion-box" id="recomendacion-box" style="display:none;">
              <strong><i class="fa fa-lightbulb"></i> Qué haría un asesor honesto</strong>
              <span id="recomendacion-text"></span>
            </div>
          </div>
        </div>

        <!-- PREGUNTAS -->
        <div id="preguntas-section" style="display:none;">
          <div class="section-hdr"><i class="fa fa-circle-question" style="color:var(--purple);"></i> Preguntas que DEBES hacer antes de firmar</div>
          <div class="preguntas-list" id="preguntas-list"></div>
        </div>

      </div>

      <!-- SHARE SECTION -->
      <div class="share-section" id="share-section">
        <div class="share-label"><i class="fa fa-share-nodes"></i> Comparte el análisis</div>
        <div class="share-buttons">
          <a class="btn-share twitter" id="btn-share-twitter" href="#" target="_blank" rel="noopener">
            <i class="fa-brands fa-x-twitter"></i> Compartir en X
          </a>
          <a class="btn-share whatsapp" id="btn-share-whatsapp" href="#" target="_blank" rel="noopener">
            <i class="fa-brands fa-whatsapp"></i> Enviar por WhatsApp
          </a>
          <button class="btn-share print" onclick="window.print()">
            <i class="fa fa-print"></i> Imprimir / PDF
          </button>
          <button class="btn-share copy" id="btn-copy" onclick="copySummary()">
            <i class="fa fa-copy"></i> Copiar resumen
          </button>
        </div>
      </div>

      <button class="btn-reset" onclick="resetForm()">
        <i class="fa fa-rotate-left" style="margin-right:6px;"></i> Analizar otra oferta
      </button>

      <!-- Print-only footer -->
      <div class="print-footer">
        Generado por <strong>SinFiltros</strong> · sinfiltros.es · La IA que te lo dice sin filtros
      </div>

    </div><!-- /results -->

  </div><!-- /card -->
</div><!-- /main -->

<!-- EXAMPLE OFFERS -->
<div class="examples-section">
  <div class="examples-label">Ejemplos de ofertas típicas de concesionario</div>
  <div class="examples-grid">
    <div class="example-chip" onclick="loadExample(0)">
      <div class="example-chip-label">Oferta PCP con balloon</div>
      <div class="example-chip-text">"Seat Ibiza desde 179€/mes, 2.000€ de entrada, 36 cuotas, cuota final 7.200€, TAE 9,99%"</div>
    </div>
    <div class="example-chip" onclick="loadExample(1)">
      <div class="example-chip-label">Préstamo concesionario</div>
      <div class="example-chip-text">"Financiamos tu Peugeot 208 nuevo a 48 meses por solo 289€/mes sin entrada. TAE 5,49%"</div>
    </div>
    <div class="example-chip" onclick="loadExample(2)">
      <div class="example-chip-label">Renting particular</div>
      <div class="example-chip-text">"Renting Kia Sportage por 399€/mes, 36 meses, 15.000 km/año incluidos, sin gastos adicionales"</div>
    </div>
  </div>
</div>

<!-- FOOTER -->
<?php include __DIR__ . '/../src/Includes/_footer.php'; ?>

<script>
  // ── File upload + compare state ──
  let fileDataA = null, fileTypeA = null;
  let fileDataB = null, fileTypeB = null;
  let compareMode = false;

  function setupFileInput(inputId, suffix) {
    document.getElementById(inputId).addEventListener('change', (e) => {
      const file = e.target.files[0];
      if (!file) return;
      if (file.size > 6 * 1024 * 1024) {
        alert('El archivo supera el límite de 6MB.');
        e.target.value = '';
        return;
      }
      const reader = new FileReader();
      reader.onload = () => {
        const b64 = reader.result.split(',')[1];
        if (suffix === 'a') { fileDataA = b64; fileTypeA = file.type; }
        else               { fileDataB = b64; fileTypeB = file.type; }
        document.getElementById('file-name-' + suffix).textContent = file.name;
        document.getElementById('file-chip-' + suffix).style.display = 'flex';
      };
      reader.readAsDataURL(file);
    });
  }
  setupFileInput('file-input-a', 'a');
  setupFileInput('file-input-b', 'b');

  function clearFile(suffix) {
    if (suffix === 'a') {
      fileDataA = null; fileTypeA = null;
      document.getElementById('file-input-a').value = '';
    } else {
      fileDataB = null; fileTypeB = null;
      document.getElementById('file-input-b').value = '';
    }
    document.getElementById('file-chip-' + suffix).style.display = 'none';
  }

  function toggleCompare() {
    compareMode = !compareMode;
    const secB = document.getElementById('compare-b');
    secB.style.display = compareMode ? 'block' : 'none';
    document.getElementById('btn-compare').classList.toggle('active', compareMode);
  }

  // ── Char counter + URL detection ──
  const offerEl      = document.getElementById('offer-text');
  const charNumEl    = document.getElementById('char-num');
  const urlIndicator = document.getElementById('url-indicator');

  function isUrl(str) {
    return /^https?:\/\//i.test(str.trim());
  }

  offerEl.addEventListener('input', () => {
    charNumEl.textContent = offerEl.value.length;
    urlIndicator.style.display = isUrl(offerEl.value) ? 'flex' : 'none';
  });

  // ── Toggle structured fields ──
  function toggleFields() {
    const toggle = document.getElementById('expand-toggle');
    const fields = document.getElementById('structured-fields');
    toggle.classList.toggle('open');
    fields.classList.toggle('open');
  }

  // ── Examples ──
  const EXAMPLES = [
    {
      text: 'Seat Ibiza 1.0 TSI 95 CV Xcellence. Oferta de financiación: 2.000€ de entrada, 36 cuotas de 179€/mes, pago final (VFG) de 7.200€. TAE del 9,99%. Precio al contado del vehículo: 19.500€.',
      precio: 19500, entrada: 2000, cuota: 179, meses: 36, residual: 7200, tae: 9.99
    },
    {
      text: 'Peugeot 208 Active Pack 100 CV. Financia sin entrada a 48 meses por 289€/mes. TAE 5,49% (TIN 5,35%). Precio final del vehículo 19.990€.',
      precio: 19990, entrada: 0, cuota: 289, meses: 48, residual: 0, tae: 5.49
    },
    {
      text: 'Renting particular Kia Sportage 1.6 PHEV. Cuota mensual: 399€/mes por 36 meses con 15.000 km anuales incluidos. Seguro a todo riesgo, mantenimiento y asistencia incluidos. Sin gastos de matriculación. Sin pago final.',
      precio: 0, entrada: 0, cuota: 399, meses: 36, residual: 0, tae: 0
    }
  ];

  function loadExample(i) {
    const e = EXAMPLES[i];
    offerEl.value = e.text;
    charNumEl.textContent = e.text.length;

    // Open fields and fill
    const toggle = document.getElementById('expand-toggle');
    const fields = document.getElementById('structured-fields');
    if (!toggle.classList.contains('open')) {
      toggle.classList.add('open');
      fields.classList.add('open');
    }
    if (e.precio)   document.getElementById('f-precio').value   = e.precio;
    if (e.entrada)  document.getElementById('f-entrada').value  = e.entrada;
    if (e.cuota)    document.getElementById('f-cuota').value    = e.cuota;
    if (e.meses)    document.getElementById('f-meses').value    = e.meses;
    if (e.residual) document.getElementById('f-residual').value = e.residual;
    if (e.tae)      document.getElementById('f-tae').value      = e.tae;

    document.getElementById('main-card').scrollIntoView({ behavior: 'smooth' });
  }

  // ── Analyze ──
  async function analyze() {
    const offerText = offerEl.value.trim();
    const precio    = parseInt(document.getElementById('f-precio').value)   || 0;
    const entrada   = parseInt(document.getElementById('f-entrada').value)  || 0;
    const cuota     = parseFloat(document.getElementById('f-cuota').value)  || 0;
    const meses     = parseInt(document.getElementById('f-meses').value)    || 0;
    const residual  = parseInt(document.getElementById('f-residual').value) || 0;
    const tae       = parseFloat(document.getElementById('f-tae').value)    || 0;
    const vin       = (document.getElementById('f-vin').value || '').trim().toUpperCase();

    const errEl = document.getElementById('error-msg');
    errEl.style.display = 'none';

    if (!offerText && !fileDataA && (!cuota || !meses)) {
      errEl.textContent = 'Pega la oferta del concesionario en el campo de texto, o introduce al menos la cuota mensual y el número de meses.';
      errEl.style.display = 'block';
      return;
    }

    document.getElementById('form-section').style.display = 'none';
    document.getElementById('results').style.display      = 'none';
    document.getElementById('loading').style.display      = 'block';
    document.getElementById('btn-analyze').disabled       = true;

    // Rotating loading messages
    const loadingMsg = document.querySelector('#loading p');
    const loadingSmall = document.querySelector('#loading small');
    const LOADING_MSGS = isUrl(offerText) ? [
      ['Leyendo el anuncio...', 'Obteniendo la oferta que no querían que analizaras...'],
      ['Extrayendo la letra pequeña...', 'La que está en fuente 6 al final de la página...'],
      ['Calculando el TAE real...', 'Spoiler: probablemente no sea el que anuncian...'],
      ['Detectando trampas...', 'Buscando las cláusulas que firmas sin leer...'],
    ] : [
      ['Analizando la oferta...', 'Calculando TAE real, coste total y detectando trampas...'],
      ['Destripando los números...', 'El precio que te dicen vs el que realmente pagas...'],
      ['Buscando las trampas...', 'Las cláusulas diseñadas para que no las leas...'],
      ['Preparando el veredicto...', 'Sin filtros. Sin rodeos. Sin suavizar la realidad...'],
    ];
    let msgIdx = 0;
    loadingMsg.textContent   = LOADING_MSGS[0][0];
    loadingSmall.textContent = LOADING_MSGS[0][1];
    const msgInterval = setInterval(() => {
      msgIdx = (msgIdx + 1) % LOADING_MSGS.length;
      loadingMsg.textContent   = LOADING_MSGS[msgIdx][0];
      loadingSmall.textContent = LOADING_MSGS[msgIdx][1];
    }, 2800);

    try {
      const res = await fetch('src/Api/coche-api.php', {
        // msgInterval cleared on success/error below
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          offer_text:    offerText,
          precio_contado: precio,
          entrada,
          cuota,
          meses,
          valor_residual: residual,
          tae_anunciada:  tae,
          file_base64:       fileDataA  || undefined,
          file_media_type:   fileTypeA  || undefined,
          compare_mode:      compareMode || undefined,
          offer_text_b:      compareMode ? (document.getElementById('offer-text-b')?.value.trim() || '') : undefined,
          file_base64_b:     compareMode ? (fileDataB  || undefined) : undefined,
          file_media_type_b: compareMode ? (fileTypeB  || undefined) : undefined,
          vin:               vin || undefined,
        })
      });

      const data = await res.json();
      clearInterval(msgInterval);
      if (!res.ok || data.error) {
        if (data.url_fetch_failed) {
          throw new Error(data.error);
        }
        throw new Error(data.error || 'Error al analizar la oferta');
      }

      renderResults(data);

    } catch(e) {
      clearInterval(msgInterval);
      document.getElementById('loading').style.display      = 'none';
      document.getElementById('form-section').style.display = 'block';
      document.getElementById('btn-analyze').disabled       = false;
      errEl.textContent = e.message || 'Ocurrió un error. Inténtalo de nuevo.';
      errEl.style.display = 'block';
    }
  }

  // ── Render results ──
  let _gaugeInterval = null;
  window._compareData = null;

  function renderResults(data) {
    // ── Compare mode: ganador banner ──
    const ganadorBanner = document.getElementById('ganador-banner');
    if (data.ganador && ganadorBanner) {
      const colors = { A: '#10B981', B: '#3B82F6', EMPATE: '#F97316' };
      ganadorBanner.style.display = 'block';
      ganadorBanner.style.borderColor = colors[data.ganador] || '#64748B';
      const gl = document.getElementById('ganador-label');
      if (gl) gl.textContent = data.ganador === 'EMPATE' ? '🤝 EMPATE' : `🏆 Mejor oferta: ${data.ganador}`;
      const gm = document.getElementById('ganador-motivo');
      if (gm) gm.textContent = data.motivo_ganador || '';
      const dl = document.getElementById('diferencias-list');
      if (dl) {
        dl.innerHTML = '';
        (data.diferencias_principales || []).forEach(d => {
          const li = document.createElement('li');
          li.textContent = d;
          dl.appendChild(li);
        });
      }
      // Store both analyses for tab switching
      if (data.analisis_a && data.analisis_b) {
        window._compareData = { a: data.analisis_a, b: data.analisis_b, ganador: data.ganador };
        document.getElementById('compare-tabs').style.display = 'flex';
        document.getElementById('tab-score-a').textContent = (data.analisis_a.puntuacion_transparencia || '?') + '/100';
        document.getElementById('tab-score-b').textContent = (data.analisis_b.puntuacion_transparencia || '?') + '/100';
        if (data.ganador === 'A') document.getElementById('tab-a').classList.add('winner');
        if (data.ganador === 'B') document.getElementById('tab-b').classList.add('winner');
      }
      if (data.analisis_a) Object.assign(data, data.analisis_a);
    } else if (ganadorBanner) {
      ganadorBanner.style.display = 'none';
      document.getElementById('compare-tabs').style.display = 'none';
    }

    document.getElementById('loading').style.display  = 'none';
    document.getElementById('results').style.display  = 'block';
    document.getElementById('print-date').textContent = 'Análisis: ' + new Date().toLocaleDateString('es-ES', {day:'2-digit',month:'long',year:'numeric'});

    renderResultsContent(data);
    document.getElementById('main-card').scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  function switchTab(tab) {
    if (!window._compareData) return;
    document.getElementById('tab-a').classList.toggle('active', tab === 'a');
    document.getElementById('tab-b').classList.toggle('active', tab === 'b');
    renderResultsContent(tab === 'a' ? window._compareData.a : window._compareData.b);
  }

  function renderResultsContent(data) {
    // ── EL NÚMERO QUE DUELE ──
    const nums_raw = data.numeros_reales || {};
    const costeFinanciacion = nums_raw.coste_financiacion;
    const nuleroDueleEl = document.getElementById('numero-duele');
    const numeroDueleAmount = document.getElementById('numero-duele-amount');
    const numeroDueleLabel  = document.getElementById('numero-duele-label');
    if (costeFinanciacion && costeFinanciacion > 500) {
      numeroDueleAmount.textContent = Math.round(costeFinanciacion).toLocaleString('es-ES') + '€';
      numeroDueleLabel.textContent  = 'de más sobre el precio al contado — el coste real de esta financiación';
      nuleroDueleEl.style.display   = 'flex';
    } else {
      nuleroDueleEl.style.display = 'none';
    }

    // ── RESUMEN DE LA OFERTA ──
    const resumenEl = document.getElementById('resumen-oferta');
    const resumenText = document.getElementById('resumen-oferta-text');
    const resumen = data.resumen_oferta || data.resumen_poliza || data.resumen;
    if (resumen && resumenEl) {
      resumenText.textContent = resumen;
      resumenEl.style.display = 'flex';
    } else if (resumenEl) {
      resumenEl.style.display = 'none';
    }

    // ── SHARE BUTTONS ──
    const veredictoText = data.veredicto || 'He analizado esta oferta con SinFiltros';
    const costeExtra    = costeFinanciacion ? `Me están cobrando ${Math.round(costeFinanciacion).toLocaleString('es-ES')}€ de más. ` : '';
    const tweetText     = encodeURIComponent(`${costeExtra}${veredictoText.substring(0,100)}... Analiza la tuya gratis 👇 #SinFiltros`);
    const tweetUrl      = encodeURIComponent('https://sinfiltros.es/coche.php');
    const waText        = encodeURIComponent(`${costeExtra}${veredictoText.substring(0,120)}...\n\nAnaliza tú también: https://sinfiltros.es/coche.php`);
    document.getElementById('btn-share-twitter').href  = `https://twitter.com/intent/tweet?text=${tweetText}&url=${tweetUrl}`;
    document.getElementById('btn-share-whatsapp').href = `https://wa.me/?text=${waText}`;
    document.getElementById('share-section').style.display = 'block';

    // Store summary for copy
    window._sfSummary = `SinFiltros — Análisis de oferta de coche\n${'─'.repeat(40)}\n${veredictoText}\n${costeExtra}\n\nAnálisis gratuito en: https://sinfiltros.es/coche.php`;

    // Gauge animation
    if (_gaugeInterval) clearInterval(_gaugeInterval);
    const score  = Math.max(0, Math.min(100, data.puntuacion_transparencia || 50));
    const circum = 276.46;
    const offset = circum - (score / 100) * circum;
    const color  = score >= 70 ? '#10B981' : score >= 40 ? '#F97316' : '#EF4444';
    const bar    = document.getElementById('gauge-bar');
    const numEl  = document.getElementById('gauge-num');
    bar.style.stroke = color;
    numEl.style.color = color;
    bar.style.strokeDashoffset = circum;
    setTimeout(() => { bar.style.strokeDashoffset = offset; }, 80);
    let n = 0;
    _gaugeInterval = setInterval(() => {
      n = Math.min(n + 2, score);
      numEl.textContent = n;
      if (n >= score) clearInterval(_gaugeInterval);
    }, 18);

    // Tipo badge
    const tipoLabels = {
      PRESTAMO_PERSONAL: 'Préstamo personal', PRESTAMO_CONCESIONARIO: 'Préstamo concesionario',
      LEASING: 'Leasing', RENTING: 'Renting', PCP: 'PCP / Balloon',
      COMPRA_DIRECTA: 'Compra directa'
    };
    const tipoEl = document.getElementById('tipo-badge');
    tipoEl.innerHTML = `<i class="fa fa-tag"></i> ${tipoLabels[data.tipo_financiacion] || data.tipo_financiacion || '—'}`;

    // Fraud risk badge
    const riesgo = Math.max(0, Math.min(100, data.riesgo_fraude || 0));
    const riesgoCls  = riesgo >= 51 ? 'high' : riesgo >= 21 ? 'medium' : 'low';
    const riesgoIcon = riesgo >= 51 ? 'fa-skull-crossbones' : riesgo >= 21 ? 'fa-triangle-exclamation' : 'fa-shield-check';
    const riesgoLbl  = riesgo >= 51 ? 'Riesgo alto' : riesgo >= 21 ? 'Riesgo medio' : 'Sin riesgo';
    const scoreMetaEl = document.querySelector('.score-meta');
    let fraudeBadgeEl = document.getElementById('fraude-mini-badge');
    if (!fraudeBadgeEl) {
      fraudeBadgeEl = document.createElement('span');
      fraudeBadgeEl.id = 'fraude-mini-badge';
      fraudeBadgeEl.className = `fraude-mini-badge ${riesgoCls}`;
      tipoEl.insertAdjacentElement('afterend', fraudeBadgeEl);
    } else {
      fraudeBadgeEl.className = `fraude-mini-badge ${riesgoCls}`;
    }
    fraudeBadgeEl.innerHTML = `<i class="fa ${riesgoIcon}"></i> ${riesgoLbl} de fraude`;

    document.getElementById('result-veredicto').textContent = data.veredicto || '';

    const transLabel = score >= 70 ? 'Anuncio relativamente transparente' :
                       score >= 40 ? 'Transparencia media' :
                                     'Poca transparencia';
    document.getElementById('transparency-label').textContent = `Transparencia: ${transLabel} (${score}/100)`;

    // FRAUDE BANNER
    const alertasFraude = data.alertas_fraude || [];
    const fraudeBanner  = document.getElementById('fraude-banner');
    const fraudeItems   = document.getElementById('fraude-items');
    const fraudeScoreBadge = document.getElementById('fraude-score-badge');
    fraudeItems.innerHTML = '';
    if (alertasFraude.length > 0) {
      fraudeBanner.style.display = 'block';
      fraudeScoreBadge.textContent = `Riesgo: ${riesgo}%`;
      alertasFraude.forEach(a => {
        fraudeItems.innerHTML += `
          <div class="fraude-item">
            <i class="fa fa-circle-exclamation"></i>
            <span>${escHtml(a)}</span>
          </div>`;
      });
    } else {
      fraudeBanner.style.display = 'none';
    }

    // Numbers
    const nums    = data.numeros_reales || {};
    const gridEl  = document.getElementById('numbers-grid');
    gridEl.innerHTML = '';
    const fmt = (v) => v != null ? Math.round(v).toLocaleString('es-ES') + '€' : '—';
    const fmtD = (v) => v != null ? parseFloat(v).toLocaleString('es-ES', {minimumFractionDigits:0,maximumFractionDigits:2}) + '€' : '—';

    const isDirecta = data.tipo_financiacion === 'COMPRA_DIRECTA' || data.modo_analisis === 'SEGUNDA_MANO';

    const items = [
      { val: fmt(nums.precio_contado), lbl: 'Precio al contado', cls: '' },
      { val: fmt(nums.entrada),        lbl: 'Entrada',           cls: '', hideIfDirecta: true },
      { val: fmtD(nums.total_cuotas),  lbl: 'Total cuotas',      cls: '', hideIfDirecta: true },
      { val: fmt(nums.pago_final),     lbl: 'Pago final',        cls: '', hideIfDirecta: true },
      { val: fmtD(nums.total_pagado),  lbl: isDirecta ? 'Precio total' : 'TOTAL PAGADO', cls: 'total', extra: null },
      { val: fmtD(nums.coste_financiacion), lbl: 'Coste financiación',
        cls: 'cost', extra: nums.porcentaje_extra != null ? `+${nums.porcentaje_extra}% vs contado` : null }
    ];

    items.forEach(it => {
      if (it.hideIfDirecta && isDirecta) return; // en compra directa no hay entrada/cuotas/pago final
      if (it.val === '—' && it.cls !== 'total') return; // skip unknowns except total
      gridEl.innerHTML += `
        <div class="number-item ${it.cls}">
          <span class="number-val">${escHtml(it.val)}</span>
          <span class="number-lbl">${escHtml(it.lbl)}</span>
          ${it.extra ? `<span class="number-extra">${escHtml(it.extra)}</span>` : ''}
        </div>`;
    });

    // TAE comparison
    const taeReal = data.tae_real;
    const taeAnun = data.tae_anunciada;
    if (taeReal != null || taeAnun != null) {
      document.getElementById('tae-compare').style.display = 'flex';
      document.getElementById('tae-anun-val').textContent = taeAnun != null ? taeAnun + '%' : 'N/D';
      document.getElementById('tae-real-val').textContent = taeReal != null ? taeReal + '%' : 'N/D';
    }

    // COSTES ADICIONALES
    const costes    = data.costes_adicionales || {};
    const costesSec = document.getElementById('costes-section');
    const costesGrid = document.getElementById('costes-grid');
    const costeNota  = document.getElementById('coste-nota');
    costesGrid.innerHTML = '';
    if (costes.aplica) {
      costesSec.style.display = 'block';
      const costeItems = [
        { val: costes.itp_estimado,       lbl: 'ITP (Impuesto Transferencia)' },
        { val: costes.ivtm_anual,         lbl: 'IVTM (Impuesto circulación/año)' },
        { val: costes.seguro_minimo_anual, lbl: 'Seguro mínimo RC / año' },
      ];
      costeItems.forEach(ci => {
        if (!ci.val) return;
        costesGrid.innerHTML += `
          <div class="coste-item">
            <span class="coste-val">${escHtml(String(ci.val))}</span>
            <span class="coste-lbl">${escHtml(ci.lbl)}</span>
          </div>`;
      });
      if (costes.total_extra_estimado) {
        costesGrid.innerHTML += `
          <div class="coste-item coste-total-item">
            <div>
              <span class="coste-val">~${Math.round(costes.total_extra_estimado).toLocaleString('es-ES')}€</span>
              <span class="coste-lbl">Gastos extra 1er año</span>
            </div>
            <i class="fa fa-circle-info" style="color:rgba(255,255,255,0.5);font-size:1.2rem;"></i>
          </div>`;
      }
      if (costes.nota) costeNota.textContent = costes.nota;
    } else {
      costesSec.style.display = 'none';
    }

    // ── VIN DECODE ──
    const vinSec      = document.getElementById('vin-section');
    const vinErrSec   = document.getElementById('vin-error-section');
    const vinData     = data.vin_decode;
    if (vinData && vinData.valid) {
      vinSec.style.display    = 'block';
      vinErrSec.style.display = 'none';
      // Format VIN in groups of 3-6-8 for readability
      const v = vinData.vin;
      document.getElementById('vin-display').textContent = v.slice(0,3) + ' ' + v.slice(3,9) + ' ' + v.slice(9);
      // Grid cells
      const vinGrid = document.getElementById('vin-grid');
      const mfrLabel = vinData.manufacturer || ('WMI: ' + vinData.wmi);
      const cells = [
        { val: vinData.country,      lbl: 'País de fabricación' },
        { val: mfrLabel,             lbl: 'Fabricante' },
        { val: vinData.model_year,   lbl: 'Año de modelo' },
        { val: vinData.plant_code,   lbl: 'Cód. planta' },
      ];
      vinGrid.innerHTML = cells.map(c =>
        `<div class="vin-cell"><div class="vin-cell-val">${escHtml(c.val)}</div><div class="vin-cell-lbl">${escHtml(c.lbl)}</div></div>`
      ).join('');
      // Check digit
      const chk = document.getElementById('vin-check-row');
      chk.className = 'vin-check-row ' + (vinData.check_ok ? 'ok' : 'err');
      chk.innerHTML = vinData.check_ok
        ? '<i class="fa fa-circle-check"></i> Dígito de control válido — VIN coherente'
        : '<i class="fa fa-triangle-exclamation"></i> Dígito de control INVÁLIDO — revisa el número o confirma con el vendedor';
      // CTA link to carVertical
      const ctaLink = document.getElementById('vin-cta-link');
      ctaLink.href = 'https://www.carvertical.com/es/precheck?vin=' + encodeURIComponent(vinData.vin);
    } else if (vinData && !vinData.valid) {
      vinSec.style.display    = 'none';
      vinErrSec.style.display = 'block';
      document.getElementById('vin-error-msg').innerHTML =
        '<i class="fa fa-triangle-exclamation"></i> VIN inválido: ' + escHtml(vinData.error);
    } else {
      vinSec.style.display    = 'none';
      vinErrSec.style.display = 'none';
    }

    // ── ASESOR HONESTO (promoted) ──
    const comp = data.comparativa;
    const asesorSec  = document.getElementById('asesor-section');
    const asesorText = document.getElementById('asesor-text');
    if (comp && comp.recomendacion && asesorSec) {
      asesorText.textContent = comp.recomendacion;
      asesorSec.style.display = 'block';
    } else if (asesorSec) {
      asesorSec.style.display = 'none';
    }

    // Trampas
    const trampas = data.trampa || [];
    const trampaEl = document.getElementById('trampa-list');
    trampaEl.innerHTML = '';
    trampas.forEach(t => {
      trampaEl.innerHTML += `
        <div class="trampa-item">
          <div class="trampa-icon"><i class="fa fa-triangle-exclamation"></i></div>
          <div class="trampa-text">${escHtml(t)}</div>
        </div>`;
    });
    document.getElementById('trampas-section').style.display = trampas.length ? 'block' : 'none';

    // Ventajas
    const ventajas = data.ventajas || [];
    const ventajaSec = document.getElementById('ventajas-section');
    const ventajaEl  = document.getElementById('ventaja-list');
    ventajaEl.innerHTML = '';
    if (ventajas.length) {
      ventajaSec.style.display = 'block';
      ventajas.forEach(v => {
        ventajaEl.innerHTML += `<li class="ventaja-item"><i class="fa fa-circle-check"></i> ${escHtml(v)}</li>`;
      });
    } else {
      ventajaSec.style.display = 'none';
    }

    // Comparativa
    const compSec = document.getElementById('comparativa-section');
    if (comp) {
      compSec.style.display = 'block';
      const compGrid = document.getElementById('comp-grid');
      compGrid.innerHTML = '';

      const verdictMeta = {
        'CARO':    { cls: 'caro',     icon: 'fa-arrow-trend-up',   label: 'Más caro' },
        'NORMAL':  { cls: 'normal',   icon: 'fa-minus',            label: 'Similar' },
        'BARATO':  { cls: 'barato',   icon: 'fa-arrow-trend-down', label: 'Más barato' },
        'POSITIVO':{ cls: 'positivo', icon: 'fa-thumbs-up',        label: 'Favorable' },
      };

      function buildCompCard(scenario, title, faIcon) {
        if (!scenario) return '';
        // Backward compat: old format was a plain string
        if (typeof scenario === 'string') {
          return `<div class="comp-row">
            <div class="comp-icon"><i class="fa ${faIcon}"></i></div>
            <div class="comp-text"><strong>${escHtml(title)}</strong>${escHtml(scenario)}</div>
          </div>`;
        }
        const vm = verdictMeta[scenario.veredicto] || verdictMeta['NORMAL'];
        const raw = scenario.diferencia_eur;
        const amtStr = raw != null
          ? (raw >= 0 ? '+' : '') + Math.round(raw).toLocaleString('es-ES') + '€'
          : '';
        return `
          <div class="comp-card">
            <div class="comp-card-header">
              <i class="fa ${faIcon}"></i>
              <div class="comp-card-title">${escHtml(title)}</div>
            </div>
            <div class="comp-card-body">
              ${amtStr ? `<div class="comp-card-amount ${vm.cls}">${escHtml(amtStr)}</div>` : ''}
              ${scenario.veredicto ? `<span class="comp-card-badge ${vm.cls}"><i class="fa ${vm.icon}"></i> ${escHtml(vm.label)}</span>` : ''}
              ${scenario.descripcion ? `<div class="comp-card-desc">${escHtml(scenario.descripcion)}</div>` : ''}
            </div>
          </div>`;
      }

      const isNewFormat = comp.vs_contado && typeof comp.vs_contado === 'object'
                       || comp.vs_banco   && typeof comp.vs_banco   === 'object';
      compGrid.className = isNewFormat ? 'comp-cards' : 'comp-grid';
      compGrid.innerHTML =
        buildCompCard(comp.vs_contado, 'vs Pagar al contado',        'fa-money-bill') +
        buildCompCard(comp.vs_banco,   'vs Préstamo bancario (~9%)', 'fa-building-columns');

      document.getElementById('recomendacion-box').style.display = 'none';
    } else {
      compSec.style.display = 'none';
    }

    // Preguntas
    const preguntas = data.preguntas_clave || [];
    const pregSec = document.getElementById('preguntas-section');
    const pregEl  = document.getElementById('preguntas-list');
    pregEl.innerHTML = '';
    if (preguntas.length) {
      pregSec.style.display = 'block';
      preguntas.forEach((p, i) => {
        // Support both old (string) and new (object) format
        if (typeof p === 'string') {
          pregEl.innerHTML += `
            <div class="pregunta-item">
              <div class="pregunta-header">
                <div class="pregunta-num">${i + 1}</div>
                <div class="pregunta-text">${escHtml(p)}</div>
              </div>
            </div>`;
        } else {
          pregEl.innerHTML += `
            <div class="pregunta-item">
              <div class="pregunta-header">
                <div class="pregunta-num">${i + 1}</div>
                <div class="pregunta-text">${escHtml(p.pregunta || '')}</div>
              </div>
              ${p.por_que ? `<div class="pregunta-why">${escHtml(p.por_que)}</div>` : ''}
              ${(p.respuesta_buena || p.respuesta_mala) ? `
              <div class="pregunta-answers">
                ${p.respuesta_buena ? `
                <div class="pregunta-answer good">
                  <i class="fa fa-circle-check"></i>
                  <div class="pregunta-answer-body">
                    <strong>Respuesta que quieres</strong>
                    <span>${escHtml(p.respuesta_buena)}</span>
                  </div>
                </div>` : ''}
                ${p.respuesta_mala ? `
                <div class="pregunta-answer bad">
                  <i class="fa fa-triangle-exclamation"></i>
                  <div class="pregunta-answer-body">
                    <strong>Señal de alarma</strong>
                    <span>${escHtml(p.respuesta_mala)}</span>
                  </div>
                </div>` : ''}
              </div>` : ''}
            </div>`;
        }
      });
    } else {
      pregSec.style.display = 'none';
    }
  }

  // ── Reset ──
  function resetForm() {
    document.getElementById('results').style.display      = 'none';
    document.getElementById('form-section').style.display = 'block';
    document.getElementById('btn-analyze').disabled       = false;
    document.getElementById('share-section').style.display = 'none';
    document.getElementById('numero-duele').style.display  = 'none';
    document.getElementById('resumen-oferta').style.display = 'none';
    document.getElementById('compare-tabs').style.display  = 'none';
    document.getElementById('ganador-banner').style.display = 'none';
    document.getElementById('asesor-section').style.display = 'none';
    window._compareData = null;
    if (_gaugeInterval) clearInterval(_gaugeInterval);
    offerEl.value = '';
    charNumEl.textContent = '0';
    urlIndicator.style.display = 'none';
    // Reset fields
    ['f-precio','f-entrada','f-cuota','f-meses','f-residual','f-tae','f-vin'].forEach(id => {
      document.getElementById(id).value = '';
    });
    document.getElementById('vin-section').style.display    = 'none';
    document.getElementById('vin-error-section').style.display = 'none';
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  // ── Copy summary ──
  async function copySummary() {
    const btn = document.getElementById('btn-copy');
    try {
      await navigator.clipboard.writeText(window._sfSummary || '');
      btn.innerHTML = '<i class="fa fa-check"></i> ¡Copiado!';
      setTimeout(() => { btn.innerHTML = '<i class="fa fa-copy"></i> Copiar resumen'; }, 2200);
    } catch(e) {
      btn.textContent = 'No soportado';
    }
  }

  // ── Utils ──
  function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }
</script>

</body>
</html>
