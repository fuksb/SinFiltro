<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Telefonía &amp; Internet — SinFiltros</title>
  <meta name="description" content="Analiza tu tarifa de móvil o fibra. Calculamos el precio real, la permanencia que te encadena y te decimos qué deberías pagar. IA brutalmente honesta." />

  <!-- Open Graph / Social sharing -->
  <meta property="og:type"         content="website" />
  <meta property="og:url"          content="https://sinfiltros.es/telco.php" />
  <meta property="og:title"        content="¿Te están timando con el móvil o internet? — SinFiltros" />
  <meta property="og:description"  content="Analiza tu tarifa de móvil o fibra. Calculamos el precio real, la permanencia que te encadena y lo que deberías pagar. Gratis." />
  <meta property="og:image"        content="https://sinfiltros.es/og-image.png" />
  <meta name="twitter:card"        content="summary_large_image" />
  <meta name="twitter:title"       content="¿Te están timando con el móvil o internet? — SinFiltros" />
  <meta name="twitter:description" content="Analiza tu tarifa de móvil o fibra. Calculamos el precio real, la permanencia que te encadena y lo que deberías pagar. Gratis." />
  <meta name="twitter:image"       content="https://sinfiltros.es/og-image.png" />

  <!-- Favicon -->
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 40 40'><defs><linearGradient id='g' x1='0' y1='0' x2='1' y2='1'><stop offset='0' stop-color='%238B5CF6'/><stop offset='1' stop-color='%23F97316'/></linearGradient></defs><rect width='40' height='40' rx='10' fill='url(%23g)'/><text x='50%' y='55%' dominant-baseline='middle' text-anchor='middle' font-family='system-ui,sans-serif' font-weight='900' font-size='16' fill='white'>SF</text></svg>" />

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="assets/sf.css">
  <style>
    :root {
      --accent:      #06B6D4;
      --accent-l:    #22D3EE;
      --accent-d:    #0E7490;
      --accent-rgb:  6,182,212;
      --hero-mid:    #061622;
      --result-bg1:  #ECFEFF;
      --result-bg2:  #F0FDFF;
    }
    /* ── Price rise banner ── */
    .price-rise-banner {
      margin: 20px 32px 0; padding: 18px 22px;
      background: linear-gradient(135deg, #431407, #7C2D12);
      border: 1px solid #F97316; border-radius: 14px;
      display: flex; align-items: center; gap: 16px;
    }
    .price-rise-icon {
      width: 40px; height: 40px; background: rgba(249,115,22,0.25);
      border-radius: 10px; display: flex; align-items: center; justify-content: center;
      font-size: 1.1rem; color: #FB923C; flex-shrink: 0;
    }
    .price-rise-text { font-size: 0.9rem; font-weight: 800; color: #FDBA74; letter-spacing: 0.02em; }
    .price-rise-text small {
      display: block; font-size: 0.75rem; font-weight: 500;
      color: rgba(253,186,116,0.7); margin-top: 2px; letter-spacing: 0;
    }

    /* ── number-item.total for telco ── */
    .number-item.total { background: linear-gradient(135deg, #083344, #0E7490); }

    @media (max-width: 600px) {
      .price-rise-banner { margin: 16px 20px 0; }
    }
  </style>
</head>
<body>

<!-- HEADER -->
<?php include '_header.php'; ?>

<!-- HERO -->
<div class="hero">
  <div class="hero-tag"><i class="fa fa-mobile-screen"></i> Telefonía &amp; Internet — Analiza tu oferta</div>
  <h1>¿Cuánto te cuesta<br/><span>de verdad?</span></h1>
  <p>Pega el texto de la oferta de móvil o fibra. Calculamos el precio real, la permanencia que te encadena y te decimos qué deberías pagar realmente.</p>
</div>

<!-- MAIN CARD -->
<div class="main">
  <div class="card" id="main-card">

    <!-- FORM -->
    <div id="form-section">
      <div class="card-header">
        <h2><i class="fa fa-mobile-screen" style="color:var(--cyan);margin-right:8px;"></i>Analiza cualquier tarifa de móvil o fibra</h2>
        <p>Pega el texto de la oferta, el email comercial del operador o una captura de pantalla transcrita.</p>
      </div>
      <div class="card-body">
        <textarea
          id="offer-text"
          placeholder="Opciones:&#10;• Pega el texto de la oferta del operador&#10;• Pega el email comercial o captura de pantalla transcrita&#10;&#10;Ejemplo: &quot;Movistar Fusión: fibra 600Mb + 2 móviles ilimitados. 65€/mes los primeros 6 meses, luego 85€/mes. Permanencia 24 meses.&quot;"
          maxlength="3000"
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
        <div class="char-count"><span id="char-num">0</span> / 3000 caracteres</div>

        <button class="expand-toggle" id="expand-toggle" onclick="toggleFields()">
          <i class="fa fa-sliders"></i>
          Añadir datos de la oferta (opcional, para mayor precisión)
          <i class="fa fa-chevron-down chevron"></i>
        </button>

        <div class="structured-fields" id="structured-fields">
          <div class="fields-label">Datos de la oferta</div>
          <div class="fields-grid">
            <div class="field-group">
              <label for="f-precio-promo">Precio mensual promo</label>
              <div class="input-with-suffix">
                <input type="number" id="f-precio-promo" placeholder="49" min="0" step="0.01" />
                <span class="input-suffix">€/mes</span>
              </div>
            </div>
            <div class="field-group">
              <label for="f-precio-despues">Precio después de permanencia</label>
              <div class="input-with-suffix">
                <input type="number" id="f-precio-despues" placeholder="65" min="0" step="0.01" />
                <span class="input-suffix">€/mes</span>
              </div>
              <span class="field-hint">Precio cuando acabe la promo</span>
            </div>
            <div class="field-group">
              <label for="f-permanencia">Meses de permanencia</label>
              <input type="number" id="f-permanencia" placeholder="12" min="0" max="60" />
              <span class="field-hint">0 = sin permanencia</span>
            </div>
            <div class="field-group">
              <label for="f-velocidad">Velocidad fibra (Mb)</label>
              <div class="input-with-suffix">
                <input type="number" id="f-velocidad" placeholder="600" min="0" />
                <span class="input-suffix">Mb</span>
              </div>
            </div>
            <div class="field-group">
              <label for="f-gb">GB datos móvil (por línea)</label>
              <div class="input-with-suffix">
                <input type="number" id="f-gb" placeholder="50" min="0" step="0.1" />
                <span class="input-suffix">GB</span>
              </div>
              <span class="field-hint">GB de datos por línea móvil</span>
            </div>
            <div class="field-group">
              <label for="f-lineas">Número de líneas móvil</label>
              <input type="number" id="f-lineas" placeholder="1" min="1" max="10" />
            </div>
          </div>
        </div>

        <div id="error-msg"></div>

        <button class="btn-analyze" id="btn-analyze" onclick="analyze()">
          <i class="fa fa-mobile-screen"></i> Analizar oferta ahora
        </button>
      </div>
    </div>

    <!-- LOADING -->
    <div id="loading">
      <div class="scan-ring"></div>
      <p>Analizando la oferta...</p>
      <small>Calculando permanencia real, precio post-promo y comparando con Digi...</small>
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
        <div class="print-logo">SinFiltros — Informe de Telefonía & Internet</div>
        <div class="print-date" id="print-date"></div>
      </div>

      <!-- EL NÚMERO QUE DUELE -->
      <div class="numero-duele" id="numero-duele">
        <div class="numero-duele-icon">📱</div>
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
            <div class="tipo-badge" id="tipo-badge"><i class="fa fa-mobile-screen"></i> —</div>
            <div class="veredicto" id="result-veredicto"></div>
            <div class="transparency-label" id="transparency-label"></div>
          </div>
        </div>
      </div>

      <!-- Real numbers -->
      <div class="numbers-block">
        <div class="block-title"><i class="fa fa-calculator" style="color:var(--cyan);"></i> Los números reales de tu tarifa</div>
        <div class="numbers-grid" id="numbers-grid"></div>
      </div>

      <!-- PRICE RISE BANNER (hidden by default) -->
      <div id="price-rise-banner" class="price-rise-banner" style="display:none;">
        <div class="price-rise-icon"><i class="fa fa-arrow-trend-up"></i></div>
        <div class="price-rise-text">
          <span id="price-rise-text">SUBIDA DE PRECIO TRAS PERMANENCIA</span>
          <small id="price-rise-sub"></small>
        </div>
      </div>

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
          <div class="section-hdr"><i class="fa fa-scale-balanced" style="color:#F97316;"></i> Comparativa honesta</div>
          <div class="comparativa-block">
            <div class="comp-grid" id="comp-grid"></div>
            <div class="recomendacion-box" id="recomendacion-box" style="display:none;">
              <strong><i class="fa fa-lightbulb"></i> Qué haría alguien inteligente</strong>
              <span id="recomendacion-text"></span>
            </div>
          </div>
        </div>

        <!-- PREGUNTAS -->
        <div id="preguntas-section" style="display:none;">
          <div class="section-hdr"><i class="fa fa-circle-question" style="color:var(--cyan);"></i> Preguntas que DEBES hacer antes de firmar</div>
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

    </div><!-- /results -->

  </div><!-- /card -->
</div><!-- /main -->

<!-- EXAMPLE OFFERS -->
<div class="examples-section">
  <div class="examples-label">Ejemplos de tarifas típicas del mercado</div>
  <div class="examples-grid">
    <div class="example-chip" onclick="loadExample(0)">
      <div class="example-chip-label">Operador grande — trampa clásica</div>
      <div class="example-chip-text">"Movistar Fusión Plus: fibra 1Gb + 2 móviles ilimitados + HBO y Disney+. 79€/mes primeros 12 meses, después 99€/mes. Permanencia 24 meses."</div>
    </div>
    <div class="example-chip" onclick="loadExample(1)">
      <div class="example-chip-label">DIGI — referencia del mercado</div>
      <div class="example-chip-text">"Digi: fibra 600Mb simétrica + móvil 25GB. 15€/mes. Sin permanencia. Sin compromiso. Router incluido en propiedad."</div>
    </div>
    <div class="example-chip" onclick="loadExample(2)">
      <div class="example-chip-label">Vodafone — permanencia media</div>
      <div class="example-chip-text">"Vodafone One: fibra 600Mb + 1 móvil 50GB ilimitadas. 45€/mes primeros 6 meses, luego 65€/mes. Permanencia 18 meses. Penalización de 150€ si cancelas."</div>
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
      text: 'Movistar Fusión Plus: fibra 1Gb + 2 móviles ilimitados + HBO y Disney+. 79€/mes los primeros 12 meses, después 99€/mes. Permanencia 24 meses.',
      precioPromo: 79, precioDes: 99, perm: 24, vel: 1000, gb: 0, lineas: 2
    },
    {
      text: 'Digi: fibra 600Mb simétrica + móvil 25GB. 15€/mes. Sin permanencia. Sin compromiso. Router incluido en propiedad.',
      precioPromo: 15, precioDes: 15, perm: 0, vel: 600, gb: 25, lineas: 1
    },
    {
      text: 'Vodafone One: fibra 600Mb + 1 móvil 50GB ilimitadas. 45€/mes primeros 6 meses, luego 65€/mes. Permanencia 18 meses. Penalización de 150€ si cancelas.',
      precioPromo: 45, precioDes: 65, perm: 18, vel: 600, gb: 50, lineas: 1
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
    document.getElementById('f-precio-promo').value   = e.precioPromo || '';
    document.getElementById('f-precio-despues').value = e.precioDes   || '';
    document.getElementById('f-permanencia').value    = e.perm        >= 0 ? e.perm : '';
    document.getElementById('f-velocidad').value      = e.vel         || '';
    document.getElementById('f-gb').value             = e.gb          || '';
    document.getElementById('f-lineas').value         = e.lineas      || 1;

    document.getElementById('main-card').scrollIntoView({ behavior: 'smooth' });
  }

  // ── Analyze ──
  async function analyze() {
    const offerText   = offerEl.value.trim();
    const precioPromo = parseFloat(document.getElementById('f-precio-promo').value)   || 0;
    const precioDes   = parseFloat(document.getElementById('f-precio-despues').value) || 0;
    const permanencia = parseInt(document.getElementById('f-permanencia').value)       || 0;
    const velocidad   = parseInt(document.getElementById('f-velocidad').value)         || 0;
    const gbDatos     = parseFloat(document.getElementById('f-gb').value)              || 0;
    const numLineas   = parseInt(document.getElementById('f-lineas').value)            || 1;

    const errEl = document.getElementById('error-msg');
    errEl.style.display = 'none';

    if (!offerText && !fileDataA && !precioPromo) {
      errEl.textContent = 'Pega el texto de la oferta en el campo de texto, o introduce al menos el precio mensual.';
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
      ['Analizando tu tarifa de telefonía...', 'Buscando lo que te cobran sin que te enteres...'],
      ['Calculando el coste total con permanencia...', 'Lo que pagarás si te vas antes de tiempo...'],
      ['Detectando subidas de precio encubiertas...', 'Las que meten en la letra pequeña del contrato...'],
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
      const res = await fetch('telco-api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          offer_text:        offerText,
          precio_promo:      precioPromo,
          precio_despues:    precioDes,
          meses_permanencia: permanencia,
          velocidad_mb:      velocidad,
          gb_datos:          gbDatos,
          num_lineas:        numLineas,
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
    const costePermanencia = nums_raw.coste_total_permanencia || data.coste_total_permanencia;
    const nuleroDueleEl = document.getElementById('numero-duele');
    const numeroDueleAmount = document.getElementById('numero-duele-amount');
    const numeroDueleLabel  = document.getElementById('numero-duele-label');
    if (costePermanencia && parseFloat(costePermanencia) > 100) {
      const val = typeof costePermanencia === 'string' ? costePermanencia : Math.round(costePermanencia).toLocaleString('es-ES') + '€';
      numeroDueleAmount.textContent = val;
      numeroDueleLabel.textContent  = 'coste total durante el período de permanencia de este contrato';
      nuleroDueleEl.style.display   = 'flex';
    } else {
      nuleroDueleEl.style.display = 'none';
    }

    // ── SHARE BUTTONS ──
    const veredictoText = data.veredicto || 'He analizado mi tarifa de telefonía con SinFiltros';
    const tweetText = encodeURIComponent(`${veredictoText.substring(0,120)}... Analiza el tuyo gratis 👇 #SinFiltros #Telefonia`);
    const tweetUrl  = encodeURIComponent('https://sinfiltros.es/telco.php');
    const waText    = encodeURIComponent(`${veredictoText.substring(0,140)}...\n\nAnaliza tú también: https://sinfiltros.es/telco.php`);
    document.getElementById('btn-share-twitter').href  = `https://twitter.com/intent/tweet?text=${tweetText}&url=${tweetUrl}`;
    document.getElementById('btn-share-whatsapp').href = `https://wa.me/?text=${waText}`;
    document.getElementById('share-section').style.display = 'block';
    window._sfSummary = `SinFiltros — Análisis de Telefonía & Internet\n${'─'.repeat(40)}\n${veredictoText}\n\nAnálisis gratuito en: https://sinfiltros.es/telco.php`;

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
      SOLO_MOVIL:  'Solo móvil',
      SOLO_FIBRA:  'Solo fibra/internet',
      CONVERGENTE: 'Pack convergente (fibra + móvil)'
    };
    const tipoEl = document.getElementById('tipo-badge');
    tipoEl.innerHTML = `<i class="fa fa-mobile-screen"></i> ${tipoLabels[data.tipo_servicio] || data.tipo_servicio || '—'}`;

    document.getElementById('result-veredicto').textContent = data.veredicto || '';

    const transLabel = score >= 70 ? 'Oferta relativamente transparente' :
                       score >= 40 ? 'Transparencia media' :
                                     'Poca transparencia — léete la letra pequeña';
    document.getElementById('transparency-label').textContent = `Transparencia: ${transLabel} (${score}/100)`;

    // Numbers grid
    const nums   = data.numeros_reales || {};
    const gridEl = document.getElementById('numbers-grid');
    gridEl.innerHTML = '';

    const fmtMes = (v) => v != null ? parseFloat(v).toLocaleString('es-ES', {minimumFractionDigits:2, maximumFractionDigits:2}) + '€/mes' : null;
    const fmtEur = (v) => v != null ? parseFloat(v).toLocaleString('es-ES', {minimumFractionDigits:2, maximumFractionDigits:2}) + '€' : null;
    const fmtGb  = (v) => v != null ? parseFloat(v).toFixed(4) + '€/GB' : null;

    const promo   = nums.precio_mensual_promo;
    const despues = nums.precio_mensual_despues;
    const promoDes = promo != null && despues != null && despues > promo * 1.15;

    const numItems = [
      { val: fmtMes(promo),                      lbl: 'Precio promo',               cls: '' },
      { val: fmtMes(despues),                     lbl: 'Precio después',             cls: promoDes ? 'cost' : '' },
      {
        val: nums.meses_permanencia != null
          ? (nums.meses_permanencia === 0 ? 'Sin permanencia' : nums.meses_permanencia + ' meses')
          : null,
        lbl: 'Permanencia',
        cls: (nums.meses_permanencia != null && nums.meses_permanencia > 18) ? 'cost' : ''
      },
      { val: fmtEur(nums.coste_total_permanencia), lbl: 'Coste total permanencia',    cls: 'total' },
      { val: fmtEur(nums.penalizacion_salida_max), lbl: 'Penalización salida máx.',   cls: 'cost' },
      { val: fmtGb(nums.precio_gb_datos),          lbl: 'Precio por GB',             cls: '' }
    ];

    numItems.forEach(it => {
      if (!it.val) return;
      gridEl.innerHTML += `
        <div class="number-item ${it.cls}">
          <span class="number-val">${escHtml(it.val)}</span>
          <span class="number-lbl">${escHtml(it.lbl)}</span>
        </div>`;
    });

    // Price rise banner
    const riseBanner = document.getElementById('price-rise-banner');
    const riseText   = document.getElementById('price-rise-text');
    const riseSub    = document.getElementById('price-rise-sub');
    const subida     = nums.subida_precio_despues;
    if (subida != null && promo != null && despues != null && despues > promo * 1.15) {
      riseBanner.style.display = 'flex';
      const pct = Math.round((subida / promo) * 100);
      riseText.textContent = `SUBIDA DE PRECIO TRAS PERMANENCIA: +${parseFloat(subida).toLocaleString('es-ES', {minimumFractionDigits:2, maximumFractionDigits:2})}€/MES (+${pct}%)`;
      riseSub.textContent  = `Pasas de ${parseFloat(promo).toLocaleString('es-ES', {minimumFractionDigits:2, maximumFractionDigits:2})}€/mes a ${parseFloat(despues).toLocaleString('es-ES', {minimumFractionDigits:2, maximumFractionDigits:2})}€/mes — ${(subida * 12).toLocaleString('es-ES', {minimumFractionDigits:2, maximumFractionDigits:2})}€ más al año`;
    } else {
      riseBanner.style.display = 'none';
    }

    // Trampas
    const trampas  = data.trampa || [];
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
    const ventajas   = data.ventajas || [];
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
      if (comp.vs_digi) {
        compGrid.innerHTML += `
          <div class="comp-row">
            <div class="comp-icon"><i class="fa fa-mobile-screen"></i></div>
            <div class="comp-text"><strong>vs DIGI (referencia barata)</strong>${escHtml(comp.vs_digi)}</div>
          </div>`;
      }
      if (comp.vs_omv) {
        compGrid.innerHTML += `
          <div class="comp-row">
            <div class="comp-icon"><i class="fa fa-sim-card"></i></div>
            <div class="comp-text"><strong>vs Simyo / Pepephone</strong>${escHtml(comp.vs_omv)}</div>
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
    ['f-precio-promo','f-precio-despues','f-permanencia','f-velocidad','f-gb','f-lineas'].forEach(id => {
      document.getElementById(id).value = '';
    });
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
