<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Analizador de Inversiones — SinFiltros</title>
  <meta name="description" content="Introduce el fondo, plan de pensiones o producto de ahorro. Calculamos exactamente cuánto dinero pierdes en comisiones a 10, 20 y 30 años y te mostramos la alternativa barata que tu banco nunca te ofrecerá." />
  <meta name="robots" content="index, follow" />
  <link rel="canonical" href="https://sinfiltros.es/inversiones.php" />

  <!-- Open Graph / Social sharing -->
  <meta property="og:type"         content="website" />
  <meta property="og:url"          content="https://sinfiltros.es/inversiones.php" />
  <meta property="og:title"        content="¿Cuánto te comen las comisiones? — SinFiltros" />
  <meta property="og:description"  content="Fondos, planes de pensiones, depósitos. Calculamos cuánto dinero pierdes en comisiones a 10, 20 y 30 años. Gratis." />
  <meta property="og:image"        content="https://sinfiltros.es/og-image.png" />
  <meta name="twitter:card"        content="summary_large_image" />
  <meta name="twitter:title"       content="¿Cuánto te comen las comisiones? — SinFiltros" />
  <meta name="twitter:description" content="Fondos, planes de pensiones, depósitos. Calculamos cuánto dinero pierdes en comisiones a 10, 20 y 30 años. Gratis." />
  <meta name="twitter:image"       content="https://sinfiltros.es/og-image.png" />

  <!-- Favicon -->
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 40 40'><defs><linearGradient id='g' x1='0' y1='0' x2='1' y2='1'><stop offset='0' stop-color='%238B5CF6'/><stop offset='1' stop-color='%23F97316'/></linearGradient></defs><rect width='40' height='40' rx='10' fill='url(%23g)'/><text x='50%' y='55%' dominant-baseline='middle' text-anchor='middle' font-family='system-ui,sans-serif' font-weight='900' font-size='16' fill='white'>SF</text></svg>" />

  <!-- Schema.org structured data -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "SoftwareApplication",
    "name": "Analizador de Inversiones — SinFiltros",
    "url": "https://sinfiltros.es/inversiones.php",
    "description": "Calcula cuánto dinero pierdes en comisiones con tu fondo o plan de pensiones a 10, 20 y 30 años. Gratis.",
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
      --accent:      #EC4899;
      --accent-l:    #F472B6;
      --accent-d:    #831843;
      --accent-rgb:  236,72,153;
      --hero-mid:    #1F0A1A;
      --result-bg1:  #FDF2F8;
      --result-bg2:  #FFF0F7;
    }
    /* ── number-item.total for inversiones ── */
    .number-item.total { background: linear-gradient(135deg, #1F0A1A, #3B0764); }

    /* ── Alternativa recomendada ── */
    .alternativa-block {
      margin: 20px 32px 0; padding: 20px 22px;
      background: linear-gradient(135deg, #ECFDF5, #F0FDF4);
      border: 1px solid #A7F3D0; border-radius: 14px;
    }
    .alternativa-header { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
    .alternativa-icon {
      width: 32px; height: 32px; background: rgba(16,185,129,0.2);
      border-radius: 8px; display: flex; align-items: center; justify-content: center;
      font-size: 0.9rem; color: var(--green); flex-shrink: 0;
    }
    .alternativa-title { font-size: 0.78rem; font-weight: 800; color: #065F46; text-transform: uppercase; letter-spacing: 0.06em; }
    .alternativa-text { font-size: 0.92rem; font-weight: 700; color: #064E3B; line-height: 1.5; }

    @media (max-width: 600px) {
      .alternativa-block { margin: 20px 20px 0; }
      #comisiones-section { margin-left: 20px !important; margin-right: 20px !important; }
      #comisiones-grid { grid-template-columns: 1fr !important; }
    }

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
    .comp-card-amount.caro { color: #DC2626; } .comp-card-amount.normal { color: #EA580C; }
    .comp-card-amount.barato, .comp-card-amount.positivo { color: #059669; }
    .comp-card-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 20px; font-size: 0.64rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; align-self: flex-start; }
    .comp-card-badge.caro { background: rgba(220,38,38,0.1); color: #DC2626; }
    .comp-card-badge.normal { background: rgba(234,88,12,0.1); color: #EA580C; }
    .comp-card-badge.barato, .comp-card-badge.positivo { background: rgba(5,150,105,0.1); color: #059669; }
    .comp-card-desc { font-size: 0.82rem; color: #475569; line-height: 1.55; }
    @media (max-width: 600px) { .comp-cards { grid-template-columns: 1fr; } }

    /* ── PRINT (inversiones — rosa) ── */
    @media print {
      * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
      @page { size: A4; margin: 0; }
      .print-header {
        justify-content: space-between; padding: 16px 22mm;
        background: #9D174D !important; border-bottom: none;
      }
      .print-header-left { display: flex; flex-direction: column; gap: 2px; }
      .print-logo { font-size: 1.5rem; letter-spacing: -0.5px; line-height: 1; color: white !important; }
      .print-subtitle { font-size: 0.78rem; color: rgba(255,255,255,0.72) !important; font-weight: 500; margin-top: 3px; }
      .print-header-right { text-align: right; }
      .print-date { font-size: 0.72rem; color: rgba(255,255,255,0.65) !important; line-height: 1.6; }
      .print-url { font-size: 0.65rem; color: rgba(255,255,255,0.45) !important; }
      .result-header    { padding: 18px 22mm !important; background: #FDF2F8 !important; border-bottom: 1px solid #FBCFE8; page-break-after: avoid; }
      .numbers-block    { padding: 16px 22mm !important; border-bottom: 1px solid #E2E8F0; page-break-inside: avoid; }
      #comisiones-section  { padding: 14px 22mm 0 !important; page-break-inside: avoid; }
      #alternativa-section { padding: 14px 22mm 0 !important; page-break-inside: avoid; }
      .result-body      { padding: 16px 22mm 20mm !important; display: flex !important; flex-direction: column !important; gap: 18px !important; }
      .tipo-badge { background: #FCE7F3 !important; color: #BE185D !important; border: 1px solid #FBCFE8 !important; }
      .pregunta-num { border: 2px solid #EC4899 !important; color: #EC4899 !important; }
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
  <div class="hero-tag"><i class="fa fa-chart-pie"></i> Inversiones — Detecta comisiones ocultas</div>
  <h1>¿Cuánto te roban<br/><span>las comisiones?</span></h1>
  <p>Introduce el fondo, plan de pensiones o producto de ahorro. Calculamos exactamente cuánto dinero pierdes en comisiones a 10, 20 y 30 años y te mostramos la alternativa barata que tu banco nunca te ofrecerá.</p>
</div>

<!-- MAIN CARD -->
<div class="main">
  <div class="card" id="main-card">

    <!-- FORM -->
    <div id="form-section">
      <div class="card-header">
        <h2><i class="fa fa-chart-pie" style="color:var(--pink);margin-right:8px;"></i>Analiza cualquier fondo o producto de inversión</h2>
        <p>Pega el nombre del fondo, el texto del KID/DFI o el informe anual. Detectamos el impacto real de las comisiones en euros.</p>
      </div>
      <div class="card-body">
        <div class="textarea-wrap">
          <textarea
            id="offer-text"
            placeholder="Opciones:&#10;• Pega el nombre del fondo o plan de pensiones&#10;• Pega el contenido del KID/DFI (documento de datos fundamentales)&#10;• Pega el texto del informe anual o la ficha del fondo&#10;&#10;Ejemplo: &quot;Fondo Santander Acciones España: TER 2.15% anual. Benchmark: Ibex 35. Rentabilidad 5 años: 4.2% anual.&quot;"
            maxlength="3000"
          ></textarea>
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
          Introducir datos del producto (para cálculo exacto del impacto de comisiones)
          <i class="fa fa-chevron-down chevron"></i>
        </button>

        <div class="structured-fields" id="structured-fields">
          <div class="fields-label">Datos del producto</div>
          <div class="fields-grid">
            <div class="field-group">
              <label for="f-ter">Comisión anual TER/OCF (%)</label>
              <div class="input-with-suffix">
                <input type="number" id="f-ter" placeholder="1.80" min="0" max="10" step="0.01" />
                <span class="input-suffix">%</span>
              </div>
              <span class="field-hint">TER o "Comisión de gestión + depositaria"</span>
            </div>
            <div class="field-group">
              <label for="f-importe">Importe a invertir (€)</label>
              <div class="input-with-suffix">
                <input type="number" id="f-importe" placeholder="50000" min="0" />
                <span class="input-suffix">€</span>
              </div>
            </div>
            <div class="field-group">
              <label for="f-plazo">Plazo inversión (años)</label>
              <input type="number" id="f-plazo" placeholder="20" min="1" max="50" />
            </div>
            <div class="field-group">
              <label for="f-rent">Rentabilidad histórica anunciada (%)</label>
              <div class="input-with-suffix">
                <input type="number" id="f-rent" placeholder="6.5" min="0" max="100" step="0.1" />
                <span class="input-suffix">%</span>
              </div>
              <span class="field-hint">Opcional: la que anuncia el fondo</span>
            </div>
          </div>
        </div>

        <div id="error-msg"></div>

        <button class="btn-analyze" id="btn-analyze" onclick="analyze()">
          <i class="fa fa-chart-pie"></i> Calcular impacto de comisiones
        </button>
      </div>
    </div>

    <!-- LOADING -->
    <div id="loading">
      <div class="scan-ring"></div>
      <p>Analizando el producto...</p>
      <small>Calculando el impacto de comisiones a 10, 20 y 30 años...</small>
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
          <div class="print-subtitle">Informe de análisis — Inversiones &amp; Fondos</div>
        </div>
        <div class="print-header-right">
          <div class="print-date" id="print-date"></div>
          <div class="print-url">sinfiltros.es/inversiones.php</div>
        </div>
      </div>

      <!-- EL NÚMERO QUE DUELE -->
      <div class="numero-duele" id="numero-duele">
        <div class="numero-duele-icon">💸</div>
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
            <!-- "bate indice" badge injected here by JS -->
            <div style="clear:both;"></div>
            <div class="veredicto" id="result-veredicto"></div>
            <div class="transparency-label" id="transparency-label"></div>
          </div>
        </div>
      </div>

      <!-- CALCULADORA DE COMISIONES (the most important section) -->
      <div id="comisiones-section" style="display:none; margin: 20px 32px 0;">
        <div style="background:linear-gradient(135deg,#0F172A,#1F0A1A);border:1px solid rgba(236,72,153,0.4);border-radius:16px;padding:22px 24px;">
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:18px;">
            <div style="width:36px;height:36px;background:rgba(236,72,153,0.25);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1rem;color:#F472B6;flex-shrink:0;">
              <i class="fa fa-coins"></i>
            </div>
            <div>
              <div style="font-size:0.9rem;font-weight:800;color:#FBCFE8;">IMPACTO DE COMISIONES</div>
              <div style="font-size:0.72rem;color:rgba(251,207,232,0.6);">€ que se queda la gestora vs un fondo indexado equivalente</div>
            </div>
          </div>
          <div id="comisiones-grid" style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:16px;"></div>
          <div id="comparativa-valores" style="display:grid;grid-template-columns:1fr 1fr;gap:10px;"></div>
        </div>
      </div>

      <!-- Real numbers -->
      <div class="numbers-block">
        <div class="block-title"><i class="fa fa-calculator" style="color:var(--pink);"></i> Datos del producto</div>
        <div class="numbers-grid" id="numbers-grid"></div>
      </div>

      <!-- ALTERNATIVA RECOMENDADA -->
      <div id="alternativa-section" style="display:none;"></div>

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
        <div id="trampas-section" style="display:none;">
          <div class="section-hdr"><i class="fa fa-triangle-exclamation" style="color:var(--red);"></i> Trampas y puntos de atención</div>
          <div class="trampa-list" id="trampa-list"></div>
        </div>

        <!-- VENTAJAS -->
        <div id="ventajas-section" style="display:none;">
          <div class="section-hdr"><i class="fa fa-circle-check" style="color:var(--green);"></i> Ventajas reales</div>
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
          <div class="section-hdr"><i class="fa fa-circle-question" style="color:var(--pink);"></i> Preguntas que DEBES hacer antes de contratar</div>
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
        <i class="fa fa-rotate-left" style="margin-right:6px;"></i> Analizar otro producto
      </button>

      <!-- Print-only footer -->
      <div class="print-footer">
        Generado por <strong>SinFiltros</strong> · sinfiltros.es · La IA que te lo dice sin filtros
      </div>

    </div><!-- /results -->

  </div><!-- /card -->
</div><!-- /main -->

<!-- EXAMPLE CHIPS -->
<div class="examples-section">
  <div class="examples-label">Ejemplos de productos típicos</div>
  <div class="examples-grid">
    <div class="example-chip" onclick="loadExample(0)">
      <div class="example-chip-label">Plan de pensiones caro</div>
      <div class="example-chip-text">"Plan de Pensiones Santander Renta Variable. TER 1.70%. Benchmark: Ibex 35. Rentabilidad 5 años: 3.2% anual."</div>
    </div>
    <div class="example-chip" onclick="loadExample(1)">
      <div class="example-chip-label">Fondo indexado</div>
      <div class="example-chip-text">"Bankinter Índice América. Comisión gestión: 0.30%. Replica S&amp;P 500. Rentabilidad 10 años: 11.2% anual."</div>
    </div>
    <div class="example-chip" onclick="loadExample(2)">
      <div class="example-chip-label">Gestión discrecional BBVA</div>
      <div class="example-chip-text">"BBVA Quality Inversión Moderada. TER estimado: 2.25%. Perfil moderado. Rentabilidad 5 años: 2.8% anual."</div>
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
      text: 'Plan de Pensiones Santander Renta Variable. Comisión de gestión: 1.50%. Comisión de depositaria: 0.20%. TER total: 1.70%. Benchmark: Ibex 35. Rentabilidad 5 años: 3.2% anual. Gestionado activamente por Santander AM.',
      ter: 1.70, importe: 50000, plazo: 20, rent: 3.2
    },
    {
      text: 'Fondo de inversión Bankinter Índice América. Comisión gestión: 0.30%. Replica el S&P 500 (con divisa cubierta al EUR). Rentabilidad 10 años: 11.2% anual. Sin comisión de éxito. Patrimonio: 1.200M€.',
      ter: 0.30, importe: 30000, plazo: 15, rent: 11.2
    },
    {
      text: 'Gestión discrecional BBVA Quality Inversión Moderada. Comisión de gestión: 1.75%. Comisión de distribución: 0.50%. TER estimado: 2.25%. Perfil moderado. Rentabilidad 5 años: 2.8% anual. Mezcla de fondos propios.',
      ter: 2.25, importe: 100000, plazo: 25, rent: 2.8
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
    document.getElementById('f-ter').value    = e.ter    || '';
    document.getElementById('f-importe').value = e.importe || '';
    document.getElementById('f-plazo').value   = e.plazo  || '';
    document.getElementById('f-rent').value    = e.rent   || '';

    document.getElementById('main-card').scrollIntoView({ behavior: 'smooth' });
  }

  // ── Analyze ──
  async function analyze() {
    const offerText = offerEl.value.trim();
    const ter     = parseFloat(document.getElementById('f-ter').value)     || 0;
    const importe = parseFloat(document.getElementById('f-importe').value) || 0;
    const plazo   = parseInt(document.getElementById('f-plazo').value)     || 0;
    const rent    = parseFloat(document.getElementById('f-rent').value)    || 0;

    const errEl = document.getElementById('error-msg');
    errEl.style.display = 'none';

    if (!offerText && !fileDataA && !ter) {
      errEl.textContent = 'Pega el nombre del fondo o las condiciones, o introduce al menos la comisión anual (TER).';
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
      ['Analizando tu fondo de inversión...', 'Calculando cuánto te comen las comisiones año a año...'],
      ['Proyectando el impacto a 20 años...', 'El dinero que pierdes sin saber que lo estás perdiendo...'],
      ['Buscando alternativas más baratas...', 'Los fondos indexados que tu banco nunca te ofrecerá...'],
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
      const res = await fetch('src/Api/inversiones-api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          offer_text:    offerText,
          comision_ter:  ter,
          importe_inv:   importe,
          plazo_anos:    plazo,
          rent_historica: rent,
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
        throw new Error(data.error || 'Error al analizar el producto');
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
    const impactoComisiones = nums_raw.impacto_comisiones_20a || data.impacto_comisiones_20a;
    const nuleroDueleEl = document.getElementById('numero-duele');
    const numeroDueleAmount = document.getElementById('numero-duele-amount');
    const numeroDueleLabel  = document.getElementById('numero-duele-label');
    if (impactoComisiones && parseFloat(impactoComisiones) > 500) {
      const val = typeof impactoComisiones === 'string' ? impactoComisiones : Math.round(impactoComisiones).toLocaleString('es-ES') + '€';
      numeroDueleAmount.textContent = val;
      numeroDueleLabel.textContent  = 'perderás en comisiones en 20 años — dinero que debería ser tuyo';
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
    const veredictoText = data.veredicto || 'He analizado mis inversiones con SinFiltros';
    const tweetText = encodeURIComponent(`${veredictoText.substring(0,120)}... Analiza las tuyas gratis 👇 #SinFiltros #Inversiones`);
    const tweetUrl  = encodeURIComponent('https://sinfiltros.es/inversiones.php');
    const waText    = encodeURIComponent(`${veredictoText.substring(0,140)}...\n\nAnaliza tú también: https://sinfiltros.es/inversiones.php`);
    document.getElementById('btn-share-twitter').href  = `https://twitter.com/intent/tweet?text=${tweetText}&url=${tweetUrl}`;
    document.getElementById('btn-share-whatsapp').href = `https://wa.me/?text=${waText}`;
    document.getElementById('share-section').style.display = 'block';
    window._sfSummary = `SinFiltros — Análisis de Inversiones\n${'─'.repeat(40)}\n${veredictoText}\n\nAnálisis gratuito en: https://sinfiltros.es/inversiones.php`;

    // Gauge animation
    if (_gaugeInterval) clearInterval(_gaugeInterval);
    const score  = Math.max(0, Math.min(100, data.puntuacion_transparencia || 50));
    const circum = 276.46;
    const offset = circum - (score / 100) * circum;
    const color  = score >= 70 ? '#10B981' : score >= 40 ? '#F97316' : '#EF4444';
    const bar    = document.getElementById('gauge-bar');
    const numEl  = document.getElementById('gauge-num');
    bar.style.stroke  = color;
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
      FONDO_ACTIVO:   'Gestión Activa',
      FONDO_INDEXADO: 'Fondo Indexado',
      PLAN_PENSIONES: 'Plan de Pensiones',
      DEPOSITO:       'Depósito',
      ETF:            'ETF (Fondo cotizado)',
      MONETARIO:      'Fondo Monetario',
      OTRO:           'Otro producto'
    };
    const tipoEl = document.getElementById('tipo-badge');
    tipoEl.innerHTML = `<i class="fa fa-tag"></i> ${tipoLabels[data.tipo_producto] || data.tipo_producto || '—'}`;

    // Bate índice badge (placed after tipo-badge)
    let bateBadgeEl = document.getElementById('bate-indice-badge');
    if (!bateBadgeEl) {
      bateBadgeEl = document.createElement('span');
      bateBadgeEl.id = 'bate-indice-badge';
      tipoEl.insertAdjacentElement('afterend', bateBadgeEl);
    }
    if (data.bate_indice === 'SI') {
      bateBadgeEl.className = 'fraude-mini-badge low';
      bateBadgeEl.style.cssText = '';
      bateBadgeEl.innerHTML = '&#10003; Bate el índice';
    } else if (data.bate_indice === 'NO') {
      bateBadgeEl.className = 'fraude-mini-badge high';
      bateBadgeEl.style.cssText = '';
      bateBadgeEl.innerHTML = '&#10007; No bate el índice';
    } else {
      bateBadgeEl.className = 'fraude-mini-badge';
      bateBadgeEl.style.cssText = 'background:rgba(100,116,139,0.1);color:#64748B;border:1px solid rgba(100,116,139,0.3);display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;font-size:0.68rem;font-weight:800;letter-spacing:0.04em;text-transform:uppercase;margin-bottom:8px;margin-left:8px;';
      bateBadgeEl.innerHTML = '? Sin datos de benchmark';
    }

    document.getElementById('result-veredicto').textContent = data.veredicto || '';

    const transLabel = score >= 70 ? 'Producto relativamente transparente' :
                       score >= 40 ? 'Transparencia media' :
                                     'Poca transparencia — revisar con cuidado';
    document.getElementById('transparency-label').textContent = `Transparencia: ${transLabel} (${score}/100)`;

    // ── CALCULADORA DE COMISIONES ──
    const comisionesSec      = document.getElementById('comisiones-section');
    const comisionesGrid     = document.getElementById('comisiones-grid');
    const comparativaValores = document.getElementById('comparativa-valores');
    const nums = data.numeros_reales || {};

    if (nums.impacto_comisiones_10a != null || nums.impacto_comisiones_20a != null) {
      comisionesSec.style.display = 'block';

      const periodos = [
        { years: 10, val: nums.impacto_comisiones_10a },
        { years: 20, val: nums.impacto_comisiones_20a },
        { years: 30, val: nums.impacto_comisiones_30a },
      ];
      comisionesGrid.innerHTML = '';
      periodos.forEach(p => {
        if (!p.val) return;
        comisionesGrid.innerHTML += `
          <div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);border-radius:12px;padding:14px;text-align:center;">
            <div style="font-size:0.68rem;font-weight:700;color:rgba(252,165,165,0.8);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px;">A ${p.years} años</div>
            <div style="font-size:1.4rem;font-weight:900;color:#FCA5A5;letter-spacing:-0.02em;">${Math.round(p.val).toLocaleString('es-ES')}€</div>
            <div style="font-size:0.65rem;color:rgba(252,165,165,0.6);margin-top:3px;">perdidos en comisiones</div>
          </div>`;
      });

      comparativaValores.innerHTML = '';
      if (nums.valor_proyectado_neto_20a) {
        comparativaValores.innerHTML += `
          <div style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.2);border-radius:10px;padding:12px;text-align:center;">
            <div style="font-size:0.68rem;font-weight:700;color:#FCA5A5;text-transform:uppercase;margin-bottom:4px;">Con este fondo a 20 años</div>
            <div style="font-size:1.15rem;font-weight:900;color:#F87171;">${Math.round(nums.valor_proyectado_neto_20a).toLocaleString('es-ES')}€</div>
          </div>`;
      }
      if (nums.valor_indexado_proyectado_20a) {
        comparativaValores.innerHTML += `
          <div style="background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.2);border-radius:10px;padding:12px;text-align:center;">
            <div style="font-size:0.68rem;font-weight:700;color:#34D399;text-transform:uppercase;margin-bottom:4px;">Con indexado (TER 0.20%)</div>
            <div style="font-size:1.15rem;font-weight:900;color:#10B981;">${Math.round(nums.valor_indexado_proyectado_20a).toLocaleString('es-ES')}€</div>
          </div>`;
      }
    } else {
      comisionesSec.style.display = 'none';
    }

    // ── Standard numbers grid ──
    const gridEl = document.getElementById('numbers-grid');
    gridEl.innerHTML = '';

    const fmtPct = (v) => v != null ? parseFloat(v).toFixed(2) + '%' : null;
    const fmtEur = (v) => v != null ? Math.round(v).toLocaleString('es-ES') + '€' : null;

    const numItems = [
      { val: fmtPct(nums.comision_anual_pct), lbl: 'Comisión anual (TER)', cls: (nums.comision_anual_pct > 1) ? 'cost' : '' },
      { val: fmtEur(nums.impacto_comisiones_20a), lbl: 'Coste comisiones 20 años', cls: 'cost' },
      { val: fmtEur(nums.valor_proyectado_neto_20a), lbl: 'Con este fondo (20 años)', cls: 'total' },
      { val: fmtEur(nums.valor_indexado_proyectado_20a), lbl: 'Con indexado TER 0.20%', cls: '' },
    ];
    numItems.forEach(it => {
      if (!it.val) return;
      gridEl.innerHTML += `
        <div class="number-item ${it.cls}">
          <span class="number-val">${escHtml(it.val)}</span>
          <span class="number-lbl">${escHtml(it.lbl)}</span>
        </div>`;
    });

    // ── Alternativa concreta ──
    const altSec   = document.getElementById('alternativa-section');
    const compObj  = data.comparativa || {};
    if (compObj.alternativa_concreta) {
      altSec.style.display = 'block';
      altSec.innerHTML = `
        <div class="alternativa-block">
          <div class="alternativa-header">
            <div class="alternativa-icon"><i class="fa fa-arrow-trend-up"></i></div>
            <div class="alternativa-title">Alternativa Recomendada (Barata y Equivalente)</div>
          </div>
          <div class="alternativa-text">${escHtml(compObj.alternativa_concreta)}</div>
        </div>`;
    } else {
      altSec.style.display = 'none';
    }

    // ── ASESOR HONESTO (promoted) ──
    const asesorSec  = document.getElementById('asesor-section');
    const asesorText = document.getElementById('asesor-text');
    if (compObj && compObj.recomendacion && asesorSec) {
      asesorText.textContent = compObj.recomendacion;
      asesorSec.style.display = 'block';
    } else if (asesorSec) {
      asesorSec.style.display = 'none';
    }

    // ── Trampas ──
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

    // ── Ventajas ──
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

    // ── Comparativa ──
    const compSec = document.getElementById('comparativa-section');
    if (compObj && (compObj.vs_indexado || compObj.recomendacion)) {
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
        if (typeof scenario === 'string') {
          return `<div class="comp-row"><div class="comp-icon"><i class="fa ${faIcon}"></i></div><div class="comp-text"><strong>${escHtml(title)}</strong>${escHtml(scenario)}</div></div>`;
        }
        const vm = verdictMeta[scenario.veredicto] || verdictMeta['NORMAL'];
        const raw = scenario.diferencia_eur;
        const amtStr = raw != null ? (raw >= 0 ? '+' : '') + Math.round(raw).toLocaleString('es-ES') + '€' : '';
        return `<div class="comp-card"><div class="comp-card-header"><i class="fa ${faIcon}"></i><div class="comp-card-title">${escHtml(title)}</div></div><div class="comp-card-body">${amtStr ? `<div class="comp-card-amount ${vm.cls}">${escHtml(amtStr)}</div>` : ''}${scenario.veredicto ? `<span class="comp-card-badge ${vm.cls}"><i class="fa ${vm.icon}"></i> ${escHtml(vm.label)}</span>` : ''}${scenario.descripcion ? `<div class="comp-card-desc">${escHtml(scenario.descripcion)}</div>` : ''}</div></div>`;
      }
      const isNewFormat = (compObj.vs_indexado && typeof compObj.vs_indexado === 'object') || (compObj.alternativa_concreta && typeof compObj.alternativa_concreta === 'object');
      compGrid.className = isNewFormat ? 'comp-cards' : 'comp-grid';
      compGrid.innerHTML =
        buildCompCard(compObj.vs_indexado,          'vs Fondo indexado de referencia',  'fa-coins') +
        buildCompCard(compObj.alternativa_concreta, 'Alternativa concreta recomendada', 'fa-star');
      document.getElementById('recomendacion-box').style.display = 'none';
    } else {
      compSec.style.display = 'none';
    }

    // ── Preguntas ──
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
  }

  // ── Reset ──
  function resetForm() {
    document.getElementById('results').style.display      = 'none';
    document.getElementById('share-section').style.display = 'none';
    document.getElementById('numero-duele').style.display  = 'none';
    document.getElementById('resumen-oferta').style.display = 'none';
    document.getElementById('compare-tabs').style.display  = 'none';
    document.getElementById('ganador-banner').style.display = 'none';
    document.getElementById('asesor-section').style.display = 'none';
    document.getElementById('form-section').style.display = 'block';
    document.getElementById('btn-analyze').disabled       = false;
    window._compareData = null;
    if (_gaugeInterval) clearInterval(_gaugeInterval);
    offerEl.value = '';
    charNumEl.textContent = '0';
    ['f-ter', 'f-importe', 'f-plazo', 'f-rent'].forEach(id => {
      document.getElementById(id).value = '';
    });
    document.getElementById('comisiones-section').style.display = 'none';
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
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }
</script>

</body>
</html>
