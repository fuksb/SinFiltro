<?php
header('Content-Type: application/json; charset=UTF-8');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/ai-helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// ── HTML → plain text helper ──
function hipCleanToText(string $html): string {
    $text = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = strip_tags($text);
    $text = preg_replace('/[ \t]{2,}/', ' ', $text);
    $text = preg_replace('/(\s*\n\s*){3,}/', "\n\n", $text);
    return trim($text);
}

// ── URL fetching & fine-print extraction for mortgage pages ──
function fetchMortgagePage(string $url): ?string {
    $cookieJar = sys_get_temp_dir() . '/sf_hip_' . md5($url) . '.txt';

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 5,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36',
        CURLOPT_HTTPHEADER     => [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
            'Accept-Language: es-ES,es;q=0.9,en;q=0.8',
            'Accept-Encoding: gzip, deflate, br',
            'Connection: keep-alive',
            'Upgrade-Insecure-Requests: 1',
            'Sec-Fetch-Dest: document',
            'Sec-Fetch-Mode: navigate',
            'Sec-Fetch-Site: none',
            'Cache-Control: max-age=0',
        ],
        CURLOPT_ENCODING       => '',
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_COOKIEJAR      => $cookieJar,
        CURLOPT_COOKIEFILE     => $cookieJar,
    ]);

    $html  = curl_exec($ch);
    $errno = curl_errno($ch);
    $code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    @unlink($cookieJar);

    if ($errno || !$html || $code < 200 || $code >= 400) return null;

    // ── 1. Open Graph / page title ──
    $meta = [];
    if (preg_match('/<title[^>]*>([^<]+)<\/title>/i', $html, $m))
        $meta['title'] = html_entity_decode(trim($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    foreach (['og:title', 'og:description'] as $tag) {
        if (preg_match('/<meta[^>]+property=["\']' . preg_quote($tag, '/') . '["\'][^>]+content=["\']([^"\']*)["\'][^>]*\/?>/i', $html, $m))
            $meta[str_replace('og:', '', $tag)] = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    // ── 2. JSON-LD structured data ──
    $structuredData = '';
    if (preg_match_all('/<script[^>]+type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/si', $html, $matches)) {
        foreach ($matches[1] as $jsonStr) {
            $data = json_decode(trim($jsonStr), true);
            if (!$data) continue;
            $items = isset($data[0]) ? $data : [$data];
            foreach ($items as $item) {
                $type = $item['@type'] ?? '';
                if (in_array($type, ['LoanOrCredit', 'FinancialProduct', 'Product', 'Offer', 'BankOrCreditUnion'])) {
                    $structuredData = json_encode($item, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                    break 2;
                }
            }
        }
    }

    // ── 3. Fine print extraction (CRITICAL for mortgage offers) ──
    $finePrintParts = [];

    // 3a. <small> tags
    if (preg_match_all('/<small[^>]*>(.*?)<\/small>/si', $html, $sm)) {
        foreach ($sm[1] as $s) {
            $t = hipCleanToText($s);
            if (strlen($t) > 15) $finePrintParts[] = $t;
        }
    }

    // 3b. Legal/disclaimer class patterns — BBVA, Santander, ING, CaixaBank, Bankinter, Sabadell, Openbank...
    $legalClassPattern =
        '/class=["\'][^"\']*(?:disclaimer|legal|footnote|nota-legal|condiciones|'
      . 'terms|aviso|asterisk|financial-disclaimer|conditions-text|fine-print|'
      . 'mortgage-detail|loan-detail|hipoteca-conditions|product-disclaimer|'
      . 'legal-info|offer-conditions|tae-detail|tin-detail|rate-detail|'
      . 'cuadro-financiero|tabla-hipoteca|hipoteca-info|nota-tae|nota-tin|'
      . 'modal-legal|nota-legal-hipoteca|letra-pequena)[^"\']*["\']/i';
    $legalIdPattern =
        '/id=["\'][^"\']*(?:disclaimer|legal|footnote|condiciones|terms|notas|'
      . 'hipoteca|tae|tin|aviso|cuadro|mortgage|loan|fine-print)[^"\']*["\']/i';

    $blockPattern = '/<(?:div|section|article|aside|p|dl)\b([^>]*)>((?:[^<]|<(?!\/?(?:div|section|article|aside|p|dl)\b))*)<\/(?:div|section|article|aside|p|dl)>/si';
    if (preg_match_all($blockPattern, $html, $bm)) {
        foreach ($bm[1] as $i => $attrs) {
            if (preg_match($legalClassPattern, $attrs) || preg_match($legalIdPattern, $attrs)) {
                $t = hipCleanToText($bm[2][$i]);
                if (strlen($t) > 20) $finePrintParts[] = $t;
            }
        }
    }

    // 3c. Paragraphs/divs with mortgage-specific keywords
    $mortgageKeywords = '/TAE\s*[:\(]|TIN\s*[:\(]|tipo\s+de\s+inter[eé]s|eur[ií]bor|diferencial\s*[:\+]|cuota\s+(?:mensual|inicial|media|desde)|plazo\s*:|importe\s+(?:total|m[áa]ximo|m[íi]nimo)|coste\s+total|comisi[oó]n\s+de\s+apertura|seguro\s+de\s+vida|seguro\s+del\s+hogar|domiciliaci[oó]n|vinculaci[oó]n|FEIN|FIAE|per[íi]odo\s+de\s+carencia|amortizaci[oó]n\s+anticipada|subrogaci[oó]n|financiaci[oó]n|hipoteca\s+(?:fija|variable|mixta)/ui';
    if (preg_match_all('/<(?:p|div|li|td|dd|span)\b[^>]*>(.*?)<\/(?:p|div|li|td|dd|span)>/si', $html, $pm)) {
        foreach ($pm[1] as $p) {
            $t = hipCleanToText($p);
            if (strlen($t) > 10 && preg_match($mortgageKeywords, $t)) {
                $finePrintParts[] = $t;
            }
        }
    }

    // 3d. Tables (rate tables, cost breakdowns)
    if (preg_match_all('/<table[^>]*>(.*?)<\/table>/si', $html, $tm)) {
        foreach ($tm[1] as $table) {
            $t = hipCleanToText($table);
            if (preg_match($mortgageKeywords, $t) && strlen($t) > 20) {
                $finePrintParts[] = $t;
            }
        }
    }

    // 3e. Lists near mortgage context
    if (preg_match_all('/<ul[^>]*>(.*?)<\/ul>/si', $html, $ulm)) {
        foreach ($ulm[1] as $ul) {
            $ulText = hipCleanToText($ul);
            if (preg_match($mortgageKeywords, $ulText) && strlen($ulText) > 20) {
                $finePrintParts[] = $ulText;
            }
        }
    }

    // 3f. data-* attributes with mortgage data
    $dataMatches = [];
    if (preg_match_all('/data-(?:tae|tin|rate|euribor|differential|spread|cuota|amount|plazo|years|months|importe|comision|fee|monthly)[^=]*=["\']([^"\']+)["\']/i', $html, $dm)) {
        foreach ($dm[0] as $i => $fullAttr) {
            $key = preg_replace('/data-([^=]+)=.*/i', '$1', $fullAttr);
            $dataMatches[] = $key . ': ' . $dm[1][$i];
        }
    }
    if ($dataMatches) {
        $finePrintParts[] = "DATOS HIPOTECA (atributos data-*): " . implode(', ', array_unique($dataMatches));
    }

    // Deduplicate and limit fine print
    $finePrintParts = array_values(array_unique($finePrintParts));
    $finePrint = '';
    $fpLen = 0;
    foreach ($finePrintParts as $fp) {
        if ($fpLen + strlen($fp) > 4000) break;
        $finePrint .= $fp . "\n";
        $fpLen += strlen($fp);
    }

    // ── 4. Main body text ──
    $clean = preg_replace([
        '/<script\b[^>]*>.*?<\/script>/si',
        '/<style\b[^>]*>.*?<\/style>/si',
        '/<nav\b[^>]*>.*?<\/nav>/si',
        '/<header\b[^>]*>.*?<\/header>/si',
        '/<footer\b[^>]*>.*?<\/footer>/si',
        '/<!--.*?-->/si',
    ], '', $html);

    $text = hipCleanToText($clean ?? $html);

    if (strlen($text) < 250 && !$structuredData && !$finePrint) return null;

    // Fine print FIRST — never truncated
    $result = '';
    if (!empty($meta['title']))       $result .= "TÍTULO: {$meta['title']}\n";
    if (!empty($meta['description'])) $result .= "DESCRIPCIÓN OG: {$meta['description']}\n";
    $result .= "\n";
    if ($structuredData) $result .= "DATOS ESTRUCTURADOS (JSON-LD):\n{$structuredData}\n\n";
    if ($finePrint)      $result .= "=== LETRA PEQUEÑA / CONDICIONES LEGALES HIPOTECA (PRIORIDAD MÁXIMA) ===\n" . trim($finePrint) . "\n=== FIN LETRA PEQUEÑA ===\n\n";
    $result .= "TEXTO PRINCIPAL:\n" . mb_substr($text, 0, 4500);

    return $result;
}

// ── Input parsing ──
$input           = json_decode(file_get_contents('php://input'), true);
$rawOfferText    = trim($input['offer_text']      ?? '');
$offerText       = $rawOfferText;
$importeHipoteca = (int)($input['importe_hipoteca'] ?? 0);
$plazoAnos       = (int)($input['plazo_anos']       ?? 0);
$tinOfertado     = (float)($input['tin_ofertado']   ?? 0);
$taeAnunciada    = (float)($input['tae_anunciada']  ?? 0);
$diferencial     = (float)($input['diferencial']    ?? 0);
$tipoHipoteca    = strtoupper(trim($input['tipo_hipoteca'] ?? ''));
$fileBase64A    = $input['file_base64']       ?? null;
$fileMediaTypeA = $input['file_media_type']   ?? null;
$compareMode    = (bool)($input['compare_mode'] ?? false);
$offerTextB     = trim($input['offer_text_b'] ?? '');
$fileBase64B    = $input['file_base64_b']     ?? null;
$fileMediaTypeB = $input['file_media_type_b'] ?? null;

// ── URL detection & fetching ──
$isUrl     = (bool) preg_match('/^https?:\/\//i', $rawOfferText);
$urlSource = null;

if ($isUrl) {
    $fetched = fetchMortgagePage($rawOfferText);
    if ($fetched && strlen($fetched) > 250) {
        $urlSource = $rawOfferText;
        $offerText = $fetched;
    } else {
        http_response_code(422);
        echo json_encode([
            'error'           => 'No se pudo leer la página del banco automáticamente. Los bancos suelen usar JavaScript dinámico. Copia el texto de la oferta y pégalo directamente aquí.',
            'url_fetch_failed' => true,
        ]);
        exit;
    }
}

if (!$offerText && !$fileBase64A && !$importeHipoteca && !$compareMode) {
    http_response_code(400);
    echo json_encode(['error' => 'Pega la oferta de hipoteca o introduce al menos el importe y el plazo.']);
    exit;
}

// ── File size validation ──
if ($fileBase64A && strlen($fileBase64A) > 8_000_000) {
    http_response_code(400);
    echo json_encode(['error' => 'El archivo adjunto supera el límite de 6MB.']);
    exit;
}
if ($fileBase64B && strlen($fileBase64B) > 8_000_000) {
    http_response_code(400);
    echo json_encode(['error' => 'El archivo B adjunto supera el límite de 6MB.']);
    exit;
}

// ── PHP pre-calculations (amortización francesa) ──
$calcData = [];
$preCalc  = '';

if ($importeHipoteca > 0 && $plazoAnos > 0 && $tinOfertado > 0) {
    $mesesTotal = $plazoAnos * 12;
    $r = ($tinOfertado / 100) / 12;

    if ($r > 0) {
        $cuotaMensual = round($importeHipoteca * $r * pow(1 + $r, $mesesTotal) / (pow(1 + $r, $mesesTotal) - 1), 2);
    } else {
        $cuotaMensual = round($importeHipoteca / $mesesTotal, 2);
    }

    $totalPagado    = round($cuotaMensual * $mesesTotal, 2);
    $totalIntereses = round($totalPagado - $importeHipoteca, 2);
    $porcentajeInteres = round($totalIntereses / $importeHipoteca * 100, 1);

    $calcData['cuota_mensual']    = $cuotaMensual;
    $calcData['total_pagado']     = $totalPagado;
    $calcData['total_intereses']  = $totalIntereses;
    $calcData['importe_hipoteca'] = $importeHipoteca;
    $calcData['plazo_anos']       = $plazoAnos;

    // Euribor stress tests (for VARIABLE / MIXTA with diferencial)
    if ($diferencial > 0) {
        foreach ([3.0, 4.0, 5.0] as $euribor) {
            $tinStress = $euribor + $diferencial;
            $rStress   = ($tinStress / 100) / 12;
            $cuotaKey  = 'cuota_euribor_' . str_replace('.', '', (string)$euribor);
            $calcData[$cuotaKey] = round(
                $importeHipoteca * $rStress * pow(1 + $rStress, $mesesTotal) / (pow(1 + $rStress, $mesesTotal) - 1),
                2
            );
        }
    }

    $preCalc = "CÁLCULOS PRE-VERIFICADOS (usa estos valores exactos en numeros_reales, no los recalcules):
- Importe hipoteca: " . number_format($importeHipoteca, 0, ',', '.') . "€
- Plazo: {$plazoAnos} años ({$mesesTotal} cuotas mensuales)
- TIN: {$tinOfertado}%
- Cuota mensual (amortización francesa): " . number_format($cuotaMensual, 2, ',', '.') . "€/mes
- Total pagado al vencer la hipoteca: " . number_format($totalPagado, 2, ',', '.') . "€
- Total intereses generados: " . number_format($totalIntereses, 2, ',', '.') . "€ ({$porcentajeInteres}% del importe prestado)";

    if ($diferencial > 0) {
        $preCalc .= "\n- Diferencial sobre Euribor: +{$diferencial}%";
        foreach ([3.0, 4.0, 5.0] as $euribor) {
            $k = 'cuota_euribor_' . str_replace('.', '', (string)$euribor);
            if (isset($calcData[$k])) {
                $preCalc .= "\n- Cuota si Euribor llega a {$euribor}%: " . number_format($calcData[$k], 2, ',', '.') . "€/mes (TIN total " . ($euribor + $diferencial) . "%)";
            }
        }
    }
}

// ── Build prompt sections ──
if ($urlSource) {
    $offerSection = "CONTENIDO EXTRAÍDO DE LA WEB DEL BANCO (fuente: {$urlSource}):\n\"\"\"\n{$offerText}\n\"\"\"\n";
} elseif ($offerText) {
    $offerSection = "TEXTO DE LA OFERTA HIPOTECARIA:\n\"\"\"\n{$offerText}\n\"\"\"\n";
} else {
    $offerSection = '';
}

$datosSection = '';
if ($importeHipoteca || $plazoAnos || $tinOfertado || $taeAnunciada || $diferencial || $tipoHipoteca) {
    $datosSection = "DATOS INTRODUCIDOS POR EL USUARIO:\n";
    if ($importeHipoteca) $datosSection .= "- Importe hipoteca: {$importeHipoteca}€\n";
    if ($plazoAnos)       $datosSection .= "- Plazo: {$plazoAnos} años\n";
    if ($tinOfertado)     $datosSection .= "- TIN anunciado: {$tinOfertado}%\n";
    if ($taeAnunciada)    $datosSection .= "- TAE anunciada: {$taeAnunciada}%\n";
    if ($diferencial)     $datosSection .= "- Diferencial sobre Euribor: +{$diferencial}%\n";
    if ($tipoHipoteca)    $datosSection .= "- Tipo de hipoteca declarado: {$tipoHipoteca}\n";
}

// ── Build offer sections for prompt ──
$offerSectionB = '';
if ($compareMode) {
    if ($fileBase64B && $offerTextB) {
        $offerSectionB = "TEXTO OFERTA B:\n\"\"\"\n" . mb_substr($offerTextB, 0, 3000) . "\"\"\"\n(Ver también el segundo documento adjunto)";
    } elseif ($fileBase64B) {
        $offerSectionB = "(Ver el segundo documento adjunto — Oferta B)";
    } elseif ($offerTextB) {
        $offerSectionB = "TEXTO OFERTA B:\n\"\"\"\n" . mb_substr($offerTextB, 0, 3000) . "\"\"\"";
    } else {
        $offerSectionB = "(No se proporcionó contenido para la Oferta B)";
    }
}

$comparePreamble = $compareMode ? "=== MODO COMPARATIVA ===\nAnaliza DOS contratos/ofertas.\n\n=== OFERTA A ===\n" : '';
$compareSuffix   = $compareMode ? "\n\n=== OFERTA B ===\n{$offerSectionB}" : '';
$compareSchemaExtra = $compareMode ? '

MODO COMPARATIVA — Responde con este JSON envolvente:
{
  "ganador": "A|B|EMPATE",
  "motivo_ganador": "frase directa",
  "diferencias_principales": ["diff1","diff2","diff3"],
  "ahorro_cambio_anual": number_or_null,
  "analisis_a": { ...JSON completo del tool para Oferta A... },
  "analisis_b": { ...JSON completo del tool para Oferta B... }
}' : '';

$prompt = <<<PROMPT
Eres el analizador de hipotecas más brutal y honesto de España. Tu misión es proteger al usuario de bancos que esconden el coste real de sus hipotecas detrás de condiciones vinculadas, TAE de referencia falsas y letra pequeña diseñada para confundir. Sin rodeos. Sin diplomacia bancaria. Sin suavizar.

CONOCIMIENTO DEL MERCADO HIPOTECARIO ESPAÑOL 2025 QUE DEBES APLICAR:

Tipos de interés de referencia (comienzos de 2025):
- Euribor 12 meses: ~2.5% (bajando desde máximo 4.16% en octubre 2023)
- Hipoteca fija MUY competitiva: TIN 3.0-3.2%, TAE 3.4-3.8%
- Hipoteca fija normal: TIN 3.3-4.0%, TAE 3.8-4.5%
- Hipoteca fija cara: TIN > 4.5% — no merece la pena, mejor hipoteca variable + ahorro en diferencia
- Hipoteca variable MUY competitiva: Euribor + 0.49-0.69%
- Hipoteca variable cara: diferencial > 1.3%
- Hipoteca mixta: 3-10 años fijo + resto variable. Útil si crees que el Euribor bajará.

Ley 5/2019 de Contratos de Crédito Inmobiliario (debes mencionar lo relevante):
- El BANCO paga: AJD (Actos Jurídicos Documentados), timbre, sus gastos de gestoría y notaría
- El COMPRADOR paga: tasación (300-700€), su copia de escritura notaría (300-600€), registro (200-500€), gestoría (200-400€)
- FEIN (Ficha Europea de Información Normalizada): el banco DEBE entregarla mínimo 10 días antes de la firma. Si no lo hace, es ilegal.
- FIAE (Ficha de Advertencias Estandarizadas): el notario debe verificar que el cliente la ha leído y entendido
- Período de reflexión OBLIGATORIO: mínimo 10 días naturales entre FEIN y firma
- Comisión amortización anticipada MÁXIMA LEGAL:
  · Variable: 0.25% sobre el capital amortizado los 3 primeros años, 0% después
  · Fija/Mixta (tramo fijo): 2% los 10 primeros años, 1.5% después
- Cláusula suelo: ILEGAL desde 2016 (STJUE). Si aparece en cualquier contrato, es nula de pleno derecho.
- Subrogación activa: puedes cambiar tu hipoteca de banco. El nuevo banco paga los gastos de subrogación.
- Novación: renegociar con tu mismo banco (ellos no están obligados a aceptar).

EL TRUCO DE LA "TAE DE REFERENCIA" — el engaño más habitual de la banca española:
- Los bancos anuncian una TAE muy baja que solo es válida si contratas 4-6 productos vinculados
- La TAE "de referencia" o "con bonificaciones" NO es la TAE real para la mayoría de usuarios
- Sin cumplir TODAS las condiciones vinculadas, la TAE real es significativamente más alta
- Condiciones típicas que reducen el diferencial/tipo: nómina domiciliada (mínimo 1.500-3.000€/mes), seguro de vida del propio banco, seguro de hogar del banco, tarjeta de crédito activa con gasto mínimo mensual, plan de pensiones, alarma del hogar, consumo tarjeta mínimo.
- Cada condición no cumplida: típicamente +0.10 a +0.30 puntos porcentuales al diferencial
- SIEMPRE señala la diferencia entre TAE con vinculaciones y TAE sin vinculaciones.

COSTES OCULTOS Y TRAMPAS FRECUENTES:
- Seguros vinculados del banco: un seguro de vida del banco puede costar 3-5x más que en el mercado libre. A 25-30 años, puede representar 15.000-35.000€ extra que NO aparece en la TAE
- Comisión de apertura: 0-1% del préstamo. Suma al coste real aunque no aparezca en la cuota
- Tasación del banco: tienen tasadoras propias que pueden infravalorar el piso. Tienes derecho legal a elegir una tasadora homologada diferente.
- IRPH (Índice de Referencia de Préstamos Hipotecarios): alternativa al Euribor, históricamente siempre más alto. Si te lo ofrecen, rechaza.
- Período de carencia: solo pagas intereses durante X meses. Parece bien pero al final el total pagado es mayor.
- Cuota creciente: empieza baja y sube. Trampa clásica para jóvenes sin capacidad de ahorro.
- Hipoteca en moneda extranjera: riesgo de tipo de cambio enorme. Huye.

BANCOS DIGITALES SIN VINCULACIONES (referencia para comparativa):
- Openbank: hipoteca variable Euribor + 0.60% sin vinculaciones (solo cuenta corriente)
- MyInvestor: hipoteca fija 3.09% TIN sin vinculaciones (20 años)
- EVO Banco: hipoteca fija ~3.2% TIN con mínima vinculación
- COINC: sin vinculaciones obligatorias
- Comparativa clave: un banco tradicional al 3.5% TIN con seguros puede costar más que un banco digital al 3.8% TIN sin vinculaciones.

COMPARATIVAS SIEMPRE CON CIFRAS EXACTAS:
- No digas "los seguros son caros". Di: "el seguro de vida vinculado cuesta ~800€/año vs ~250€ en el mercado libre; en 25 años son 13.750€ extra que no aparecen en la TAE"
- No digas "la TAE real es mayor". Di: "la TAE anunciada del 3.6% incluye 5 vinculaciones; sin ellas sería ~4.2%, equivalente a pagar X€ más en total"
- No digas "hay opciones mejores". Di: "Openbank ofrece Euribor +0.60% sin vinculaciones; con tu préstamo de X€ a Y años eso son Z€ menos en intereses"

{$comparePreamble}{$offerSection}{$compareSuffix}
{$datosSection}
{$preCalc}

LEE TODA LA LETRA PEQUEÑA — es lo más crítico:
Cuando el contenido viene de una URL, el bloque "=== LETRA PEQUEÑA / CONDICIONES LEGALES HIPOTECA ===" contiene lo que el banco esconde. DEBES extraer:
- TAE real y TAE de referencia (con y sin vinculaciones)
- TIN inicial y diferencial exacto sobre Euribor
- LISTA COMPLETA de condiciones vinculadas y penalización por no cumplirlas
- Comisión de apertura exacta (en % y en €)
- Sistema de amortización (casi siempre francés)
- Índice de referencia (Euribor 12 meses, IRPH...)
- Plazo del tramo fijo si es mixta, y qué pasa después
- Importe máximo financiable, LTV (% sobre tasación o valor compraventa)
- Quién paga qué gastos
- Condiciones de amortización anticipada y subrogación
- Fecha de validez de la oferta

RESPONDE ÚNICAMENTE con un objeto JSON válido, sin markdown, sin texto antes ni después:

{
  "tipo_hipoteca": "[exactamente uno de: FIJA, VARIABLE, MIXTA]",
  "puntuacion_transparencia": [0-100: 100=completamente honesto, 0=diseñado para engañar],
  "veredicto": "[máximo 15 palabras. Sin adornos. Di si es buena, mala o una trampa]",
  "numeros_reales": {
    "importe_hipoteca": [importe prestado en €, número entero, o null],
    "plazo_anos": [plazo en años, número entero, o null],
    "cuota_mensual": [cuota mensual en €, 2 decimales, o null],
    "total_pagado": [total pagado al vencer la hipoteca en €, 2 decimales, o null],
    "total_intereses": [total intereses en €, 2 decimales, o null],
    "coste_seguros_estimado": [coste total estimado de seguros vinculados durante toda la vida del préstamo, número entero o null],
    "coste_total_real": [total_pagado + coste_seguros_estimado, número entero o null]
  },
  "tin_ofertado": [TIN anunciado con 2 decimales, o null],
  "tae_anunciada": [TAE que anuncian (de referencia, con vinculaciones), número, o null],
  "tae_real_estimada": [TAE sin vinculaciones o TAE real total con todos los costes incluidos, número, o null],
  "condiciones_vinculadas": [
    "[condición exacta requerida para obtener el tipo anunciado. Vacío [] si no hay condiciones vinculadas]"
  ],
  "costes_iniciales": {
    "tasacion": "[estimación realista: '400-600€ aprox.' Si depende del importe, calcúlalo]",
    "notaria": "[estimación: '400-700€ aprox. (copia escritura comprador)']",
    "registro": "[estimación: '200-450€ aprox.']",
    "gestoria": "[estimación: '200-350€ aprox.']",
    "total_estimado": [suma de los 4 costes anteriores, número entero],
    "nota": "[1 frase: 'Según Ley 5/2019, el banco paga AJD y sus gastos; tú pagas tasación, tu notaría y registro']"
  },
  "riesgo_euribor": {
    "aplica": [true si es VARIABLE o MIXTA con tramo variable, false si es FIJA pura],
    "diferencial": [diferencial sobre Euribor, número con 2 decimales, o null],
    "cuota_euribor_actual": [cuota estimada con Euribor al 2.5%, o null],
    "cuota_euribor_3": [cuota si Euribor sube al 3%, o null],
    "cuota_euribor_4": [cuota si Euribor sube al 4%, o null],
    "cuota_euribor_5": [cuota si Euribor sube al 5%, o null]
  },
  "trampa": [
    "[cláusula abusiva, coste oculto o punto crítico que el usuario DEBE conocer. Siempre 2-4 puntos]"
  ],
  "ventajas": [
    "[ventaja real y objetiva si existe. Array vacío [] si no hay ninguna real]"
  ],
  "comparativa": {
    "vs_banco_digital": "[comparación directa con banco digital sin vinculaciones (Openbank, MyInvestor, EVO). SIEMPRE con cifras exactas en €: cuánto pagarías más/menos en total]",
    "vs_tipo_opuesto": "[si es FIJA: compara con variable actual (Euribor + diferencial competitivo). Si es VARIABLE: compara con fija 3.2% TIN. SIEMPRE con cifras concretas]",
    "recomendacion": "[2-3 frases directas. Qué haría alguien con criterio financiero que no quiere venderte nada. Menciona si los seguros vinculados anulan el beneficio del tipo bajo. Sin suavizar.]"
  },
  "preguntas_clave": [
    "[pregunta directa y específica que el usuario debe hacer al banco antes de firmar]",
    "[pregunta directa y específica que el usuario debe hacer al banco antes de firmar]",
    "[pregunta directa y específica que el usuario debe hacer al banco antes de firmar]"
  ]
}

CRITERIOS DE PUNTUACIÓN:
Transparencia (puntuacion_transparencia):
- 80-100: TAE sin vinculaciones clara, todos los costes detallados, sin letra pequeña oculta
- 50-79: Datos principales OK pero TAE de referencia poco clara o vinculaciones enterradas
- 20-49: Solo TAE de referencia, sin desglose de vinculaciones, seguros no cuantificados
- 0-19: Diseñado para confundir. TAE irreal, condiciones imposibles de cumplir, datos clave ocultos

REGLAS:
- "trampa": Siempre 2-4 puntos. Si hay seguros vinculados del banco, SIEMPRE es uno de los puntos.
- "ventajas": Solo ventajas REALES. Sin seguros vinculados y TAE competitiva sin condiciones, eso es una ventaja. [] si no hay.
- "preguntas_clave": Exactamente 3, específicas y directas.
- "riesgo_euribor.aplica": true SOLO para VARIABLE y MIXTA.
- Si faltan datos para calcular algo, pon null pero explícalo en trampa/veredicto.
{$compareSchemaExtra}
PROMPT;

// ── Disk cache ──
$cacheDir = __DIR__ . '/data/cache_hipotecas';
if (!is_dir($cacheDir)) { @mkdir($cacheDir, 0755, true); }

$cacheKey  = md5($rawOfferText . '|' . $importeHipoteca . '|' . $plazoAnos . '|' . $tinOfertado . '|' . $taeAnunciada . '|' . $diferencial . '|' . $tipoHipoteca . '|' . ($fileBase64A ? md5($fileBase64A) : '') . '|' . ($compareMode ? '1' : '0') . '|' . $offerTextB . '|' . ($fileBase64B ? md5($fileBase64B) : ''));
$cacheFile = $cacheDir . '/' . $cacheKey . '.json';
$cacheTTL  = 60 * 60 * 24 * 7;  // 7 days

if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTTL) {
    $cached = file_get_contents($cacheFile);
    if ($cached) { echo $cached; exit; }
}

$content = callAI($prompt, $fileBase64A, $fileMediaTypeA, $compareMode ? $fileBase64B : null, $compareMode ? $fileMediaTypeB : null, $compareMode ? 4000 : 2500);

if ($content === false) {
    http_response_code(503);
    echo json_encode(['error' => 'No se pudo conectar con la IA. Inténtalo de nuevo.']);
    exit;
}

$analysis = json_decode(trim($content), true);

if (!$analysis || (!isset($analysis['veredicto']) && !isset($analysis['ganador']))) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al procesar el análisis. Inténtalo de nuevo.']);
    exit;
}

// For compare mode, pass through the ganador/diferencias fields if present
if ($compareMode && isset($analysis['ganador'])) {
    $validGanador = ['A', 'B', 'EMPATE'];
    if (!in_array($analysis['ganador'] ?? '', $validGanador)) {
        $analysis['ganador'] = 'EMPATE';
    }
    if (!is_array($analysis['diferencias_principales'] ?? null)) {
        $analysis['diferencias_principales'] = [];
    }
}

// Override with pre-calculated numbers for accuracy
if (!empty($calcData)) {
    if (isset($calcData['cuota_mensual']))    $analysis['numeros_reales']['cuota_mensual']    = $calcData['cuota_mensual'];
    if (isset($calcData['total_pagado']))     $analysis['numeros_reales']['total_pagado']     = $calcData['total_pagado'];
    if (isset($calcData['total_intereses']))  $analysis['numeros_reales']['total_intereses']  = $calcData['total_intereses'];
    if (isset($calcData['importe_hipoteca'])) $analysis['numeros_reales']['importe_hipoteca'] = $calcData['importe_hipoteca'];
    if (isset($calcData['plazo_anos']))       $analysis['numeros_reales']['plazo_anos']       = $calcData['plazo_anos'];
    if ($tinOfertado)  $analysis['tin_ofertado']  = $tinOfertado;
    if ($taeAnunciada) $analysis['tae_anunciada'] = $taeAnunciada;

    // Euribor stress tests
    if ($diferencial > 0) {
        $analysis['riesgo_euribor']['diferencial'] = $diferencial;
        foreach ([['30', '3'], ['40', '4'], ['50', '5']] as [$calcSuffix, $label]) {
            $k = 'cuota_euribor_' . $calcSuffix;
            if (isset($calcData[$k])) {
                $analysis['riesgo_euribor']['cuota_euribor_' . $label] = $calcData[$k];
            }
        }
    }

    // Recalculate coste_total_real if we have seguros
    if (isset($analysis['numeros_reales']['coste_seguros_estimado']) && $analysis['numeros_reales']['coste_seguros_estimado']) {
        $analysis['numeros_reales']['coste_total_real'] = (int)round(
            $calcData['total_pagado'] + $analysis['numeros_reales']['coste_seguros_estimado']
        );
    }
}

// Sanitize
$analysis['puntuacion_transparencia'] = max(0, min(100, (int)($analysis['puntuacion_transparencia'] ?? 50)));

$validTipos = ['FIJA', 'VARIABLE', 'MIXTA'];
if (!in_array($analysis['tipo_hipoteca'] ?? '', $validTipos)) {
    $analysis['tipo_hipoteca'] = 'FIJA';
}

if (!isset($analysis['condiciones_vinculadas']) || !is_array($analysis['condiciones_vinculadas'])) {
    $analysis['condiciones_vinculadas'] = [];
}
if (!isset($analysis['riesgo_euribor']) || !is_array($analysis['riesgo_euribor'])) {
    $analysis['riesgo_euribor'] = ['aplica' => false];
}
if (!isset($analysis['costes_iniciales']) || !is_array($analysis['costes_iniciales'])) {
    $analysis['costes_iniciales'] = [
        'total_estimado' => 2000,
        'nota' => 'Estima entre 1.000-2.500€ en gastos propios (tasación, notaría, registro, gestoría).'
    ];
}

$finalJson = json_encode($analysis, JSON_UNESCAPED_UNICODE);

@file_put_contents($cacheFile, $finalJson, LOCK_EX);
_pruneCache($cacheDir, $cacheTTL);

echo $finalJson;
