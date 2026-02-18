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

// ── HTML utility ──
function cleanToText(string $html): string {
    $text = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = strip_tags($text);
    $text = preg_replace('/[ \t]{2,}/', ' ', $text);
    $text = preg_replace('/(\s*\n\s*){3,}/', "\n\n", $text);
    return trim($text);
}

// ── URL fetching & fine print extraction for investment pages ──
function fetchInvestmentPage(string $url): ?string {
    $cookieJar = sys_get_temp_dir() . '/sf_inv_' . md5($url) . '.txt';

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
        CURLOPT_ENCODING       => '',   // auto-decompress
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
                if (in_array($type, ['FinancialProduct', 'InvestmentFund', 'Product', 'Offer'])) {
                    $structuredData = json_encode($item, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                    break 2;
                }
            }
        }
    }

    // ── 3. Fine print / legal text extraction (CRITICAL for investment pages) ──
    $finePrintParts = [];

    // 3a. <small> tags
    if (preg_match_all('/<small[^>]*>(.*?)<\/small>/si', $html, $sm)) {
        foreach ($sm[1] as $s) {
            $t = cleanToText($s);
            if (strlen($t) > 15) $finePrintParts[] = $t;
        }
    }

    // 3b. Blocks with legal/disclaimer/conditions class or ID (investment-specific)
    $legalClassPattern =
        '/class=["\'][^"\']*(?:disclaimer|legal|footnote|nota-legal|condiciones|'
      . 'terms|aviso|asterisk|dfi|kid|folleto|prospectus|'
      . 'legal-text|conditions-text|comision|gastos|ter|ocf|'
      . 'rentabilidad|benchmark|riesgo|advertencia|warning)[^"\']*["\']/i';
    $legalIdPattern =
        '/id=["\'][^"\']*(?:disclaimer|legal|footnote|condiciones|terms|notas|'
      . 'dfi|kid|folleto|comisiones|gastos|aviso|riesgo)[^"\']*["\']/i';

    $blockPattern = '/<(?:div|section|article|aside|p|dl)\b([^>]*)>((?:[^<]|<(?!\/?(?:div|section|article|aside|p|dl)\b))*)<\/(?:div|section|article|aside|p|dl)>/si';
    if (preg_match_all($blockPattern, $html, $bm)) {
        foreach ($bm[1] as $i => $attrs) {
            if (preg_match($legalClassPattern, $attrs) || preg_match($legalIdPattern, $attrs)) {
                $t = cleanToText($bm[2][$i]);
                if (strlen($t) > 20) $finePrintParts[] = $t;
            }
        }
    }

    // 3c. Paragraphs and divs that explicitly mention investment keywords
    $investmentKeywords = '/comisi[oó]n|gesti[oó]n|TER|OCF|rentabilidad|benchmark|fondo|inversi[oó]n|patrimonio|partici[pó]|reembolso|suscripci[oó]n|depositaria|distribuci[oó]n|[ií]ndice|volatilidad|riesgo|ISIN|DFI|KID/ui';
    if (preg_match_all('/<(?:p|div|li|td|dd)\b[^>]*>(.*?)<\/(?:p|div|li|td|dd)>/si', $html, $pm)) {
        foreach ($pm[1] as $p) {
            $t = cleanToText($p);
            if (strlen($t) > 10 && preg_match($investmentKeywords, $t)) {
                $finePrintParts[] = $t;
            }
        }
    }

    // 3d. All <li> in lists near investment context
    if (preg_match_all('/<ul[^>]*>(.*?)<\/ul>/si', $html, $ulm)) {
        foreach ($ulm[1] as $ul) {
            $ulText = cleanToText($ul);
            if (preg_match($investmentKeywords, $ulText) && strlen($ulText) > 20) {
                $finePrintParts[] = $ulText;
            }
        }
    }

    // 3e. Tables (fee tables, performance tables)
    if (preg_match_all('/<table[^>]*>(.*?)<\/table>/si', $html, $tm)) {
        foreach ($tm[1] as $table) {
            $t = cleanToText($table);
            if (preg_match($investmentKeywords, $t) && strlen($t) > 20) {
                $finePrintParts[] = $t;
            }
        }
    }

    // 3f. data-* attributes that contain fund/investment info
    $dataMatches = [];
    if (preg_match_all('/data-(?:ter|ocf|isin|rentabilidad|comision|gestion|fondo|patrimonio|benchmark|riesgo|volatilidad|categoria)[^=\s>]*=["\']([^"\']+)["\']/i', $html, $dm)) {
        foreach ($dm[0] as $i => $fullAttr) {
            $key = preg_replace('/data-([^=]+)=.*/i', '$1', $fullAttr);
            $dataMatches[] = $key . ': ' . $dm[1][$i];
        }
    }
    if ($dataMatches) {
        $finePrintParts[] = "DATOS FONDO (atributos data-*): " . implode(', ', array_unique($dataMatches));
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

    // ── 4. Main body text (stripped) ──
    $clean = preg_replace([
        '/<script\b[^>]*>.*?<\/script>/si',
        '/<style\b[^>]*>.*?<\/style>/si',
        '/<nav\b[^>]*>.*?<\/nav>/si',
        '/<header\b[^>]*>.*?<\/header>/si',
        '/<footer\b[^>]*>.*?<\/footer>/si',
        '/<!--.*?-->/si',
    ], '', $html);

    $text = cleanToText($clean ?? $html);

    if (strlen($text) < 250 && !$structuredData && !$finePrint) return null;

    // ── Assemble result — fine print FIRST so it doesn't get truncated ──
    $result = '';
    if (!empty($meta['title']))       $result .= "TÍTULO: {$meta['title']}\n";
    if (!empty($meta['description'])) $result .= "DESCRIPCIÓN OG: {$meta['description']}\n";
    $result .= "\n";
    if ($structuredData) $result .= "DATOS ESTRUCTURADOS (JSON-LD):\n{$structuredData}\n\n";
    if ($finePrint)      $result .= "=== LETRA PEQUEÑA / CONDICIONES LEGALES (PRIORIDAD MÁXIMA) ===\n" . trim($finePrint) . "\n=== FIN LETRA PEQUEÑA ===\n\n";
    $result .= "TEXTO PRINCIPAL DE LA FICHA:\n" . mb_substr($text, 0, 4500);

    return $result;
}

// ── Input ──
$input         = json_decode(file_get_contents('php://input'), true);
$rawOfferText  = trim($input['offer_text']        ?? '');
$offerText     = $rawOfferText;
$comisionTer   = (float)($input['comision_ter']   ?? 0);
$importeInv    = (float)($input['importe_inv']    ?? 0);
$plazoAnos     = (int)($input['plazo_anos']       ?? 0);
$rentHistorica = (float)($input['rent_historica'] ?? 0);
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
    $fetched = fetchInvestmentPage($rawOfferText);
    if ($fetched && strlen($fetched) > 250) {
        $urlSource = $rawOfferText;
        $offerText = $fetched;
    } else {
        http_response_code(422);
        echo json_encode([
            'error'            => 'No se pudo leer el contenido del enlace automáticamente. Algunas fichas de fondos cargan con JavaScript. Copia el texto de la ficha y pégalo directamente aquí.',
            'url_fetch_failed' => true,
        ]);
        exit;
    }
}

if (!$offerText && !$fileBase64A && !$comisionTer) {
    http_response_code(400);
    echo json_encode(['error' => 'Pega el nombre del fondo o las condiciones, o introduce al menos la comisión anual (TER).']);
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

// ── Disk cache ──
$cacheDir = __DIR__ . '/data/cache_inversiones';
if (!is_dir($cacheDir)) @mkdir($cacheDir, 0755, true);

$cacheKey  = md5($rawOfferText . '|' . $comisionTer . '|' . $importeInv . '|' . $plazoAnos . '|' . $rentHistorica . '|' . ($fileBase64A ? md5($fileBase64A) : '') . '|' . ($compareMode ? '1' : '0') . '|' . $offerTextB . '|' . ($fileBase64B ? md5($fileBase64B) : ''));
$cacheFile = $cacheDir . '/' . $cacheKey . '.json';
$cacheTTL  = 7 * 24 * 3600; // 7 days

if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTTL) {
    echo file_get_contents($cacheFile);
    exit;
}

// ── PHP pre-calculations (heart of the tool) ──
$preCalc  = '';
$calcData = [];

// Assume 7% gross annual return (long-term MSCI World / S&P500 reference)
$rentBruta = 0.07;

if ($comisionTer > 0 && $importeInv > 0) {
    $comisionDecimal  = $comisionTer / 100;
    $rentNetaActivo   = $rentBruta - $comisionDecimal;
    // Reference low-cost index fund: 0.20% TER
    $terIndexado      = 0.002;
    $rentNetaIndexado = $rentBruta - $terIndexado;

    // Calculate at 10, 20, 30 years
    foreach ([10, 20, 30] as $years) {
        $valorActivo   = round($importeInv * pow(1 + $rentNetaActivo,   $years), 0);
        $valorIndexado = round($importeInv * pow(1 + $rentNetaIndexado, $years), 0);
        $impacto       = round($valorIndexado - $valorActivo, 0);
        $calcData["valor_{$years}a_activo"]   = $valorActivo;
        $calcData["valor_{$years}a_indexado"] = $valorIndexado;
        $calcData["impacto_{$years}a"]        = $impacto;
    }

    // Main fields for numeros_reales
    $calcData['impacto_comisiones_10a']          = $calcData['impacto_10a'];
    $calcData['impacto_comisiones_20a']          = $calcData['impacto_20a'];
    $calcData['impacto_comisiones_30a']          = $calcData['impacto_30a'];
    $calcData['valor_proyectado_neto_20a']       = $calcData['valor_20a_activo'];
    $calcData['valor_indexado_proyectado_20a']   = $calcData['valor_20a_indexado'];
    $calcData['diferencia_total_20a']            = $calcData['impacto_20a'];

    $preCalc = "CÁLCULOS PRE-VERIFICADOS (usa exactamente estos valores, no los recalcules):\n"
        . "- Importe inicial: " . number_format($importeInv, 0, ',', '.') . "€\n"
        . "- Comisión anual (TER): {$comisionTer}%\n"
        . "- Asumiendo 7% rentabilidad bruta anual (referencia MSCI World largo plazo):\n"
        . "  - A 10 años con este fondo: " . number_format($calcData['valor_10a_activo'],   0, ',', '.') . "€"
        .   " | con indexado: "            . number_format($calcData['valor_10a_indexado'], 0, ',', '.') . "€"
        .   " | diferencia: "              . number_format($calcData['impacto_10a'],        0, ',', '.') . "€\n"
        . "  - A 20 años con este fondo: " . number_format($calcData['valor_20a_activo'],   0, ',', '.') . "€"
        .   " | con indexado: "            . number_format($calcData['valor_20a_indexado'], 0, ',', '.') . "€"
        .   " | diferencia: "              . number_format($calcData['impacto_20a'],        0, ',', '.') . "€\n"
        . "  - A 30 años con este fondo: " . number_format($calcData['valor_30a_activo'],   0, ',', '.') . "€"
        .   " | con indexado: "            . number_format($calcData['valor_30a_indexado'], 0, ',', '.') . "€"
        .   " | diferencia: "              . number_format($calcData['impacto_30a'],        0, ',', '.') . "€\n"
        . "- Alternativa indexada (TER 0.20%): ahorrarías "
        . number_format($calcData['impacto_20a'], 0, ',', '.') . "€ a 20 años";
}

// ── Build prompt sections ──
if ($urlSource) {
    $offerSection = "CONTENIDO EXTRAÍDO DE LA FICHA DEL FONDO (fuente: {$urlSource}):\n\"\"\"\n{$offerText}\n\"\"\"\n";
} elseif ($offerText) {
    $offerSection = "TEXTO DEL FONDO / PRODUCTO DE INVERSIÓN:\n\"\"\"\n{$offerText}\n\"\"\"\n";
} else {
    $offerSection = '';
}

$datosSection = '';
if ($comisionTer || $importeInv || $plazoAnos || $rentHistorica) {
    $datosSection = "DATOS INTRODUCIDOS POR EL USUARIO:\n";
    if ($comisionTer)   $datosSection .= "- Comisión anual (TER/OCF): {$comisionTer}%\n";
    if ($importeInv)    $datosSection .= "- Importe a invertir: " . number_format($importeInv, 0, ',', '.') . "€\n";
    if ($plazoAnos)     $datosSection .= "- Plazo de inversión: {$plazoAnos} años\n";
    if ($rentHistorica) $datosSection .= "- Rentabilidad histórica anunciada: {$rentHistorica}%\n";
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
Eres el analizador de inversiones más brutal y honesto de España. Tu misión es mostrar el impacto real de las comisiones en €, desmontar el mito de la gestión activa y dirigir al usuario hacia alternativas baratas y eficientes. Los datos mandan, no los folletos comerciales.

CONOCIMIENTO DEL MERCADO DE INVERSIONES ESPAÑOL 2025:

COMISIONES Y SU IMPACTO — lo más importante de todo:
- TER/OCF (Total Expense Ratio / Ongoing Charges Figure): coste total anual del fondo.
- Fondos de gestión activa españoles: 1.5-2.5%/año de TER. Algunos llegan al 3%+.
- Fondos indexados (ETFs y fondos índice): 0.05-0.30%/año.
- A 20 años, la diferencia entre un fondo activo al 2% vs indexado al 0.2% (asumiendo mismo rendimiento bruto 7%):
  * 10.000€ iniciales: activo ~22.000€ | indexado ~27.000€ → diferencia ~5.000€
  * 50.000€ iniciales: activo ~111.000€ | indexado ~135.000€ → diferencia ~24.000€
  * 100.000€ iniciales: activo ~222.000€ | indexado ~270.000€ → diferencia ~48.000€
- Esta diferencia NO aparece en el folleto. Es el coste invisible que se lleva la gestora.

RENDIMIENTO VS BENCHMARKS:
- Solo el 20-25% de fondos de gestión activa supera a su índice de referencia a 10 años.
- A 15-20 años: apenas el 10-15%.
- Fuente: SPIVA (S&P Indices Versus Active) — dato verificable.
- Conclusión: pagar 1.5-2.5% anual por gestión activa es, estadísticamente, tirar dinero.

TIPOS DE PRODUCTOS:
- Fondo de gestión activa: equipo de gestores elige valores. Cara, raramente bate al mercado.
- Fondo indexado (index fund): replica un índice (MSCI World, S&P 500, Ibex 35). Barato, eficiente.
- ETF (Exchange Traded Fund): fondo indexado que cotiza en bolsa. Aún más barato (desde 0.05%).
- Plan de pensiones: vehículo con ventaja fiscal en aportaciones (deduce hasta 1.500€/año del IRPF). ILÍQUIDO hasta jubilación (o invalidez, dependencia, desempleo larga duración). Solo compensa si tramo fiscal es 37-47%.
- Depósito bancario: sin riesgo, rendimiento fijo. Referencia 2025: 2.0-3.5% TAE a 12 meses.
- Fondo monetario: alternativa al depósito con más liquidez. 2025: ~3.5-4% anual.
- PIAS: seguro de ahorro con ventaja fiscal a largo plazo. Comisiones habitualmente altas. Revisar.

FONDOS DE REFERENCIA BARATOS DISPONIBLES EN ESPAÑA 2025:
- Vanguard Global Stock Index (ISIN: IE00B03HCZ61): TER 0.23%. Referencia clásica.
- iShares Core MSCI World ETF (ISIN: IE00B4L5Y983): TER 0.20%.
- Amundi MSCI World (LU1681043599): TER 0.12%.
- Amundi Prime Global (LU2089238203): TER 0.05%.
- Fundsmith Equity (LU0690375182): TER 1.05% — gestión activa pero histórico sólido vs benchmarks.

DONDE CONTRATAR INDEXADOS EN ESPAÑA (sin banco de toda la vida):
- MyInvestor: amplio catálogo indexados, desde 1€.
- Indexa Capital: carteras indexadas automatizadas, muy eficiente.
- Openbank (Santander): acceso a Vanguard, iShares.
- Finanbest: carteras indexadas, sencillo.
- Interactive Brokers: para ETFs directos, más sofisticado.

PLANES DE PENSIONES — cuando compensa y cuando no:
- Ventaja: deduces hasta 1.500€/año del IRPF. Si estás en tramo 37%, ahorras 555€/año en impuestos.
- Trampa: pagas al rescatar (tributas por toda la ganancia como rendimiento trabajo).
- Solo compensa con claridad si: tramo fiscal actual >37% Y plan indexado con TER <0.5%.
- Plan de pensiones del banco de siempre: TER 1.5-2.5%+ → la ventaja fiscal se come en comisiones.
- Alternativa: fondo de inversión indexado (más líquido, similar ventaja a largo plazo sin bloqueo hasta jubilación).

SEÑALES DE TRAMPA:
🚨 ABUSIVO:
- TER > 2% en cualquier fondo (excepto nichos muy específicos)
- Plan de pensiones del banco con TER > 1.5% → pérdida neta vs fondo indexado incluso con ventaja fiscal
- Comisión de éxito > 10% de las ganancias (además del TER)
- Rentabilidad anunciada "histórica" sin mencionar el benchmark que no bate
🔶 PRECAUCIÓN:
- TER entre 1-2% sin demostrar alfa (rendimiento superior al índice) consistente
- Fondos con AUM (patrimonio) < 50M€ — riesgo de liquidación
- Fondos de autor con historial < 5 años
- Lock-up / iliquidez > 12 meses sin compensación adecuada

COMPARATIVAS — SIEMPRE CON € A 10/20 AÑOS:
- "Con TER 2% pierdes X€ en comisiones en 20 años vs un Vanguard al 0.2%."
- "La ventaja fiscal del plan de pensiones equivale a Y€/año. Las comisiones te cuestan Z€/año. Balance: W€/año."
- "Históricamente, este tipo de fondo solo bate al índice en el X% de los casos a 10 años."

{$comparePreamble}{$offerSection}{$compareSuffix}
{$datosSection}
{$preCalc}

RESPONDE ÚNICAMENTE con un objeto JSON válido:

{
  "tipo_producto": "[exactamente uno de: FONDO_ACTIVO, FONDO_INDEXADO, PLAN_PENSIONES, DEPOSITO, ETF, MONETARIO, OTRO]",
  "nombre_producto": "[nombre del fondo/producto o null]",
  "gestora": "[nombre de la gestora o null]",
  "isin": "[ISIN si aparece en el texto, o null]",
  "resumen": "[1-2 frases: tipo, gestora, TER, benchmark]",
  "veredicto": "[máximo 15 palabras. Directo. Si te están robando con comisiones, dilo.]",
  "puntuacion_transparencia": [0-100],
  "numeros_reales": {
    "comision_anual_pct": [TER/OCF en %, 2 decimales, o null],
    "impacto_comisiones_10a": [€ que te ahorrarías con indexado a 10 años, o null],
    "impacto_comisiones_20a": [€ que te ahorrarías con indexado a 20 años, o null],
    "impacto_comisiones_30a": [€ que te ahorrarías con indexado a 30 años, o null],
    "valor_proyectado_neto_20a": [€ estimados en 20 años con este fondo, o null],
    "valor_indexado_proyectado_20a": [€ estimados en 20 años con indexado ref., o null],
    "diferencia_total_20a": [€ diferencia a 20 años, o null]
  },
  "bate_indice": "[exactamente uno de: SI, NO, DESCONOCIDO]",
  "comision_exito": [true si hay comisión de éxito además del TER, false si no],
  "iliquidez_anos": [años de bloqueo si aplica (ej: plan pensiones = hasta jubilación = 999), o null],
  "trampa": ["2-4 puntos concretos: comisiones ocultas, lock-up, benchmarks trucados"],
  "ventajas": ["0-3 ventajas reales. [] si ninguna."],
  "comparativa": {
    "vs_indexado": "[X€ en comisiones a 20 años vs Amundi/iShares/Vanguard con TER Y%]",
    "alternativa_concreta": "[nombre fondo + ISIN + TER: 'Amundi Prime Global (LU2089238203): TER 0.05%']",
    "recomendacion": "[2-3 frases directas. Qué haría un asesor que no cobra comisión.]"
  },
  "preguntas_clave": ["pregunta 1", "pregunta 2", "pregunta 3"]
}

CRITERIOS:
- 80-100: TER <0.3%, bate benchmark consistentemente o es indexado, sin comisiones ocultas
- 50-79: TER 0.3-1%, historial decente, información completa
- 20-49: TER 1-2%, no bate benchmark, información parcial
- 0-19: TER >2%, no bate benchmark, comisiones ocultas, plan pensiones del banco caro
{$compareSchemaExtra}
PROMPT;

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

// ── Override numeros_reales with PHP-calculated values (priority) ──
if (!empty($calcData)) {
    if (isset($calcData['impacto_comisiones_10a']))        $analysis['numeros_reales']['impacto_comisiones_10a']        = $calcData['impacto_comisiones_10a'];
    if (isset($calcData['impacto_comisiones_20a']))        $analysis['numeros_reales']['impacto_comisiones_20a']        = $calcData['impacto_comisiones_20a'];
    if (isset($calcData['impacto_comisiones_30a']))        $analysis['numeros_reales']['impacto_comisiones_30a']        = $calcData['impacto_comisiones_30a'];
    if (isset($calcData['valor_proyectado_neto_20a']))     $analysis['numeros_reales']['valor_proyectado_neto_20a']     = $calcData['valor_proyectado_neto_20a'];
    if (isset($calcData['valor_indexado_proyectado_20a'])) $analysis['numeros_reales']['valor_indexado_proyectado_20a'] = $calcData['valor_indexado_proyectado_20a'];
    if (isset($calcData['diferencia_total_20a']))          $analysis['numeros_reales']['diferencia_total_20a']          = $calcData['diferencia_total_20a'];
}

// Always override comision_anual_pct with user-provided TER
if ($comisionTer) $analysis['numeros_reales']['comision_anual_pct'] = $comisionTer;

// ── Sanitize ──
$analysis['puntuacion_transparencia'] = max(0, min(100, (int)($analysis['puntuacion_transparencia'] ?? 50)));

$validTipos = ['FONDO_ACTIVO', 'FONDO_INDEXADO', 'PLAN_PENSIONES', 'DEPOSITO', 'ETF', 'MONETARIO', 'OTRO'];
if (!in_array($analysis['tipo_producto'] ?? '', $validTipos)) {
    $analysis['tipo_producto'] = 'OTRO';
}

$validBate = ['SI', 'NO', 'DESCONOCIDO'];
if (!in_array($analysis['bate_indice'] ?? '', $validBate)) {
    $analysis['bate_indice'] = 'DESCONOCIDO';
}

if (!isset($analysis['trampa']) || !is_array($analysis['trampa'])) {
    $analysis['trampa'] = [];
}
if (!isset($analysis['ventajas']) || !is_array($analysis['ventajas'])) {
    $analysis['ventajas'] = [];
}
if (!isset($analysis['preguntas_clave']) || !is_array($analysis['preguntas_clave'])) {
    $analysis['preguntas_clave'] = [];
}
if (!isset($analysis['comision_exito'])) {
    $analysis['comision_exito'] = false;
}

$finalJson = json_encode($analysis, JSON_UNESCAPED_UNICODE);

@file_put_contents($cacheFile, $finalJson, LOCK_EX);
_pruneCache($cacheDir, $cacheTTL);

echo $finalJson;
