<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Analizador de Seguros — SinFiltros</title>
  <meta name="description" content="Pega las condiciones de tu seguro. Descubrimos qué exclusiones te esconden, si pagas de más y si tienes coberturas duplicadas que ya tienes gratis." />

  <!-- Open Graph / Social sharing -->
  <meta property="og:type"         content="website" />
  <meta property="og:url"          content="https://sinfiltros.es/seguros.php" />
  <meta property="og:title"        content="¿Pagas de más por tu seguro? — SinFiltros" />
  <meta property="og:description"  content="Pega tu póliza y descubrimos qué cubre de verdad, qué exclusiones te esconden y si estás pagando de más. Gratis." />
  <meta property="og:image"        content="https://sinfiltros.es/og-image.png" />
  <meta name="twitter:card"        content="summary_large_image" />
  <meta name="twitter:title"       content="¿Pagas de más por tu seguro? — SinFiltros" />
  <meta name="twitter:description" content="Pega tu póliza y descubrimos qué cubre de verdad, qué exclusiones te esconden y si estás pagando de más. Gratis." />
  <meta name="twitter:image"       content="https://sinfiltros.es/og-image.png" />

  <!-- Favicon -->
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 40 40'><defs><linearGradient id='g' x1='0' y1='0' x2='1' y2='1'><stop offset='0' stop-color='%238B5CF6'/><stop offset='1' stop-color='%23F97316'/></linearGradient></defs><rect width='40' height='40' rx='10' fill='url(%23g)'/><text x='50%' y='55%' dominant-baseline='middle' text-anchor='middle' font-family='system-ui,sans-serif' font-weight='900' font-size='16' fill='white'>SF</text></svg>" />

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="assets/sf.css">
  <style>
    :root {
      --accent:      #10B981;
      --accent-l:    #34D399;
      --accent-d:    #065F46;
      --accent-rgb:  16,185,129;
      --hero-mid:    #052E16;
      --result-bg1:  #ECFDF5;
      --result-bg2:  #F0FDF4;
      --amber:       #F59E0B;
      --purple:      #8B5CF6;
      --purple-l:    #A78BFA;
    }
    /* ── Aseguradora label ── */
    .aseguradora-label { font-size: 0.78rem; color: var(--mid); margin-top: 4px; font-weight: 500; }

    /* ── Number items (seguros uses highlight/warn instead of total/cost) ── */
    .number-item.highlight {
      background: linear-gradient(135deg, #052E16, #065F46); border-color: transparent;
    }
    .number-item.warn {
      background: linear-gradient(135deg, #FEF2F2, #FFF5F5); border-color: #FCA5A5;
    }
    .number-item.highlight .number-val { color: white; }
    .number-item.warn .number-val  { color: var(--red); }
    .number-item.highlight .number-lbl { color: rgba(255,255,255,0.55); }
    .number-item.warn .number-lbl  { color: #EF4444; }

    /* ── Hipoteca vinculada banner ── */
    .hip-banner {
      margin: 0 32px; padding: 20px 22px;
      background: linear-gradient(135deg, #451A03, #92400E);
      border: 1px solid #F59E0B; border-radius: 0;
    }
    .hip-banner-hdr { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
    .hip-banner-icon {
      width: 36px; height: 36px; background: rgba(245,158,11,0.25);
      border-radius: 10px; display: flex; align-items: center; justify-content: center;
      font-size: 1rem; color: #FCD34D; flex-shrink: 0;
    }
    .hip-banner-title { font-size: 0.88rem; font-weight: 800; color: #FDE68A; letter-spacing: -0.01em; }
    .hip-banner-body { font-size: 0.83rem; color: #FCD34D; line-height: 1.55; }
    .hip-sobrecoste {
      display: inline-block; margin-top: 8px; padding: 6px 14px; border-radius: 8px;
      background: rgba(245,158,11,0.2); border: 1px solid rgba(245,158,11,0.4);
      font-size: 0.82rem; font-weight: 700; color: #FBBF24;
    }
    .hip-law {
      margin-top: 10px; padding: 10px 14px; background: rgba(0,0,0,0.2); border-radius: 8px;
      font-size: 0.78rem; color: #FDE68A; line-height: 1.5;
    }

    /* ── Exclusiones criticas ── */
    .exclusiones-block { margin: 20px 32px 0; }
    .exclusion-list { display: flex; flex-direction: column; gap: 10px; }
    .exclusion-item {
      display: flex; gap: 14px; padding: 14px 16px; border-radius: 12px;
      background: #FEF2F2; border: 1px solid #FCA5A5;
    }
    .exclusion-icon {
      width: 28px; height: 28px; background: #FCA5A5; border-radius: 7px; flex-shrink: 0;
      display: flex; align-items: center; justify-content: center;
      color: var(--red); font-size: 0.78rem;
    }
    .exclusion-text { font-size: 0.875rem; color: #7F1D1D; line-height: 1.55; }

    /* ── Coberturas duplicadas ── */
    .duplicadas-block { margin: 20px 32px 0; }
    .duplicada-list { display: flex; flex-direction: column; gap: 10px; }
    .duplicada-item {
      display: flex; gap: 14px; padding: 14px 16px; border-radius: 12px;
      background: #FFFBEB; border: 1px solid #FDE68A;
    }
    .duplicada-icon {
      width: 28px; height: 28px; background: #FDE68A; border-radius: 7px; flex-shrink: 0;
      display: flex; align-items: center; justify-content: center;
      color: #92400E; font-size: 0.78rem;
    }
    .duplicada-text { font-size: 0.875rem; color: #78350F; line-height: 1.55; }

    @media (max-width: 600px) {
      .hip-banner { margin: 0 20px; }
      .exclusiones-block { margin: 20px 20px 0; }
      .duplicadas-block { margin: 20px 20px 0; }
    }
  </style>
</head>
<body>

<!-- HEADER -->
<?php include '_header.php'; ?>

<!-- HERO -->
<div class="hero">
  <div class="hero-tag"><i class="fa fa-umbrella"></i> Seguros — Analiza tu póliza</div>
  <h1>¿Tu seguro cubre<br/><span>lo que crees?</span></h1>
  <p>Pega las condiciones de tu seguro. Descubrimos qué exclusiones te esconden, si pagas de más y si tienes coberturas duplicadas que ya tienes gratis.</p>
</div>

<!-- MAIN CARD -->
<div class="main">
  <div class="card" id="main-card">

    <!-- FORM -->
    <div id="form-section">
      <div class="card-header">
        <h2><i class="fa fa-umbrella" style="color:var(--accent);margin-right:8px;"></i>Analiza tu póliza o condiciones de seguro</h2>
        <p>Pega el texto de las condiciones, el email de renovación o la oferta de un seguro nuevo.</p>
      </div>
      <div class="card-body">
        <textarea
          id="offer-text"
          placeholder="Opciones:&#10;• Pega el texto de tu póliza o condiciones generales&#10;• Pega el email de renovación con las coberturas&#10;• Pega el texto de la oferta de un seguro nuevo&#10;&#10;Ejemplo: &quot;Seguro de hogar BBVA: prima 580€/año, continente 120.000€, contenido 30.000€, RC 300.000€. Franquicia 300€. Seguro vinculado a hipoteca.&quot;"
          maxlength="4000"
        ></textarea>
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
        <div class="char-count"><span id="char-num">0</span> / 4000 caracteres</div>

        <!-- Toggle optional fields -->
        <button class="expand-toggle" id="expand-toggle" onclick="toggleFields()">
          <i class="fa fa-sliders"></i>
          Añadir datos de mi póliza (opcional)
          <i class="fa fa-chevron-down chevron"></i>
        </button>

        <div class="structured-fields" id="structured-fields">
          <div class="fields-label">Datos de la póliza</div>
          <div class="fields-grid">
            <div class="field-group">
              <label for="f-tipo">Tipo de seguro</label>
              <select id="f-tipo">
                <option value="">No sé / detectar automáticamente</option>
                <option value="HOGAR">Hogar</option>
                <option value="VIDA">Vida</option>
                <option value="COCHE">Coche</option>
                <option value="SALUD">Salud</option>
                <option value="MULTIRRIESGO">Multirriesgo</option>
                <option value="OTRO">Otro</option>
              </select>
            </div>
            <div class="field-group">
              <label for="f-prima">Prima anual (€/año)</label>
              <div class="input-with-suffix">
                <input type="number" id="f-prima" placeholder="450" min="0" step="0.01" />
                <span class="input-suffix">€/año</span>
              </div>
            </div>
            <div class="field-group">
              <label for="f-capital">Capital asegurado (€)</label>
              <div class="input-with-suffix">
                <input type="number" id="f-capital" placeholder="150000" min="0" />
                <span class="input-suffix">€</span>
              </div>
              <span class="field-hint">Valor total asegurado</span>
            </div>
            <div class="field-group">
              <label for="f-hipoteca">¿Vinculado a hipoteca?</label>
              <select id="f-hipoteca">
                <option value="">No</option>
                <option value="si">Sí, lo exige el banco</option>
              </select>
            </div>
          </div>
        </div>

        <div id="error-msg"></div>

        <button class="btn-analyze" id="btn-analyze" onclick="analyze()">
          <i class="fa fa-umbrella"></i> Analizar mi seguro
        </button>
      </div>
    </div>

    <!-- LOADING -->
    <div id="loading">
      <div class="scan-ring"></div>
      <p>Analizando tu póliza...</p>
      <small>Detectando exclusiones ocultas, coberturas duplicadas y sobreprecio...</small>
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
        <div class="print-logo">SinFiltros — Informe de Seguros</div>
        <div class="print-date" id="print-date"></div>
      </div>

      <!-- EL NÚMERO QUE DUELE -->
      <div class="numero-duele" id="numero-duele">
        <div class="numero-duele-icon">🛡️</div>
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
            <div class="tipo-badge" id="tipo-badge"><i class="fa fa-umbrella"></i> —</div>
            <div class="veredicto" id="result-veredicto"></div>
            <div class="transparency-label" id="transparency-label"></div>
            <div class="aseguradora-label" id="aseguradora-label" style="display:none;"></div>
          </div>
        </div>
      </div>

      <!-- VINCULADO HIPOTECA BANNER (hidden by default) -->
      <div id="hip-banner" class="hip-banner" style="display:none;">
        <div class="hip-banner-hdr">
          <div class="hip-banner-icon"><i class="fa fa-triangle-exclamation"></i></div>
          <div class="hip-banner-title">SEGURO VINCULADO A HIPOTECA — Tienes derecho a cambiarlo</div>
        </div>
        <div class="hip-banner-body">
          <span id="hip-sobrecoste-text"></span>
          <div class="hip-law">
            <i class="fa fa-scale-balanced" style="margin-right:6px;"></i>
            La Ley 5/2019 te da derecho a contratar con cualquier aseguradora. El banco no puede penalizarte ni empeorar las condiciones de tu hipoteca si el seguro externo tiene cobertura equivalente.
          </div>
        </div>
      </div>

      <!-- Real numbers -->
      <div class="numbers-block">
        <div class="block-title"><i class="fa fa-calculator" style="color:var(--accent);"></i> Números reales de tu póliza</div>
        <div class="numbers-grid" id="numbers-grid"></div>
      </div>

      <!-- EXCLUSIONES CRITICAS -->
      <div id="exclusiones-section" class="exclusiones-block" style="display:none;">
        <div class="section-hdr"><i class="fa fa-ban" style="color:var(--red);"></i> Exclusiones Críticas que Debes Conocer</div>
        <div class="exclusion-list" id="exclusion-list"></div>
      </div>

      <!-- COBERTURAS DUPLICADAS -->
      <div id="duplicadas-section" class="duplicadas-block" style="display:none;">
        <div class="section-hdr"><i class="fa fa-copy" style="color:var(--amber);"></i> Coberturas que Probablemente Ya Tienes Gratis</div>
        <div class="duplicada-list" id="duplicada-list"></div>
      </div>

      <!-- Body sections -->
      <div class="result-body">

        <!-- TRAMPAS -->
        <div id="trampas-section" style="display:none;">
          <div class="section-hdr"><i class="fa fa-triangle-exclamation" style="color:var(--red);"></i> Trampas y puntos de atención</div>
          <div class="trampa-list" id="trampa-list"></div>
        </div>

        <!-- VENTAJAS -->
        <div id="ventajas-section" style="display:none;">
          <div class="section-hdr"><i class="fa fa-circle-check" style="color:var(--accent);"></i> Ventajas reales de esta póliza</div>
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
          <div class="section-hdr"><i class="fa fa-circle-question" style="color:var(--accent);"></i> Preguntas que DEBES hacer antes de firmar</div>
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
        <i class="fa fa-rotate-left" style="margin-right:6px;"></i> Analizar otra póliza
      </button>

    </div><!-- /results -->

  </div><!-- /card -->
</div><!-- /main -->

<!-- EXAMPLES -->
<div class="examples-section">
  <div class="examples-label">Ejemplos de seguros típicos</div>
  <div class="examples-grid">
    <div class="example-chip" onclick="loadExample(0)">
      <div class="example-chip-label">Hogar vinculado a hipoteca</div>
      <div class="example-chip-text">"Seguro de hogar Santander: prima 580€/año, continente 120.000€, contenido 30.000€. Franquicia 300€. Vinculado a hipoteca, el banco dijo que era obligatorio."</div>
    </div>
    <div class="example-chip" onclick="loadExample(1)">
      <div class="example-chip-label">Coche todo riesgo</div>
      <div class="example-chip-text">"Seguro coche todo riesgo con Allianz: 1.100€/año, sin franquicia, asistencia 24h, coche de sustitución 15 días, lunas sin franquicia. VW Golf 2020."</div>
    </div>
    <div class="example-chip" onclick="loadExample(2)">
      <div class="example-chip-label">Salud privado</div>
      <div class="example-chip-text">"Sanitas Más Salud: 98€/mes (1.176€/año). Médico general, especialistas, urgencias 24h, hospitalización. Sin copago. Sin límite de visitas."</div>
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

  // ── Char counter ──
  const offerEl   = document.getElementById('offer-text');
  const charNumEl = document.getElementById('char-num');

  offerEl.addEventListener('input', () => {
    charNumEl.textContent = offerEl.value.length;
  });

  // ── Toggle optional fields ──
  function toggleFields() {
    const toggle = document.getElementById('expand-toggle');
    const fields = document.getElementById('structured-fields');
    toggle.classList.toggle('open');
    fields.classList.toggle('open');
  }

  // ── Examples ──
  const EXAMPLES = [
    {
      text: 'Seguro de hogar del banco Santander. Prima: 580€/año. Continente: 120.000€, contenido 30.000€. Franquicia 300€. Está vinculado a mi hipoteca. El banco me dijo que era obligatorio contratar con ellos.',
      tipo: 'HOGAR', prima: 580, capital: 150000, hipoteca: 'si'
    },
    {
      text: 'Seguro de coche todo riesgo con Allianz. Prima: 1.100€/año. Coberturas: todo riesgo sin franquicia, asistencia 24h, coche de sustitución 15 días, lunas sin franquicia. Vehículo: Volkswagen Golf 2020.',
      tipo: 'COCHE', prima: 1100, capital: 0, hipoteca: ''
    },
    {
      text: 'Seguro de salud privado con Sanitas Más Salud. Prima: 98€/mes (1.176€/año). Incluye médico general, especialistas, urgencias 24h, hospitalización. Sin copago. Sin límite de visitas. Cuadro médico España.',
      tipo: 'SALUD', prima: 1176, capital: 0, hipoteca: ''
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
    document.getElementById('f-tipo').value     = e.tipo    || '';
    document.getElementById('f-prima').value    = e.prima   || '';
    document.getElementById('f-capital').value  = e.capital || '';
    document.getElementById('f-hipoteca').value = e.hipoteca || '';

    document.getElementById('main-card').scrollIntoView({ behavior: 'smooth' });
  }

  // ── Analyze ──
  async function analyze() {
    const offerText = offerEl.value.trim();
    const tipo      = document.getElementById('f-tipo').value;
    const prima     = parseFloat(document.getElementById('f-prima').value)   || 0;
    const capital   = parseFloat(document.getElementById('f-capital').value) || 0;
    const hipoteca  = document.getElementById('f-hipoteca').value === 'si';

    const errEl = document.getElementById('error-msg');
    errEl.style.display = 'none';

    if (!offerText && !fileDataA) {
      errEl.textContent = 'Pega el texto de tu póliza o las condiciones del seguro.';
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
      ['Analizando tu póliza de seguro...', 'Buscando las exclusiones que no te explicaron...'],
      ['Calculando la prima anual real...', 'Comparando con alternativas del mercado...'],
      ['Detectando coberturas duplicadas...', 'Las que ya tienes gratis con tu banco o tarjeta...'],
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
      const res = await fetch('seguros-api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          offer_text:    offerText,
          tipo_seguro:   tipo,
          prima_anual:   prima,
          capital_aseg:  capital,
          vinculado_hip: hipoteca,
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
        if (data.url_fetch_failed) throw new Error(data.error);
        throw new Error(data.error || 'Error al analizar la póliza');
      }

      renderResults(data);

    } catch (e) {
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
    const primaAnual = nums_raw.prima_anual || data.prima_anual;
    const nuleroDueleEl = document.getElementById('numero-duele');
    const numeroDueleAmount = document.getElementById('numero-duele-amount');
    const numeroDueleLabel  = document.getElementById('numero-duele-label');
    if (primaAnual && parseFloat(primaAnual) > 100) {
      const val = typeof primaAnual === 'string' ? primaAnual : Math.round(primaAnual).toLocaleString('es-ES') + '€';
      numeroDueleAmount.textContent = val;
      numeroDueleLabel.textContent  = 'de prima anual — lo que pagas cada año por este seguro';
      nuleroDueleEl.style.display   = 'flex';
    } else {
      nuleroDueleEl.style.display = 'none';
    }

    // ── SHARE BUTTONS ──
    const veredictoText = data.veredicto || 'He analizado mi seguro con SinFiltros';
    const tweetText = encodeURIComponent(`${veredictoText.substring(0,120)}... Analiza el tuyo gratis 👇 #SinFiltros #Seguros`);
    const tweetUrl  = encodeURIComponent('https://sinfiltros.es/seguros.php');
    const waText    = encodeURIComponent(`${veredictoText.substring(0,140)}...\n\nAnaliza tú también: https://sinfiltros.es/seguros.php`);
    document.getElementById('btn-share-twitter').href  = `https://twitter.com/intent/tweet?text=${tweetText}&url=${tweetUrl}`;
    document.getElementById('btn-share-whatsapp').href = `https://wa.me/?text=${waText}`;
    document.getElementById('share-section').style.display = 'block';
    window._sfSummary = `SinFiltros — Análisis de Seguro\n${'─'.repeat(40)}\n${veredictoText}\n\nAnálisis gratuito en: https://sinfiltros.es/seguros.php`;

    // Gauge animation
    const score  = Math.max(0, Math.min(100, data.puntuacion_transparencia || 50));
    const circum = 276.46;
    const offset = circum - (score / 100) * circum;
    const color  = score >= 70 ? '#10B981' : score >= 40 ? '#F97316' : '#EF4444';
    const bar    = document.getElementById('gauge-bar');
    const numEl  = document.getElementById('gauge-num');
    bar.style.stroke = color;
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
      HOGAR:        'Seguro de Hogar',
      VIDA:         'Seguro de Vida',
      COCHE:        'Seguro de Coche',
      SALUD:        'Seguro de Salud',
      MULTIRRIESGO: 'Seguro Multirriesgo',
      OTRO:         'Otro seguro'
    };
    const tipoEl = document.getElementById('tipo-badge');
    tipoEl.innerHTML = `<i class="fa fa-umbrella"></i> ${tipoLabels[data.tipo_seguro] || data.tipo_seguro || '—'}`;

    document.getElementById('result-veredicto').textContent = data.veredicto || '';

    const transLabel = score >= 70 ? 'Póliza relativamente transparente' :
                       score >= 40 ? 'Transparencia media' :
                                     'Poca transparencia — revisa con cuidado';
    document.getElementById('transparency-label').textContent = `Transparencia: ${transLabel} (${score}/100)`;

    // Aseguradora
    const asegEl = document.getElementById('aseguradora-label');
    if (data.aseguradora) {
      asegEl.textContent = 'Aseguradora: ' + data.aseguradora;
      asegEl.style.display = 'block';
    } else {
      asegEl.style.display = 'none';
    }

    // VINCULADO HIPOTECA BANNER
    const hipBanner   = document.getElementById('hip-banner');
    const hipSobreTxt = document.getElementById('hip-sobrecoste-text');
    if (data.vinculado_hipoteca) {
      hipBanner.style.display = 'block';
      let sobreText = 'Tu banco te ha vinculado este seguro a la hipoteca. Tienes pleno derecho a contratar la misma cobertura con una aseguradora libre y ahorrarte cientos de euros al año.';
      if (data.sobrecoste_vinculacion_anual && data.sobrecoste_vinculacion_anual > 0) {
        sobreText += `<br/><span class="hip-sobrecoste"><i class="fa fa-arrow-trend-up" style="margin-right:5px;"></i>Coste extra estimado vs mercado: <strong>${Math.round(data.sobrecoste_vinculacion_anual).toLocaleString('es-ES')}€/año</strong></span>`;
      }
      hipSobreTxt.innerHTML = sobreText;
    } else {
      hipBanner.style.display = 'none';
    }

    // Numbers grid
    const nums   = data.numeros_reales || {};
    const gridEl = document.getElementById('numbers-grid');
    gridEl.innerHTML = '';

    const fmtEur = (v, dec) => {
      if (v == null) return null;
      dec = dec != null ? dec : 0;
      return parseFloat(v).toLocaleString('es-ES', { minimumFractionDigits: dec, maximumFractionDigits: dec }) + '€';
    };
    const fmtPct = (v, dec) => {
      if (v == null) return null;
      dec = dec != null ? dec : 4;
      return parseFloat(v).toLocaleString('es-ES', { minimumFractionDigits: dec, maximumFractionDigits: dec }) + '%';
    };

    const numItems = [
      { key: 'prima_anual',            label: 'Prima Anual',       fmt: v => fmtEur(v, 0),  cls: 'highlight' },
      { key: 'prima_mensual',          label: 'Prima Mensual',     fmt: v => fmtEur(v, 2),  cls: '' },
      { key: 'capital_asegurado',      label: 'Capital Asegurado', fmt: v => fmtEur(v, 0),  cls: '' },
      { key: 'franquicia_euros',       label: 'Franquicia',        fmt: v => fmtEur(v, 0),  cls: 'warn' },
      { key: 'tasa_sobre_capital_pct', label: 'Prima / Capital',   fmt: v => fmtPct(v, 4),  cls: '' },
    ];

    numItems.forEach(it => {
      const val       = nums[it.key];
      const formatted = it.fmt(val);
      if (formatted == null) return;
      gridEl.innerHTML += `
        <div class="number-item ${it.cls}">
          <span class="number-val">${escHtml(formatted)}</span>
          <span class="number-lbl">${escHtml(it.label)}</span>
        </div>`;
    });

    // Exclusiones criticas
    const exclusiones   = data.exclusiones_criticas || [];
    const exclusionSec  = document.getElementById('exclusiones-section');
    const exclusionList = document.getElementById('exclusion-list');
    exclusionList.innerHTML = '';
    if (exclusiones.length > 0) {
      exclusionSec.style.display = 'block';
      exclusiones.forEach(ex => {
        exclusionList.innerHTML += `
          <div class="exclusion-item">
            <div class="exclusion-icon"><i class="fa fa-ban"></i></div>
            <div class="exclusion-text">${escHtml(ex)}</div>
          </div>`;
      });
    } else {
      exclusionSec.style.display = 'none';
    }

    // Coberturas duplicadas
    const duplicadas    = data.coberturas_duplicadas || [];
    const duplicadasSec = document.getElementById('duplicadas-section');
    const duplicadaList = document.getElementById('duplicada-list');
    duplicadaList.innerHTML = '';
    if (duplicadas.length > 0) {
      duplicadasSec.style.display = 'block';
      duplicadas.forEach(d => {
        duplicadaList.innerHTML += `
          <div class="duplicada-item">
            <div class="duplicada-icon"><i class="fa fa-copy"></i></div>
            <div class="duplicada-text">${escHtml(d)}</div>
          </div>`;
      });
    } else {
      duplicadasSec.style.display = 'none';
    }

    // Trampas
    const trampas  = data.trampa || [];
    const trampaSec = document.getElementById('trampas-section');
    const trampaEl  = document.getElementById('trampa-list');
    trampaEl.innerHTML = '';
    if (trampas.length > 0) {
      trampaSec.style.display = 'block';
      trampas.forEach(t => {
        trampaEl.innerHTML += `
          <div class="trampa-item">
            <div class="trampa-icon"><i class="fa fa-triangle-exclamation"></i></div>
            <div class="trampa-text">${escHtml(t)}</div>
          </div>`;
      });
    } else {
      trampaSec.style.display = 'none';
    }

    // Ventajas
    const ventajas   = data.ventajas || [];
    const ventajaSec = document.getElementById('ventajas-section');
    const ventajaEl  = document.getElementById('ventaja-list');
    ventajaEl.innerHTML = '';
    if (ventajas.length > 0) {
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
      if (comp.vs_mercado) {
        compGrid.innerHTML += `
          <div class="comp-row">
            <div class="comp-icon"><i class="fa fa-scale-balanced"></i></div>
            <div class="comp-text"><strong>vs Mercado libre</strong>${escHtml(comp.vs_mercado)}</div>
          </div>`;
      }
      if (comp.alternativas) {
        compGrid.innerHTML += `
          <div class="comp-row">
            <div class="comp-icon"><i class="fa fa-building"></i></div>
            <div class="comp-text"><strong>Alternativas concretas</strong>${escHtml(comp.alternativas)}</div>
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
    if (preguntas.length > 0) {
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
    document.getElementById('f-tipo').value     = '';
    document.getElementById('f-prima').value    = '';
    document.getElementById('f-capital').value  = '';
    document.getElementById('f-hipoteca').value = '';
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
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }
</script>

</body>
</html>
