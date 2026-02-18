<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Analizador de Hipotecas — SinFiltros</title>
  <meta name="description" content="¿Te ofrecen una buena hipoteca o es humo? Análisis brutal del TIN, TAE real, seguros vinculados y coste total a 30 años. IA brutalmente honesta." />

  <!-- Open Graph / Social sharing -->
  <meta property="og:type"         content="website" />
  <meta property="og:url"          content="https://sinfiltros.es/hipoteca.php" />
  <meta property="og:title"        content="¿Te están colando la hipoteca? — SinFiltros" />
  <meta property="og:description"  content="Calculamos el coste real a 30 años, destripamos las vinculaciones y te decimos cuánto pagas de más. Gratis. En 15 segundos." />
  <meta property="og:image"        content="https://sinfiltros.es/og-image.png" />
  <meta name="twitter:card"        content="summary_large_image" />
  <meta name="twitter:title"       content="¿Te están colando la hipoteca? — SinFiltros" />
  <meta name="twitter:description" content="Calculamos el coste real a 30 años, destripamos las vinculaciones y te decimos cuánto pagas de más. Gratis. En 15 segundos." />
  <meta name="twitter:image"       content="https://sinfiltros.es/og-image.png" />

  <!-- Favicon -->
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 40 40'><defs><linearGradient id='g' x1='0' y1='0' x2='1' y2='1'><stop offset='0' stop-color='%238B5CF6'/><stop offset='1' stop-color='%23F97316'/></linearGradient></defs><rect width='40' height='40' rx='10' fill='url(%23g)'/><text x='50%' y='55%' dominant-baseline='middle' text-anchor='middle' font-family='system-ui,sans-serif' font-weight='900' font-size='16' fill='white'>SF</text></svg>" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="assets/sf.css">
  <style>
    :root {
      --accent:      #F97316;
      --accent-l:    #FB923C;
      --accent-d:    #C2410C;
      --accent-rgb:  249,115,22;
      --hero-mid:    #1C1917;
      --result-bg1:  #FFF7ED;
      --result-bg2:  #FFFBEB;
      --blue:        #3B82F6;
    }
    /* ── TIN vs TAE comparison bar ── */
    .tin-tae-bar {
      display: flex; align-items: center; gap: 8px;
      margin-top: 20px; padding: 14px 18px;
      background: white; border: 1px solid #FED7AA; border-radius: 12px;
    }
    .tin-tae-item { flex: 1; text-align: center; }
    .tin-tae-item .tv-val { font-size: 1.3rem; font-weight: 900; display: block; }
    .tin-tae-item .tv-lbl { font-size: 0.68rem; font-weight: 600; color: var(--mid); text-transform: uppercase; letter-spacing: 0.05em; }
    .tin-tae-item .tv-hint { font-size: 0.65rem; color: var(--light); margin-top: 2px; display: block; }
    .tin-tae-vs { font-size: 0.8rem; font-weight: 800; color: var(--light); }
    .tin-tae-item.tin .tv-val { color: var(--accent); }
    .tin-tae-item.tae-a .tv-val { color: #C2410C; }
    .tin-tae-item.tae-r .tv-val { color: var(--red); }

    /* ── Numbers extras ── */
    .number-item.seguros {
      background: linear-gradient(135deg, #FFF7ED, #FFFBEB); border-color: #FED7AA;
    }
    .number-item.seguros .number-val { color: #C2410C; }
    .number-item.seguros .number-lbl { color: #92400E; }
    .number-item.total { background: linear-gradient(135deg, #1C1917, #292524); }

    /* ── Euribor risk block ── */
    .euribor-block {
      margin: 0 32px; background: linear-gradient(135deg, #0C0A09, #1C0A00);
      border: 1px solid #F97316; border-radius: 16px; padding: 22px 24px;
    }
    .euribor-hdr { display: flex; align-items: center; gap: 10px; margin-bottom: 16px; }
    .euribor-icon {
      width: 36px; height: 36px; background: rgba(249,115,22,0.25);
      border-radius: 10px; display: flex; align-items: center; justify-content: center;
      font-size: 1rem; color: #FB923C; flex-shrink: 0;
    }
    .euribor-title { font-size: 0.9rem; font-weight: 800; color: #FED7AA; letter-spacing: -0.01em; }
    .euribor-subtitle { font-size: 0.75rem; color: rgba(254,215,170,0.6); margin-top: 2px; }
    .euribor-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 10px; }
    .euribor-item {
      background: rgba(255,255,255,0.05); border: 1px solid rgba(249,115,22,0.2);
      border-radius: 12px; padding: 14px 12px; text-align: center;
    }
    .euribor-item.current { border-color: rgba(249,115,22,0.4); background: rgba(249,115,22,0.08); }
    .euribor-item.danger  { border-color: rgba(239,68,68,0.5); background: rgba(239,68,68,0.08); }
    .euribor-euribor-val { font-size: 0.7rem; font-weight: 700; color: #FB923C; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px; }
    .euribor-cuota { font-size: 1.15rem; font-weight: 900; color: white; display: block; margin-bottom: 2px; }
    .euribor-item.danger .euribor-cuota { color: #FCA5A5; }
    .euribor-lbl { font-size: 0.65rem; font-weight: 600; color: rgba(255,255,255,0.4); }

    /* ── Gastos iniciales ── */
    .gastos-block {
      margin: 0 32px; padding: 22px 24px;
      background: linear-gradient(135deg, #FFFBEB, #FFF7ED);
      border: 1px solid #FDE68A; border-radius: 14px;
    }
    .gastos-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 10px; margin-bottom: 12px; }
    .gasto-item { background: white; border: 1px solid #FDE68A; border-radius: 10px; padding: 12px; text-align: center; }
    .gasto-val { font-size: 0.95rem; font-weight: 800; color: #92400E; display: block; margin-bottom: 3px; }
    .gasto-lbl { font-size: 0.66rem; font-weight: 600; color: #B45309; text-transform: uppercase; letter-spacing: 0.05em; }
    .gasto-total-item {
      grid-column: 1 / -1; background: linear-gradient(135deg, #78350F, #92400E);
      border-color: transparent; border-radius: 10px; padding: 14px;
      display: flex; align-items: center; justify-content: space-between;
    }
    .gasto-total-item .gasto-val { color: white; font-size: 1.1rem; }
    .gasto-total-item .gasto-lbl { color: rgba(255,255,255,0.65); }
    .gastos-nota { font-size: 0.78rem; color: #78350F; line-height: 1.5; padding: 10px 12px; background: rgba(251,191,36,0.15); border-radius: 8px; }

    /* ── Condiciones vinculadas ── */
    .vinculadas-block {
      margin: 0 32px; padding: 20px 22px;
      background: linear-gradient(135deg, #EFF6FF, #F0F9FF);
      border: 1px solid #BFDBFE; border-radius: 14px;
    }
    .vinculada-item {
      display: flex; gap: 10px; align-items: flex-start;
      padding: 8px 0; border-top: 1px solid rgba(59,130,246,0.12);
      font-size: 0.875rem; color: #1E3A5F; line-height: 1.5;
    }
    .vinculada-item:first-child { border-top: none; padding-top: 0; }
    .vinculada-item i { color: var(--blue); margin-top: 2px; font-size: 0.8rem; flex-shrink: 0; }

    .section-spacer { height: 20px; }

    @media (max-width: 600px) {
      .euribor-block { margin: 0 20px; }
      .gastos-block { margin: 0 20px; }
      .vinculadas-block { margin: 0 20px; }
    }

    /* ── PRINT (hipoteca-specific extras) ── */
    @media print {
      * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
      @page { size: A4; margin: 0; }
      .print-header {
        justify-content: space-between; padding: 16px 22mm;
        background: #C2410C !important; border-bottom: none;
      }
      .print-header-left { display: flex; flex-direction: column; gap: 2px; }
      .print-logo { font-size: 1.5rem; letter-spacing: -0.5px; line-height: 1; }
      .print-subtitle { font-size: 0.78rem; color: rgba(255,255,255,0.72) !important; font-weight: 500; margin-top: 3px; }
      .print-header-right { text-align: right; }
      .print-date { font-size: 0.72rem; color: rgba(255,255,255,0.65) !important; line-height: 1.6; }
      .print-url { font-size: 0.65rem; color: rgba(255,255,255,0.45) !important; }
      .result-header    { padding: 18px 22mm !important; background: #FFF7ED !important; border-bottom: 1px solid #FED7AA; page-break-after: avoid; }
      .numbers-block    { padding: 16px 22mm !important; border-bottom: 1px solid #E2E8F0; page-break-inside: avoid; }
      #euribor-section  { padding: 14px 22mm 0 !important; page-break-inside: avoid; }
      #gastos-section   { padding: 14px 22mm 0 !important; page-break-inside: avoid; }
      #vinculadas-section { padding: 14px 22mm 0 !important; page-break-inside: avoid; }
      .result-body      { padding: 16px 22mm 20mm !important; display: flex !important; flex-direction: column !important; gap: 18px !important; }
      .tipo-badge { background: #FFF7ED !important; color: #C2410C !important; border: 1px solid #FED7AA !important; }
      .pregunta-num { border: 2px solid #F97316 !important; color: #F97316 !important; }
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
<?php include '_header.php'; ?>

<!-- HERO -->
<div class="hero">
  <div class="hero-tag"><i class="fa fa-percent"></i> Hipotecas — Análisis sin filtros</div>
  <h1>¿Te están colando<br /><span>la hipoteca?</span></h1>
  <p>Pega la oferta del banco o introduce los datos. Calculamos el coste real a 30 años, detectamos los seguros caros y te decimos exactamente cuánto te están cobrando de más.</p>
</div>

<!-- MAIN CARD -->
<div class="main">
  <div class="card" id="main-card">

    <!-- FORM -->
    <div id="form-section">
      <div class="card-header">
        <h2><i class="fa fa-percent" style="color:var(--orange);margin-right:8px;"></i>Analiza cualquier oferta hipotecaria</h2>
        <p>Pega el enlace del banco, el texto de la oferta o el email que te enviaron. O rellena los datos directamente.</p>
      </div>
      <div class="card-body">
        <div class="textarea-wrap">
          <textarea
            id="offer-text"
            placeholder="Opciones:&#10;• Pega la URL de la oferta del banco (Bankinter, BBVA, ING, Santander…)&#10;• Pega el texto o email de la oferta hipotecaria&#10;&#10;Ejemplo: &quot;Hipoteca fija al 3,40% TIN / TAE 3,89%. A 30 años. Condiciones: nómina domiciliada, seguro de vida y hogar del banco.&quot;"
            maxlength="3000"
          ></textarea>
          <div id="url-indicator" style="display:none; margin-top:8px; padding:10px 14px; background:#FFF7ED; border:1px solid #FED7AA; border-radius:10px; font-size:0.82rem; color:#C2410C; align-items:center; gap:8px;">
            <i class="fa fa-link"></i>
            <span>Enlace detectado — intentaremos leer la oferta del banco automáticamente</span>
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

        <button class="expand-toggle" id="expand-toggle" onclick="toggleFields()">
          <i class="fa fa-sliders"></i>
          Introducir datos de la hipoteca (opcional, para mayor precisión)
          <i class="fa fa-chevron-down chevron"></i>
        </button>

        <div class="structured-fields" id="structured-fields">
          <div class="fields-label">Datos de la oferta hipotecaria</div>
          <div class="fields-grid">
            <div class="field-group">
              <label for="f-importe">Importe hipoteca</label>
              <div class="input-with-suffix">
                <input type="number" id="f-importe" placeholder="150000" min="0" max="9999999" />
                <span class="input-suffix">€</span>
              </div>
              <span class="field-hint">Capital que pides al banco</span>
            </div>
            <div class="field-group">
              <label for="f-plazo">Plazo</label>
              <div class="input-with-suffix">
                <input type="number" id="f-plazo" placeholder="25" min="1" max="40" />
                <span class="input-suffix">años</span>
              </div>
            </div>
            <div class="field-group">
              <label for="f-tin">TIN anunciado</label>
              <div class="input-with-suffix">
                <input type="number" id="f-tin" placeholder="3.40" min="0" max="30" step="0.01" />
                <span class="input-suffix">%</span>
              </div>
              <span class="field-hint">Tipo de interés nominal</span>
            </div>
            <div class="field-group">
              <label for="f-tae">TAE anunciada</label>
              <div class="input-with-suffix">
                <input type="number" id="f-tae" placeholder="3.89" min="0" max="30" step="0.01" />
                <span class="input-suffix">%</span>
              </div>
              <span class="field-hint">La que pone en el contrato</span>
            </div>
            <div class="field-group">
              <label for="f-tipo">Tipo de hipoteca</label>
              <select id="f-tipo">
                <option value="">-- Selecciona --</option>
                <option value="FIJA">Hipoteca fija</option>
                <option value="VARIABLE">Hipoteca variable</option>
                <option value="MIXTA">Hipoteca mixta</option>
              </select>
            </div>
            <div class="field-group">
              <label for="f-diferencial">Diferencial Euribor</label>
              <div class="input-with-suffix">
                <input type="number" id="f-diferencial" placeholder="0.60" min="0" max="10" step="0.01" />
                <span class="input-suffix">%</span>
              </div>
              <span class="field-hint">Solo para variable/mixta (Euribor + este %)</span>
            </div>
          </div>
        </div>

        <div id="error-msg"></div>

        <button class="btn-analyze" id="btn-analyze" onclick="analyze()">
          <i class="fa fa-percent"></i> Analizar hipoteca ahora
        </button>
      </div>
    </div>

    <!-- LOADING -->
    <div id="loading">
      <div class="scan-ring"></div>
      <p id="loading-msg">Analizando la hipoteca...</p>
      <small id="loading-sub">Calculando el coste real, detectando condiciones ocultas...</small>
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
        <div class="print-header-left">
          <div class="print-logo">SinFiltros</div>
          <div class="print-subtitle">Informe de análisis — Hipotecas</div>
        </div>
        <div class="print-header-right">
          <div class="print-date" id="print-date"></div>
          <div class="print-url">sinfiltros.es/hipoteca.php</div>
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
            <div class="tipo-badge" id="tipo-badge"><i class="fa fa-percent"></i> —</div>
            <div class="veredicto" id="result-veredicto"></div>
            <div class="transparency-label" id="transparency-label"></div>
          </div>
        </div>
        <!-- TIN vs TAE bar -->
        <div id="tin-tae-bar" class="tin-tae-bar" style="display:none;">
          <div class="tin-tae-item tin">
            <span class="tv-val" id="tin-val">—%</span>
            <span class="tv-lbl">TIN</span>
            <span class="tv-hint">Tipo de interés puro</span>
          </div>
          <div class="tin-tae-vs">→</div>
          <div class="tin-tae-item tae-a">
            <span class="tv-val" id="tae-anun-val">—%</span>
            <span class="tv-lbl">TAE anunciada</span>
            <span class="tv-hint">Con vinculaciones</span>
          </div>
          <div class="tin-tae-vs">→</div>
          <div class="tin-tae-item tae-r">
            <span class="tv-val" id="tae-real-val">—%</span>
            <span class="tv-lbl">TAE real estimada</span>
            <span class="tv-hint">Sin vinculaciones</span>
          </div>
        </div>
      </div>

      <!-- Real numbers -->
      <div class="numbers-block">
        <div class="block-title"><i class="fa fa-calculator" style="color:var(--orange);"></i> Lo que realmente pagas</div>
        <div class="numbers-grid" id="numbers-grid"></div>
      </div>

      <!-- EURIBOR RISK (variable/mixta only) -->
      <div id="euribor-section" style="display:none; padding: 20px 32px 0;">
        <div class="euribor-block">
          <div class="euribor-hdr">
            <div class="euribor-icon"><i class="fa fa-chart-line"></i></div>
            <div>
              <div class="euribor-title">Riesgo Euribor — ¿Qué pasa si sube?</div>
              <div class="euribor-subtitle">Proyecciones de tu cuota mensual según el Euribor</div>
            </div>
          </div>
          <div class="euribor-grid" id="euribor-grid"></div>
        </div>
      </div>

      <!-- INITIAL COSTS -->
      <div id="gastos-section" style="display:none; padding: 20px 32px 0;">
        <div class="gastos-block">
          <div class="block-title" style="margin-bottom:14px;"><i class="fa fa-file-invoice" style="color:#92400E;"></i> Gastos iniciales de la hipoteca</div>
          <div class="gastos-grid" id="gastos-grid"></div>
          <div class="gastos-nota" id="gastos-nota"></div>
        </div>
      </div>

      <!-- CONDICIONES VINCULADAS -->
      <div id="vinculadas-section" style="display:none; padding: 20px 32px 0;">
        <div class="vinculadas-block">
          <div class="block-title" style="margin-bottom:12px; color:#1E3A5F;"><i class="fa fa-link" style="color:var(--blue);"></i> Condiciones obligatorias para obtener ese tipo</div>
          <div id="vinculadas-list"></div>
        </div>
      </div>

      <!-- Body sections -->
      <div class="result-body">

        <div id="trampas-section">
          <div class="section-hdr"><i class="fa fa-triangle-exclamation" style="color:var(--red);"></i> Trampas y puntos críticos</div>
          <div class="trampa-list" id="trampa-list"></div>
        </div>

        <div id="ventajas-section" style="display:none;">
          <div class="section-hdr"><i class="fa fa-circle-check" style="color:var(--green);"></i> Ventajas reales de esta hipoteca</div>
          <ul class="ventaja-list" id="ventaja-list"></ul>
        </div>

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

        <div id="preguntas-section" style="display:none;">
          <div class="section-hdr"><i class="fa fa-circle-question" style="color:var(--orange);"></i> Preguntas que DEBES hacer al banco antes de firmar</div>
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
        <i class="fa fa-rotate-left" style="margin-right:6px;"></i> Analizar otra hipoteca
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
  <div class="examples-label">Ejemplos de ofertas hipotecarias típicas</div>
  <div class="examples-grid">
    <div class="example-chip" onclick="loadExample(0)">
      <div class="example-chip-label">Hipoteca fija con vinculaciones</div>
      <div class="example-chip-text">"Hipoteca fija al 3,40% TIN, TAE 3,89%. 25 años. Con domiciliación de nómina, seguro de vida y hogar del banco."</div>
    </div>
    <div class="example-chip" onclick="loadExample(1)">
      <div class="example-chip-label">Hipoteca variable sin vinculaciones</div>
      <div class="example-chip-text">"Hipoteca variable: Euribor + 0,49%. TAE Variable 3,82%. Sin comisiones, sin seguros obligatorios."</div>
    </div>
    <div class="example-chip" onclick="loadExample(2)">
      <div class="example-chip-label">Hipoteca mixta banco tradicional</div>
      <div class="example-chip-text">"Hipoteca mixta: 5 años al 2,80% TIN, después Euribor + 0,65%. TAE Variable 3,89%. Requiere seguro vida, hogar y tarjeta."</div>
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

  function toggleFields() {
    document.getElementById('expand-toggle').classList.toggle('open');
    document.getElementById('structured-fields').classList.toggle('open');
  }

  // ── Examples ──
  const EXAMPLES = [
    {
      text: 'Hipoteca Fija al 3,40% TIN (TAE 3,89%). Plazo máximo 25 años. Importe solicitado 200.000€. Condiciones para obtener el tipo bonificado: domiciliación de nómina mínima 1.500€/mes, seguro de vida del banco y seguro del hogar del banco.',
      importe: 200000, plazo: 25, tin: 3.40, tae: 3.89, tipo: 'FIJA', diferencial: 0
    },
    {
      text: 'Hipoteca Variable: Euribor 12 meses + 0,49%. TAE Variable 3,82% (calculada con Euribor actual). Sin comisión de apertura. Sin vinculaciones obligatorias. Solo necesitas abrir una cuenta corriente gratuita.',
      importe: 180000, plazo: 30, tin: 0, tae: 3.82, tipo: 'VARIABLE', diferencial: 0.49
    },
    {
      text: 'Hipoteca Mixta: 5 primeros años al 2,80% TIN fijo. Después: Euribor + 0,65% variable. TAE Variable 3,89%. Importe: 250.000€ a 30 años. Bonificaciones requeridas: seguro de vida vinculado, seguro del hogar, tarjeta de crédito con consumo mínimo 500€/mes.',
      importe: 250000, plazo: 30, tin: 2.80, tae: 3.89, tipo: 'MIXTA', diferencial: 0.65
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
    if (e.importe)     document.getElementById('f-importe').value     = e.importe;
    if (e.plazo)       document.getElementById('f-plazo').value       = e.plazo;
    if (e.tin)         document.getElementById('f-tin').value         = e.tin;
    if (e.tae)         document.getElementById('f-tae').value         = e.tae;
    if (e.tipo)        document.getElementById('f-tipo').value        = e.tipo;
    if (e.diferencial) document.getElementById('f-diferencial').value = e.diferencial;

    document.getElementById('main-card').scrollIntoView({ behavior: 'smooth' });
  }

  // ── Analyze ──
  async function analyze() {
    const offerText  = offerEl.value.trim();
    const importe    = parseInt(document.getElementById('f-importe').value)      || 0;
    const plazo      = parseInt(document.getElementById('f-plazo').value)        || 0;
    const tin        = parseFloat(document.getElementById('f-tin').value)        || 0;
    const tae        = parseFloat(document.getElementById('f-tae').value)        || 0;
    const tipo       = document.getElementById('f-tipo').value;
    const diferencial = parseFloat(document.getElementById('f-diferencial').value) || 0;

    const errEl = document.getElementById('error-msg');
    errEl.style.display = 'none';

    if (!offerText && !fileDataA && !importe) {
      errEl.textContent = 'Pega la oferta del banco en el campo de texto, o introduce al menos el importe de la hipoteca.';
      errEl.style.display = 'block';
      return;
    }

    document.getElementById('form-section').style.display = 'none';
    document.getElementById('results').style.display      = 'none';
    document.getElementById('loading').style.display      = 'block';
    document.getElementById('btn-analyze').disabled       = true;

    const loadingMsg = document.getElementById('loading-msg');
    const loadingSub = document.getElementById('loading-sub');
    const LOADING_MSGS = isUrl(offerText) ? [
      ['Leyendo la oferta del banco...', 'Obteniendo los datos que enterraron en la web...'],
      ['Calculando el coste real a 30 años...', 'Incluyendo seguros, comisiones y vinculaciones...'],
      ['Destripando las condiciones vinculadas...', 'El seguro de vida que te "recomiendan"...'],
      ['Preparando el análisis...', 'Sin suavizar los números. Sin filtros.'],
    ] : [
      ['Analizando la hipoteca...', 'Calculando coste real, detectando vinculaciones y comparando...'],
      ['Calculando el coste real a 30 años...', 'Incluyendo seguros, comisiones y vinculaciones...'],
      ['Buscando las trampas del banco...', 'Las que están en la letra pequeña de la FEIN...'],
      ['Preparando el veredicto...', 'Sin suavizar los números. Sin filtros.'],
    ];
    let msgIdx = 0;
    loadingMsg.textContent = LOADING_MSGS[0][0];
    loadingSub.textContent = LOADING_MSGS[0][1];
    const msgInterval = setInterval(() => {
      msgIdx = (msgIdx + 1) % LOADING_MSGS.length;
      loadingMsg.textContent = LOADING_MSGS[msgIdx][0];
      loadingSub.textContent = LOADING_MSGS[msgIdx][1];
    }, 2800);

    try {
      const res = await fetch('hipoteca-api.php', {
        // msgInterval cleared on success/error below
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          offer_text:       offerText,
          importe_hipoteca: importe,
          plazo_anos:       plazo,
          tin_ofertado:     tin,
          tae_anunciada:    tae,
          tipo_hipoteca:    tipo,
          diferencial:      diferencial,
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
        throw new Error(data.error || 'Error al analizar la hipoteca');
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
    const totalIntereses = nums_raw.total_intereses;
    const nuleroDueleEl = document.getElementById('numero-duele');
    const numeroDueleAmount = document.getElementById('numero-duele-amount');
    const numeroDueleLabel  = document.getElementById('numero-duele-label');
    if (totalIntereses && totalIntereses > 5000) {
      const fmt2 = v => parseFloat(v).toLocaleString('es-ES', {minimumFractionDigits:0, maximumFractionDigits:0}) + '€';
      numeroDueleAmount.textContent = fmt2(totalIntereses);
      numeroDueleLabel.textContent  = 'en intereses — lo que le darás al banco por prestarte el dinero';
      nuleroDueleEl.style.display   = 'flex';
    } else {
      nuleroDueleEl.style.display = 'none';
    }

    // ── SHARE BUTTONS ──
    const veredictoText = data.veredicto || 'He analizado esta hipoteca con SinFiltros';
    const interesesText = totalIntereses ? `Voy a pagar ${Math.round(totalIntereses).toLocaleString('es-ES')}€ en intereses. ` : '';
    const tweetText     = encodeURIComponent(`${interesesText}${veredictoText.substring(0,100)}... Analiza la tuya gratis 👇 #SinFiltros #Hipoteca`);
    const tweetUrl      = encodeURIComponent('https://sinfiltros.es/hipoteca.php');
    const waText        = encodeURIComponent(`${interesesText}${veredictoText.substring(0,120)}...\n\nAnaliza tú también: https://sinfiltros.es/hipoteca.php`);
    document.getElementById('btn-share-twitter').href  = `https://twitter.com/intent/tweet?text=${tweetText}&url=${tweetUrl}`;
    document.getElementById('btn-share-whatsapp').href = `https://wa.me/?text=${waText}`;
    document.getElementById('share-section').style.display = 'block';

    window._sfSummary = `SinFiltros — Análisis de hipoteca\n${'─'.repeat(40)}\n${veredictoText}\n${interesesText}\n\nAnálisis gratuito en: https://sinfiltros.es/hipoteca.php`;

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
    const tipoLabels = { FIJA: 'Hipoteca Fija', VARIABLE: 'Hipoteca Variable', MIXTA: 'Hipoteca Mixta' };
    document.getElementById('tipo-badge').innerHTML =
      `<i class="fa fa-percent"></i> ${tipoLabels[data.tipo_hipoteca] || data.tipo_hipoteca || '—'}`;

    document.getElementById('result-veredicto').textContent = data.veredicto || '';

    const transLabel = score >= 70 ? 'Oferta relativamente transparente' :
                       score >= 40 ? 'Transparencia media — revisa las vinculaciones' :
                                     'Poca transparencia — condiciones enterradas en letra pequeña';
    document.getElementById('transparency-label').textContent = `Transparencia: ${transLabel} (${score}/100)`;

    // TIN / TAE bar
    const tinVal  = data.tin_ofertado;
    const taeAnun = data.tae_anunciada;
    const taeReal = data.tae_real_estimada;
    const tinTaeBar = document.getElementById('tin-tae-bar');
    if (tinVal != null || taeAnun != null) {
      tinTaeBar.style.display = 'flex';
      document.getElementById('tin-val').textContent     = tinVal  != null ? tinVal  + '%' : 'N/D';
      document.getElementById('tae-anun-val').textContent = taeAnun != null ? taeAnun + '%' : 'N/D';
      document.getElementById('tae-real-val').textContent = taeReal != null ? taeReal + '%' : 'N/D';
    } else {
      tinTaeBar.style.display = 'none';
    }

    // Numbers grid
    const nums   = data.numeros_reales || {};
    const gridEl = document.getElementById('numbers-grid');
    gridEl.innerHTML = '';
    const fmt  = v => v != null ? Math.round(v).toLocaleString('es-ES') + '€' : null;
    const fmt2 = v => v != null ? parseFloat(v).toLocaleString('es-ES', {minimumFractionDigits:2, maximumFractionDigits:2}) + '€' : null;

    const numItems = [
      { val: fmt2(nums.cuota_mensual),         lbl: 'Cuota mensual',        cls: '' },
      { val: fmt2(nums.total_pagado),           lbl: 'Total pagado',         cls: 'total' },
      { val: fmt2(nums.total_intereses),        lbl: 'Total intereses',      cls: 'cost' },
      { val: fmt(nums.coste_seguros_estimado),  lbl: 'Seguros vinculados',   cls: 'seguros' },
      { val: fmt(nums.coste_total_real),        lbl: 'COSTE TOTAL REAL',     cls: 'total' },
    ];

    numItems.forEach(it => {
      if (!it.val) return;
      gridEl.innerHTML += `
        <div class="number-item ${it.cls}">
          <span class="number-val">${escHtml(it.val)}</span>
          <span class="number-lbl">${escHtml(it.lbl)}</span>
        </div>`;
    });

    // Euribor risk
    const euribor    = data.riesgo_euribor || {};
    const euriborSec = document.getElementById('euribor-section');
    const euriborGrid = document.getElementById('euribor-grid');
    euriborGrid.innerHTML = '';
    if (euribor.aplica) {
      euriborSec.style.display = 'block';
      const scenarios = [
        { label: 'Euribor actual (~2.5%)', cuota: euribor.cuota_euribor_actual, cls: 'current' },
        { label: 'Si Euribor al 3%',       cuota: euribor.cuota_euribor_3,      cls: '' },
        { label: 'Si Euribor al 4%',       cuota: euribor.cuota_euribor_4,      cls: 'danger' },
        { label: 'Si Euribor al 5%',       cuota: euribor.cuota_euribor_5,      cls: 'danger' },
      ];
      scenarios.forEach(s => {
        if (!s.cuota) return;
        euriborGrid.innerHTML += `
          <div class="euribor-item ${s.cls}">
            <div class="euribor-euribor-val">${escHtml(s.label)}</div>
            <span class="euribor-cuota">${parseFloat(s.cuota).toLocaleString('es-ES', {minimumFractionDigits:2,maximumFractionDigits:2})}€</span>
            <div class="euribor-lbl">/ mes</div>
          </div>`;
      });
    } else {
      euriborSec.style.display = 'none';
    }

    // Initial costs
    const gastos     = data.costes_iniciales || {};
    const gastosSec  = document.getElementById('gastos-section');
    const gastosGrid = document.getElementById('gastos-grid');
    const gastosNota = document.getElementById('gastos-nota');
    gastosGrid.innerHTML = '';
    const gastoItems = [
      { val: gastos.tasacion, lbl: 'Tasación' },
      { val: gastos.notaria,  lbl: 'Notaría (copia)' },
      { val: gastos.registro, lbl: 'Registro' },
      { val: gastos.gestoria, lbl: 'Gestoría' },
    ];
    let hasGastos = false;
    gastoItems.forEach(gi => {
      if (!gi.val) return;
      hasGastos = true;
      gastosGrid.innerHTML += `
        <div class="gasto-item">
          <span class="gasto-val">${escHtml(gi.val)}</span>
          <span class="gasto-lbl">${escHtml(gi.lbl)}</span>
        </div>`;
    });
    if (gastos.total_estimado) {
      hasGastos = true;
      gastosGrid.innerHTML += `
        <div class="gasto-item gasto-total-item">
          <div>
            <span class="gasto-val">~${Math.round(gastos.total_estimado).toLocaleString('es-ES')}€</span>
            <span class="gasto-lbl">Total gastos iniciales</span>
          </div>
          <i class="fa fa-circle-info" style="color:rgba(255,255,255,0.5);font-size:1.2rem;"></i>
        </div>`;
    }
    if (gastos.nota) {
      hasGastos = true;
      gastosNota.textContent = gastos.nota;
    }
    gastosSec.style.display = hasGastos ? 'block' : 'none';

    // Condiciones vinculadas
    const vinculadas    = data.condiciones_vinculadas || [];
    const vinculadasSec = document.getElementById('vinculadas-section');
    const vinculadasList = document.getElementById('vinculadas-list');
    vinculadasList.innerHTML = '';
    if (vinculadas.length) {
      vinculadasSec.style.display = 'block';
      vinculadas.forEach(v => {
        vinculadasList.innerHTML += `
          <div class="vinculada-item">
            <i class="fa fa-circle-dot"></i>
            <span>${escHtml(v)}</span>
          </div>`;
      });
    } else {
      vinculadasSec.style.display = 'none';
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
      if (comp.vs_banco_digital) {
        compGrid.innerHTML += `
          <div class="comp-row">
            <div class="comp-icon"><i class="fa fa-mobile-screen"></i></div>
            <div class="comp-text"><strong>vs Banco digital sin vinculaciones</strong>${escHtml(comp.vs_banco_digital)}</div>
          </div>`;
      }
      if (comp.vs_tipo_opuesto) {
        compGrid.innerHTML += `
          <div class="comp-row">
            <div class="comp-icon"><i class="fa fa-scale-balanced"></i></div>
            <div class="comp-text"><strong>vs Tipo opuesto (fija vs variable)</strong>${escHtml(comp.vs_tipo_opuesto)}</div>
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

  // ── Reset ──
  function resetForm() {
    document.getElementById('results').style.display      = 'none';
    document.getElementById('form-section').style.display = 'block';
    document.getElementById('btn-analyze').disabled       = false;
    document.getElementById('share-section').style.display = 'none';
    document.getElementById('numero-duele').style.display  = 'none';
    offerEl.value = '';
    charNumEl.textContent = '0';
    urlIndicator.style.display = 'none';
    ['f-importe','f-plazo','f-tin','f-tae','f-diferencial'].forEach(id => {
      document.getElementById(id).value = '';
    });
    document.getElementById('f-tipo').value = '';
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  // ── Utils ──
  function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }
</script>

</body>
</html>
