<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Analizador de Luz &amp; Gas — SinFiltros</title>
  <meta name="description" content="Pega tu factura o la oferta de tu comercializadora. Calculamos tu coste real, detectamos potencia de más y te decimos sin rodeos si te están timando." />

  <!-- Open Graph / Social sharing -->
  <meta property="og:type"         content="website" />
  <meta property="og:url"          content="https://sinfiltros.es/luz.php" />
  <meta property="og:title"        content="¿Te están timando con la luz? — SinFiltros" />
  <meta property="og:description"  content="Pega tu factura o contrato de luz y gas. Calculamos tu coste real, detectamos potencia de más y cláusulas abusivas. Gratis." />
  <meta property="og:image"        content="https://sinfiltros.es/og-image.png" />
  <meta name="twitter:card"        content="summary_large_image" />
  <meta name="twitter:title"       content="¿Te están timando con la luz? — SinFiltros" />
  <meta name="twitter:description" content="Pega tu factura o contrato de luz y gas. Calculamos tu coste real, detectamos potencia de más y cláusulas abusivas. Gratis." />
  <meta name="twitter:image"       content="https://sinfiltros.es/og-image.png" />

  <!-- Favicon -->
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 40 40'><defs><linearGradient id='g' x1='0' y1='0' x2='1' y2='1'><stop offset='0' stop-color='%238B5CF6'/><stop offset='1' stop-color='%23F97316'/></linearGradient></defs><rect width='40' height='40' rx='10' fill='url(%23g)'/><text x='50%' y='55%' dominant-baseline='middle' text-anchor='middle' font-family='system-ui,sans-serif' font-weight='900' font-size='16' fill='white'>SF</text></svg>" />

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="assets/sf.css">
  <style>
    :root {
      --accent:      #FBBF24;
      --accent-l:    #FCD34D;
      --accent-d:    #92400E;
      --accent-rgb:  251,191,36;
      --hero-mid:    #1C1400;
      --result-bg1:  #FFFBEB;
      --result-bg2:  #FEF3C7;
    }
    /* ── Permanencia banner ── */
    .permanencia-banner {
      margin: 0 32px; padding: 18px 22px;
      background: linear-gradient(135deg, #451A03, #78350F);
      border: 1px solid #F59E0B; border-radius: 14px; margin-bottom: 16px;
    }
    .permanencia-banner-hdr { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; }
    .permanencia-icon {
      width: 32px; height: 32px; background: rgba(245,158,11,0.3);
      border-radius: 9px; display: flex; align-items: center; justify-content: center;
      color: var(--accent-l); flex-shrink: 0;
    }
    .permanencia-title { font-size: 0.85rem; font-weight: 800; color: #FDE68A; }
    .permanencia-detalle { font-size: 0.82rem; color: var(--accent-l); line-height: 1.5; }

    /* ── Potencia block ── */
    .potencia-block {
      margin: 0 32px; padding: 22px 24px; border-radius: 14px; border: 1px solid; margin-bottom: 16px;
    }
    .potencia-block.excesiva     { background: linear-gradient(135deg, #FFF7ED, #FFFBEB); border-color: #FED7AA; }
    .potencia-block.adecuada     { background: linear-gradient(135deg, #F0FDF4, #ECFDF5); border-color: #A7F3D0; }
    .potencia-block.insuficiente { background: linear-gradient(135deg, #FEF2F2, #FFF5F5); border-color: #FCA5A5; }
    .potencia-block.desconocida  { background: #F8FAFC; border-color: var(--border); }
    .potencia-hdr { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; flex-wrap: wrap; }
    .potencia-estado-badge {
      padding: 3px 10px; border-radius: 20px;
      font-size: 0.72rem; font-weight: 800; letter-spacing: 0.06em; text-transform: uppercase;
    }
    .potencia-estado-badge.excesiva     { background: rgba(249,115,22,0.15); color: #C2410C; border: 1px solid rgba(249,115,22,0.3); }
    .potencia-estado-badge.adecuada     { background: rgba(16,185,129,0.12); color: #065F46; border: 1px solid rgba(16,185,129,0.3); }
    .potencia-estado-badge.insuficiente { background: rgba(239,68,68,0.12);  color: #7F1D1D; border: 1px solid rgba(239,68,68,0.3); }
    .potencia-estado-badge.desconocida  { background: #E2E8F0; color: var(--mid); border: 1px solid var(--border); }
    .potencia-ahorro { font-size: 0.8rem; font-weight: 800; margin-left: auto; }
    .potencia-ahorro.excesiva     { color: #C2410C; }
    .potencia-ahorro.adecuada     { color: #065F46; }
    .potencia-ahorro.insuficiente { color: #B91C1C; }
    .potencia-explicacion { font-size: 0.85rem; line-height: 1.55; }
    .potencia-block.excesiva     .potencia-explicacion { color: #92400E; }
    .potencia-block.adecuada     .potencia-explicacion { color: #064E3B; }
    .potencia-block.insuficiente .potencia-explicacion { color: #7F1D1D; }
    .potencia-block.desconocida  .potencia-explicacion { color: var(--mid); }

    @media (max-width: 600px) {
      .permanencia-banner { margin: 0 20px; }
      .potencia-block { margin: 0 20px; }
    }
  </style>
</head>
<body>

<!-- HEADER -->
<?php include '_header.php'; ?>

<!-- HERO -->
<div class="hero">
  <div class="hero-tag"><i class="fa fa-bolt"></i> Luz &amp; Gas — Analiza tu contrato</div>
  <h1>¿Te están robando<br/><span>con la luz?</span></h1>
  <p>Pega tu factura, el texto de la oferta o el enlace. Calculamos tu coste real, detectamos potencia de más y te decimos sin rodeos si te están timando.</p>
</div>

<!-- MAIN CARD -->
<div class="main">
  <div class="card" id="main-card">

    <!-- FORM -->
    <div id="form-section">
      <div class="card-header">
        <h2><i class="fa fa-bolt" style="color:var(--accent);margin-right:8px;"></i>Analiza tu contrato de luz o gas</h2>
        <p>Pega el texto de tu factura, la oferta de la nueva comercializadora o el enlace a tu área de cliente.</p>
      </div>
      <div class="card-body">
        <div class="textarea-wrap">
          <textarea
            id="offer-text"
            placeholder="Opciones:&#10;• Pega el texto de tu factura de la luz&#10;• Pega la oferta de la nueva comercializadora&#10;• Pega el enlace de tu área de cliente&#10;&#10;Ejemplo: &quot;Tarifa fija Endesa 0,18€/kWh, potencia 3,3kW, sin permanencia&quot;"
            maxlength="3000"
          ></textarea>
          <div id="url-indicator" style="display:none; margin-top:8px; padding:10px 14px; background:#FFFBEB; border:1px solid #FDE68A; border-radius:10px; font-size:0.82rem; color:#92400E; align-items:center; gap:8px;">
            <i class="fa fa-link"></i>
            <span>Enlace detectado — intentaremos obtener el contenido de la oferta automáticamente</span>
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
          Añadir datos de mi contrato (opcional, para mayor precisión)
          <i class="fa fa-chevron-down chevron"></i>
        </button>

        <div class="structured-fields" id="structured-fields">
          <div class="fields-label">Datos de tu contrato o factura</div>
          <div class="fields-grid">
            <div class="field-group">
              <label for="f-potencia">Potencia contratada (kW)</label>
              <div class="input-with-suffix">
                <input type="number" id="f-potencia" placeholder="3.3" min="0" max="100" step="0.1" />
                <span class="input-suffix">kW</span>
              </div>
            </div>
            <div class="field-group">
              <label for="f-consumo">Consumo mensual est. (kWh)</label>
              <div class="input-with-suffix">
                <input type="number" id="f-consumo" placeholder="250" min="0" />
                <span class="input-suffix">kWh</span>
              </div>
            </div>
            <div class="field-group">
              <label for="f-kwh">Precio energía (€/kWh)</label>
              <div class="input-with-suffix">
                <input type="number" id="f-kwh" placeholder="0.1650" min="0" step="0.0001" />
                <span class="input-suffix">€/kWh</span>
              </div>
            </div>
            <div class="field-group">
              <label for="f-pot-dia">Precio potencia (€/kW/día)</label>
              <div class="input-with-suffix">
                <input type="number" id="f-pot-dia" placeholder="0.1082" min="0" step="0.0001" />
                <span class="input-suffix">€/kW/d</span>
              </div>
            </div>
            <div class="field-group">
              <label for="f-tipo">Tipo de tarifa</label>
              <select id="f-tipo">
                <option value="">— Selecciona —</option>
                <option value="PVPC">PVPC (mercado regulado)</option>
                <option value="TARIFA_FIJA">Tarifa Fija</option>
                <option value="TARIFA_INDEXADA">Tarifa Indexada</option>
              </select>
            </div>
            <div class="field-group">
              <label for="f-permanencia">Permanencia (meses)</label>
              <div class="input-with-suffix">
                <input type="number" id="f-permanencia" placeholder="0" min="0" max="60" />
                <span class="input-suffix">meses</span>
              </div>
              <span class="field-hint">0 = sin permanencia</span>
            </div>
          </div>
        </div>

        <div id="error-msg"></div>

        <button class="btn-analyze" id="btn-analyze" onclick="analyze()">
          <i class="fa fa-bolt"></i> Analizar mi contrato de luz
        </button>
      </div>
    </div>

    <!-- LOADING -->
    <div id="loading">
      <div class="scan-ring"></div>
      <p>Analizando tu contrato...</p>
      <small>Detectando trampas de potencia, permanencias ocultas y precios abusivos...</small>
    </div>

    <!-- RESULTS -->
    <!-- Ganador banner (compare mode) -->
    <div class="ganador-banner" id="ganador-banner">
      <div class="ganador-label" id="ganador-label"></div>
      <div class="ganador-motivo" id="ganador-motivo"></div>
      <ul class="diferencias-list" id="diferencias-list"></ul>
    </div>

        <div id="results">
      <!-- Print-only header -->
      <div class="print-header">
        <div class="print-logo">SinFiltros — Informe de Luz & Gas</div>
        <div class="print-date" id="print-date"></div>
      </div>

      <!-- EL NÚMERO QUE DUELE -->
      <div class="numero-duele" id="numero-duele">
        <div class="numero-duele-icon">⚡</div>
        <div>
          <span class="numero-duele-amount" id="numero-duele-amount"></span>
          <span class="numero-duele-label" id="numero-duele-label"></span>
        </div>
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
            <div class="tipo-badge" id="tipo-badge"><i class="fa fa-bolt"></i> —</div>
            <div class="veredicto" id="result-veredicto"></div>
            <div class="transparency-label" id="transparency-label"></div>
          </div>
        </div>
      </div>

      <!-- Real numbers -->
      <div class="numbers-block">
        <div class="block-title"><i class="fa fa-calculator" style="color:var(--accent);"></i> Lo que realmente pagas</div>
        <div class="numbers-grid" id="numbers-grid"></div>
      </div>

      <!-- PERMANENCIA BANNER (hidden by default) -->
      <div id="permanencia-banner" class="permanencia-banner" style="display:none;">
        <div class="permanencia-banner-hdr">
          <div class="permanencia-icon"><i class="fa fa-clock"></i></div>
          <span class="permanencia-title">PERMANENCIA: <span id="perm-meses"></span> MESES</span>
        </div>
        <div class="permanencia-detalle" id="perm-penalizacion"></div>
      </div>

      <!-- POTENCIA BLOCK (rendered dynamically) -->
      <div id="potencia-section" style="display:none;"></div>

      <!-- Body sections -->
      <div class="result-body">

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
          <div class="section-hdr"><i class="fa fa-scale-balanced" style="color:var(--orange);"></i> Comparativa honesta</div>
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
          <div class="section-hdr"><i class="fa fa-circle-question" style="color:var(--accent-d);"></i> Preguntas que DEBES hacer antes de firmar</div>
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
        <i class="fa fa-rotate-left" style="margin-right:6px;"></i> Analizar otro contrato
      </button>

    </div><!-- /results -->

  </div><!-- /card -->
</div><!-- /main -->

<!-- EXAMPLE OFFERS -->
<div class="examples-section">
  <div class="examples-label">Ejemplos de contratos habituales</div>
  <div class="examples-grid">
    <div class="example-chip" onclick="loadExample(0)">
      <div class="example-chip-label">Tarifa fija cara</div>
      <div class="example-chip-text">"Tarifa fija Endesa Tempo Estable: 0,18€/kWh. Potencia 4,4kW. Sin permanencia. Consumo: 280 kWh/mes."</div>
    </div>
    <div class="example-chip" onclick="loadExample(1)">
      <div class="example-chip-label">Indexada con permanencia</div>
      <div class="example-chip-text">"Naturgy Tempo Click: PVPC con margen 0,012€/kWh. Potencia 3,3kW. Permanencia 12 meses, penalización 50€."</div>
    </div>
    <div class="example-chip" onclick="loadExample(2)">
      <div class="example-chip-label">Potencia sobredimensionada</div>
      <div class="example-chip-text">"Repsol Luz Equilibrio: precio fijo 0,15€/kWh. Potencia 5,5kW en piso de 80m². Factura media: 85€/mes."</div>
    </div>
  </div>
</div>

<!-- FOOTER -->
<?php include '_footer.php'; ?>

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
      text: 'Tarifa fija Endesa Tempo Estable: 0,18€/kWh. Potencia contratada: 4,4 kW. Término de potencia incluido. Sin permanencia. Mi consumo habitual: 280 kWh/mes.',
      potencia: 4.4, consumo: 280, kwh: 0.18, potDia: 0.1082, tipo: 'TARIFA_FIJA', perm: 0
    },
    {
      text: 'Oferta Naturgy Tempo Click: PVPC con margen de 0,012€/kWh. Potencia 3,3kW. Permanencia 12 meses, penalización de 50€ si cancelas.',
      potencia: 3.3, consumo: 0, kwh: 0, potDia: 0, tipo: 'TARIFA_INDEXADA', perm: 12
    },
    {
      text: 'Repsol Luz Equilibrio: precio fijo 0,15€/kWh. Potencia 5,5kW (tengo piso de 80m²). Factura media que me llega: 85€/mes.',
      potencia: 5.5, consumo: 0, kwh: 0.15, potDia: 0, tipo: 'TARIFA_FIJA', perm: 0
    }
  ];

  function loadExample(i) {
    const e = EXAMPLES[i];
    offerEl.value = e.text;
    charNumEl.textContent = e.text.length;

    const toggle = document.getElementById('expand-toggle');
    const fields = document.getElementById('structured-fields');
    if (!toggle.classList.contains('open')) {
      toggle.classList.add('open');
      fields.classList.add('open');
    }
    if (e.potencia)  document.getElementById('f-potencia').value    = e.potencia;
    if (e.consumo)   document.getElementById('f-consumo').value     = e.consumo;
    if (e.kwh)       document.getElementById('f-kwh').value         = e.kwh;
    if (e.potDia)    document.getElementById('f-pot-dia').value     = e.potDia;
    if (e.tipo)      document.getElementById('f-tipo').value        = e.tipo;
    document.getElementById('f-permanencia').value = e.perm || '';

    document.getElementById('main-card').scrollIntoView({ behavior: 'smooth' });
  }

  // ── Analyze ──
  async function analyze() {
    const offerText  = offerEl.value.trim();
    const potencia   = parseFloat(document.getElementById('f-potencia').value)   || 0;
    const consumo    = parseFloat(document.getElementById('f-consumo').value)    || 0;
    const kwh        = parseFloat(document.getElementById('f-kwh').value)        || 0;
    const potDia     = parseFloat(document.getElementById('f-pot-dia').value)    || 0;
    const tipo       = document.getElementById('f-tipo').value;
    const permanencia = parseInt(document.getElementById('f-permanencia').value) || 0;

    const errEl = document.getElementById('error-msg');
    errEl.style.display = 'none';

    if (!offerText && !fileDataA && !consumo && !kwh) {
      errEl.textContent = 'Pega tu factura o el texto de la oferta, o introduce al menos el consumo y el precio por kWh.';
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
    const LOADING_MSGS = [
      ['Analizando tu contrato de luz...', 'Calculando lo que realmente pagas vs lo que deberías pagar...'],
      ['Buscando la potencia contratada de más...', 'El truco más común de las eléctricas...'],
      ['Detectando cláusulas abusivas...', 'Las que esconden en la letra pequeña del contrato...'],
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
      const res = await fetch('luz-api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          offer_text:          offerText,
          potencia_kw:         potencia,
          consumo_kwh_mes:     consumo,
          precio_kwh:          kwh,
          precio_potencia_dia: potDia,
          tipo_tarifa:         tipo,
          permanencia_meses:   permanencia,
          file_base64:       fileDataA  || undefined,
          file_media_type:   fileTypeA  || undefined,
          compare_mode:      compareMode || undefined,
          offer_text_b:      compareMode ? (document.getElementById('offer-text-b')?.value.trim() || '') : undefined,
          file_base64_b:     compareMode ? (fileDataB  || undefined) : undefined,
          file_media_type_b: compareMode ? (fileTypeB  || undefined) : undefined,
        })
      });

      const data = await res.json();
      clearInterval(msgInterval);
      if (!res.ok || data.error) {
        if (data.url_fetch_failed) {
          throw new Error(data.error);
        }
        throw new Error(data.error || 'Error al analizar el contrato');
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
      // In compare mode, use analisis_a data for rendering the main results
      if (data.analisis_a) {
        Object.assign(data, data.analisis_a);
      }
    } else if (ganadorBanner) {
      ganadorBanner.style.display = 'none';
    }

    document.getElementById('loading').style.display  = 'none';
    document.getElementById('results').style.display  = 'block';

    // Print date
    document.getElementById('print-date').textContent = 'Análisis: ' + new Date().toLocaleDateString('es-ES', {day:'2-digit',month:'long',year:'numeric'});

    // ── EL NÚMERO QUE DUELE ──
    const nums_raw = data.numeros_reales || {};
    const costeAnual = nums_raw.coste_anual_estimado || data.coste_anual_estimado;
    const nuleroDueleEl = document.getElementById('numero-duele');
    const numeroDueleAmount = document.getElementById('numero-duele-amount');
    const numeroDueleLabel  = document.getElementById('numero-duele-label');
    if (costeAnual && parseFloat(costeAnual) > 200) {
      const val = typeof costeAnual === 'string' ? costeAnual : Math.round(costeAnual).toLocaleString('es-ES') + '€';
      numeroDueleAmount.textContent = val;
      numeroDueleLabel.textContent  = 'de coste anual estimado en tu contrato actual';
      nuleroDueleEl.style.display   = 'flex';
    } else {
      nuleroDueleEl.style.display = 'none';
    }

    // ── SHARE BUTTONS ──
    const veredictoText = data.veredicto || 'He analizado mi contrato de luz con SinFiltros';
    const tweetText = encodeURIComponent(`${veredictoText.substring(0,120)}... Analiza el tuyo gratis 👇 #SinFiltros #Luz`);
    const tweetUrl  = encodeURIComponent('https://sinfiltros.es/luz.php');
    const waText    = encodeURIComponent(`${veredictoText.substring(0,140)}...\n\nAnaliza tú también: https://sinfiltros.es/luz.php`);
    document.getElementById('btn-share-twitter').href  = `https://twitter.com/intent/tweet?text=${tweetText}&url=${tweetUrl}`;
    document.getElementById('btn-share-whatsapp').href = `https://wa.me/?text=${waText}`;
    document.getElementById('share-section').style.display = 'block';
    window._sfSummary = `SinFiltros — Análisis de Luz & Gas\n${'─'.repeat(40)}\n${veredictoText}\n\nAnálisis gratuito en: https://sinfiltros.es/luz.php`;

    // Gauge animation
    const score  = Math.max(0, Math.min(100, data.puntuacion_transparencia || 50));
    const circum = 276.46;
    const offset = circum - (score / 100) * circum;
    const color  = score >= 70 ? '#10B981' : score >= 40 ? '#F97316' : '#EF4444';
    const bar    = document.getElementById('gauge-bar');
    const numEl  = document.getElementById('gauge-num');
    bar.style.stroke  = color;
    numEl.style.color = color;
    setTimeout(() => { bar.style.strokeDashoffset = offset; }, 80);
    let n = 0;
    const iv = setInterval(() => {
      n = Math.min(n + 2, score);
      numEl.textContent = n;
      if (n >= score) clearInterval(iv);
    }, 18);

    // Tipo badge
    const tipoLabels = {
      PVPC:            'PVPC (mercado libre)',
      TARIFA_FIJA:     'Tarifa Fija',
      TARIFA_INDEXADA: 'Tarifa Indexada',
      DESCONOCIDO:     'Tipo desconocido'
    };
    const tipoEl = document.getElementById('tipo-badge');
    tipoEl.innerHTML = `<i class="fa fa-bolt"></i> ${tipoLabels[data.tipo_contrato] || data.tipo_contrato || '—'}`;

    document.getElementById('result-veredicto').textContent = data.veredicto || '';

    const transLabel = score >= 70 ? 'Anuncio relativamente transparente' :
                       score >= 40 ? 'Transparencia media' :
                                     'Poca transparencia';
    document.getElementById('transparency-label').textContent = `Transparencia: ${transLabel} (${score}/100)`;

    // Numbers grid
    const nums   = data.numeros_reales || {};
    const gridEl = document.getElementById('numbers-grid');
    gridEl.innerHTML = '';

    const fmt2 = (v) => (v != null) ? parseFloat(v).toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : null;
    const fmt1 = (v) => (v != null) ? parseFloat(v).toLocaleString('es-ES', { minimumFractionDigits: 0, maximumFractionDigits: 1 }) : null;
    const fmt4 = (v) => (v != null) ? parseFloat(v).toLocaleString('es-ES', { minimumFractionDigits: 4, maximumFractionDigits: 4 }) : null;

    const numItems = [
      {
        val: nums.potencia_kw != null ? (fmt1(nums.potencia_kw) + ' kW') : null,
        lbl: 'Potencia contratada', cls: ''
      },
      {
        val: nums.precio_kwh != null ? (fmt4(nums.precio_kwh) + ' €/kWh') : null,
        lbl: 'Precio energía', cls: ''
      },
      {
        val: nums.coste_mensual_estimado != null ? (fmt2(nums.coste_mensual_estimado) + '€') : null,
        lbl: 'Coste mensual est.', cls: ''
      },
      {
        val: nums.coste_anual_estimado != null ? (fmt2(nums.coste_anual_estimado) + '€') : null,
        lbl: 'Coste anual est.', cls: 'total'
      },
      {
        val: (nums.sobrecoste_vs_pvpc_anual != null && nums.sobrecoste_vs_pvpc_anual > 0)
               ? (fmt2(nums.sobrecoste_vs_pvpc_anual) + '€') : null,
        lbl: 'Sobrecoste vs PVPC', cls: 'cost'
      }
    ];

    numItems.forEach(it => {
      if (it.val == null) return;
      gridEl.innerHTML += `
        <div class="number-item ${it.cls}">
          <span class="number-val">${escHtml(it.val)}</span>
          <span class="number-lbl">${escHtml(it.lbl)}</span>
        </div>`;
    });

    // Permanencia banner
    const permBanner = document.getElementById('permanencia-banner');
    const permMeses  = data.permanencia_meses;
    if (permMeses && permMeses > 0) {
      document.getElementById('perm-meses').textContent       = permMeses;
      document.getElementById('perm-penalizacion').textContent = data.penalizacion_salida
        || 'Penalización por salida anticipada. Consulta las condiciones exactas de tu contrato.';
      permBanner.style.display = 'block';
    } else {
      permBanner.style.display = 'none';
    }

    // Potencia block
    const potSec  = document.getElementById('potencia-section');
    const potEval = data.potencia_evaluacion;
    if (potEval && potEval.estado) {
      const estadoKey = potEval.estado.toLowerCase();
      const estadoLabels = {
        excesiva:     'Potencia EXCESIVA',
        adecuada:     'Potencia ADECUADA',
        insuficiente: 'Potencia INSUFICIENTE',
        desconocida:  'Potencia DESCONOCIDA'
      };
      let ahorroStr = '';
      if (potEval.ahorro_potencial_anual != null && potEval.ahorro_potencial_anual > 0) {
        ahorroStr = `Ahorro potencial: ${fmt2(potEval.ahorro_potencial_anual)}€/año`;
      } else if (potEval.recomendacion_kw) {
        ahorroStr = `Recomendado: ${potEval.recomendacion_kw} kW`;
      }

      potSec.innerHTML = `
        <div class="potencia-block ${estadoKey}">
          <div class="potencia-hdr">
            <span class="potencia-estado-badge ${estadoKey}">${escHtml(estadoLabels[estadoKey] || potEval.estado)}</span>
            ${ahorroStr ? `<span class="potencia-ahorro ${estadoKey}">${escHtml(ahorroStr)}</span>` : ''}
          </div>
          <div class="potencia-explicacion">${escHtml(potEval.explicacion || '')}</div>
        </div>`;
      potSec.style.display = 'block';
    } else {
      potSec.style.display = 'none';
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
    const ventajas  = data.ventajas || [];
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
    const comp    = data.comparativa;
    const compSec = document.getElementById('comparativa-section');
    if (comp) {
      compSec.style.display = 'block';
      const compGrid = document.getElementById('comp-grid');
      compGrid.innerHTML = '';
      if (comp.vs_pvpc) {
        compGrid.innerHTML += `
          <div class="comp-row">
            <div class="comp-icon"><i class="fa fa-bolt"></i></div>
            <div class="comp-text"><strong>vs PVPC regulado</strong>${escHtml(comp.vs_pvpc)}</div>
          </div>`;
      }
      if (comp.vs_competidores) {
        compGrid.innerHTML += `
          <div class="comp-row">
            <div class="comp-icon"><i class="fa fa-leaf"></i></div>
            <div class="comp-text"><strong>vs Comercializadoras alternativas</strong>${escHtml(comp.vs_competidores)}</div>
          </div>`;
      }
      if (comp.recomendacion) {
        const recBox  = document.getElementById('recomendacion-box');
        const recText = document.getElementById('recomendacion-text');
        recText.textContent = comp.recomendacion;
        recBox.style.display = 'block';
      }
    } else {
      compSec.style.display = 'none';
    }

    // Preguntas
    const preguntas = data.preguntas_clave || [];
    const pregSec   = document.getElementById('preguntas-section');
    const pregEl    = document.getElementById('preguntas-list');
    pregEl.innerHTML = '';
    if (preguntas.length) {
      pregSec.style.display = 'block';
      preguntas.forEach((p, i) => {
        pregEl.innerHTML += `
          <div class="pregunta-item">
            <div class="pregunta-num">${i + 1}</div>
            <span>${escHtml(p)}</span>
          </div>`;
      });
    } else {
      pregSec.style.display = 'none';
    }

    document.getElementById('main-card').scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  // ── Reset ──
  function resetForm() {
    document.getElementById('results').style.display      = 'none';
    document.getElementById('share-section').style.display = 'none';
    document.getElementById('numero-duele').style.display  = 'none';
    document.getElementById('form-section').style.display = 'block';
    document.getElementById('btn-analyze').disabled       = false;
    offerEl.value = '';
    charNumEl.textContent = '0';
    urlIndicator.style.display = 'none';
    ['f-potencia', 'f-consumo', 'f-kwh', 'f-pot-dia', 'f-permanencia'].forEach(id => {
      document.getElementById(id).value = '';
    });
    document.getElementById('f-tipo').value = '';
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
