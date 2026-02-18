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

// ── URL fetching & HTML extraction ──
function cleanToText(string $html): string {
    $text = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = strip_tags($text);
    $text = preg_replace('/[ \t]{2,}/', ' ', $text);
    $text = preg_replace('/(\s*\n\s*){3,}/', "\n\n", $text);
    return trim($text);
}

function fetchEnergyPage(string $url): ?string {
    $cookieJar = sys_get_temp_dir() . '/sf_ck_' . md5($url) . '.txt';

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
                if (in_array($type, ['Product', 'Offer', 'Service', 'PriceSpecification'])) {
                    $structuredData = json_encode($item, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                    break 2;
                }
            }
        }
    }

    // ── 3. Fine print / legal text extraction (CRITICAL for energy contracts) ──
    // These sections hide tariff conditions, permanence, etc.
    $finePrintParts = [];

    // 3a. <small> tags
    if (preg_match_all('/<small[^>]*>(.*?)<\/small>/si', $html, $sm)) {
        foreach ($sm[1] as $s) {
            $t = cleanToText($s);
            if (strlen($t) > 15) $finePrintParts[] = $t;
        }
    }

    // 3b. Blocks with legal/disclaimer/conditions class or ID
    $legalClassPattern =
        '/class=["\'][^"\']*(?:disclaimer|legal|footnote|nota-legal|nota[_-]pie|condiciones|'
      . 'terms|aviso|asterisk|tarifa-info|offer-detail|price-footnote|'
      . 'modal-legal|ficha-legal|cuadro-tarifas|financial-disclaimer|'
      . 'offer-conditions|legal-text|conditions-text|energia-info|tarifa-detalle)[^"\']*["\']/i';
    $legalIdPattern =
        '/id=["\'][^"\']*(?:disclaimer|legal|footnote|condiciones|terms|notas|'
      . 'tarifa|aviso|cuadro-tarifas|energia)[^"\']*["\']/i';

    $blockPattern = '/<(?:div|section|article|aside|p|dl)\b([^>]*)>((?:[^<]|<(?!\/?(?:div|section|article|aside|p|dl)\b))*)<\/(?:div|section|article|aside|p|dl)>/si';
    if (preg_match_all($blockPattern, $html, $bm)) {
        foreach ($bm[1] as $i => $attrs) {
            if (preg_match($legalClassPattern, $attrs) || preg_match($legalIdPattern, $attrs)) {
                $t = cleanToText($bm[2][$i]);
                if (strlen($t) > 20) $finePrintParts[] = $t;
            }
        }
    }

    // 3c. Paragraphs and divs that explicitly mention energy tariff terms
    $energyKeywords = '/tarifa|precio\/kWh|término|potencia|consumo|factura|compensaci[oó]n|permanencia|penalizaci[oó]n|bono|descuento|PVPC|período|vigencia|IVA|impuesto|alquiler.{0,10}contador/ui';
    if (preg_match_all('/<(?:p|div|li|td|dd)\b[^>]*>(.*?)<\/(?:p|div|li|td|dd)>/si', $html, $pm)) {
        foreach ($pm[1] as $p) {
            $t = cleanToText($p);
            if (strlen($t) > 10 && preg_match($energyKeywords, $t)) {
                $finePrintParts[] = $t;
            }
        }
    }

    // 3d. All <li> in lists near energy context
    if (preg_match_all('/<ul[^>]*>(.*?)<\/ul>/si', $html, $ulm)) {
        foreach ($ulm[1] as $ul) {
            $ulText = cleanToText($ul);
            if (preg_match($energyKeywords, $ulText) && strlen($ulText) > 20) {
                $finePrintParts[] = $ulText;
            }
        }
    }

    // 3e. Tables (tariff comparison tables, price breakdowns)
    if (preg_match_all('/<table[^>]*>(.*?)<\/table>/si', $html, $tm)) {
        foreach ($tm[1] as $table) {
            $t = cleanToText($table);
            if (preg_match($energyKeywords, $t) && strlen($t) > 20) {
                $finePrintParts[] = $t;
            }
        }
    }

    // 3f. data-* attributes that contain energy price info
    $dataMatches = [];
    if (preg_match_all('/data-(?:price|kwh|potencia|tarifa|energia|consumo|permanencia|descuento|tae|monthly|offer|amount|total)[^=\s>]*=["\']([^"\']+)["\']/i', $html, $dm)) {
        foreach ($dm[0] as $i => $fullAttr) {
            $key = preg_replace('/data-([^=]+)=.*/i', '$1', $fullAttr);
            $dataMatches[] = $key . ': ' . $dm[1][$i];
        }
    }
    if ($dataMatches) {
        $finePrintParts[] = "DATOS TARIFA (atributos data-*): " . implode(', ', array_unique($dataMatches));
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
    $result .= "TEXTO PRINCIPAL DE LA OFERTA:\n" . mb_substr($text, 0, 4500);

    return $result;
}

$input         = json_decode(file_get_contents('php://input'), true);
$rawOfferText  = trim($input['offer_text']          ?? '');
$offerText     = $rawOfferText;
$potenciaKw    = (float)($input['potencia_kw']      ?? 0);
$consumoKwh    = (float)($input['consumo_kwh_mes']  ?? 0);
$precioKwh     = (float)($input['precio_kwh']       ?? 0);
$precioPotDia  = (float)($input['precio_potencia_dia'] ?? 0);
$tipoTarifa    = trim($input['tipo_tarifa']          ?? '');
$permanencia   = (int)($input['permanencia_meses']  ?? 0);
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
    $fetched = fetchEnergyPage($rawOfferText);
    if ($fetched && strlen($fetched) > 250) {
        $urlSource = $rawOfferText;
        $offerText = $fetched;
    } else {
        http_response_code(422);
        echo json_encode([
            'error'           => 'No se pudo leer el contenido del enlace automáticamente. Algunas webs de energía cargan con JavaScript. Copia el texto de la oferta o la factura y pégalo directamente aquí.',
            'url_fetch_failed' => true,
        ]);
        exit;
    }
}

if (!$offerText && !$fileBase64A && !$consumoKwh && !$precioKwh) {
    http_response_code(400);
    echo json_encode(['error' => 'Pega tu factura o el texto de la oferta, o introduce al menos el consumo y el precio por kWh.']);
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
$cacheDir = __DIR__ . '/data/cache_luz';
if (!is_dir($cacheDir)) mkdir($cacheDir, 0755, true);
$cacheKey  = md5($rawOfferText . '|' . $potenciaKw . '|' . $consumoKwh . '|' . $precioKwh . '|' . $precioPotDia . '|' . $tipoTarifa . '|' . $permanencia . '|' . ($fileBase64A ? md5($fileBase64A) : '') . '|' . ($compareMode ? '1' : '0') . '|' . $offerTextB . '|' . ($fileBase64B ? md5($fileBase64B) : ''));
$cacheFile = $cacheDir . '/' . $cacheKey . '.json';
$cacheTtl  = 7 * 24 * 3600; // 7 days

if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTtl) {
    echo file_get_contents($cacheFile);
    exit;
}

// ── Pre-calculate numbers in PHP for accuracy ──
$preCalc  = '';
$calcData = [];

if ($consumoKwh > 0 && $precioKwh > 0) {
    $costeEnergiaMes  = round($consumoKwh * $precioKwh, 2);
    $costePotenciaMes = $potenciaKw > 0 && $precioPotDia > 0
        ? round($potenciaKw * $precioPotDia * 30, 2) : 0;
    $costeTotalMes  = round($costeEnergiaMes + $costePotenciaMes, 2);
    $costeAnual     = round($costeTotalMes * 12, 2);
    // Reference PVPC: 0.13 €/kWh energia + 0.1082 €/kW/día potencia (3.3kW default)
    $pvpcRefKwh     = 0.13;
    $pvpcRefPotDia  = 0.1082;
    $pvpcEnergiaMes = round($consumoKwh * $pvpcRefKwh, 2);
    $pvpcPotMes     = round(($potenciaKw > 0 ? $potenciaKw : 3.3) * $pvpcRefPotDia * 30, 2);
    $pvpcTotalMes   = round($pvpcEnergiaMes + $pvpcPotMes, 2);
    $sobrecosteAnual = round(($costeTotalMes - $pvpcTotalMes) * 12, 2);
    $calcData['coste_energia_mes']        = $costeEnergiaMes;
    $calcData['coste_potencia_mes']       = $costePotenciaMes;
    $calcData['coste_total_mes']          = $costeTotalMes;
    $calcData['coste_anual']              = $costeAnual;
    $calcData['sobrecoste_vs_pvpc_anual'] = $sobrecosteAnual;

    $preCalc = "CÁLCULOS PRE-VERIFICADOS (usa estos valores exactos en tu respuesta JSON, no los recalcules):
- Coste energía mes: " . number_format($costeEnergiaMes, 2, ',', '.') . "€ ({$consumoKwh} kWh × {$precioKwh} €/kWh)
- Coste potencia mes: " . number_format($costePotenciaMes, 2, ',', '.') . "€" . ($potenciaKw > 0 && $precioPotDia > 0 ? " ({$potenciaKw} kW × {$precioPotDia} €/kW/día × 30 días)" : '') . "
- TOTAL MES: " . number_format($costeTotalMes, 2, ',', '.') . "€
- TOTAL AÑO: " . number_format($costeAnual, 2, ',', '.') . "€
- Referencia PVPC estimada: " . number_format($pvpcTotalMes, 2, ',', '.') . "€/mes
- Sobrecoste vs PVPC referencia: " . number_format($sobrecosteAnual, 2, ',', '.') . "€/año";
}

if ($urlSource) {
    $offerSection = "CONTENIDO EXTRAÍDO DE LA OFERTA (fuente: {$urlSource}):\n\"\"\"\n{$offerText}\n\"\"\"\n";
} elseif ($offerText) {
    $offerSection = "TEXTO DE LA FACTURA / OFERTA:\n\"\"\"\n{$offerText}\n\"\"\"\n";
} else {
    $offerSection = '';
}

$datosSection = '';
if ($potenciaKw || $consumoKwh || $precioKwh || $precioPotDia || $tipoTarifa || $permanencia) {
    $datosSection = "DATOS INTRODUCIDOS POR EL USUARIO:\n";
    if ($potenciaKw)   $datosSection .= "- Potencia contratada: {$potenciaKw} kW\n";
    if ($consumoKwh)   $datosSection .= "- Consumo mensual estimado: {$consumoKwh} kWh/mes\n";
    if ($precioKwh)    $datosSection .= "- Precio energía (término de energía): {$precioKwh} €/kWh\n";
    if ($precioPotDia) $datosSection .= "- Precio potencia (término de potencia): {$precioPotDia} €/kW/día\n";
    if ($tipoTarifa)   $datosSection .= "- Tipo de tarifa: {$tipoTarifa}\n";
    if ($permanencia)  $datosSection .= "- Permanencia declarada: {$permanencia} meses\n";
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
Eres el analizador de contratos de luz y gas más brutal y honesto de España. Tu misión es proteger al usuario de las comercializadoras que abusan con tarifas opacas, permanencias ocultas y potencia sobredimensionada. Datos con cifras exactas, sin suavizar.

CONOCIMIENTO DEL MERCADO ELÉCTRICO ESPAÑOL 2025:

Tipos de tarifa:
- PVPC (Precio Voluntario Pequeño Consumidor): precio variable por horas. Referencia: ~0.11-0.15 €/kWh de media anual 2025. Regulada por OMIE. Más barata si consumes en horas valle (23:00-8:00).
- Tarifa Fija: precio garantizado 12 meses, sin sorpresas. Cara en momentos de mercado bajo, ventaja en picos.
- Tarifa Indexada (PVPC simulada por privados): sigue mercado pero con margen comercializadora. Puede salir peor que PVPC puro.

Componentes de la factura de luz:
- Término de energía (TE): lo que pagas por kWh consumidos. Referencia PVPC 2025: 0.11-0.15 €/kWh.
- Término de potencia (TP): tarifa fija por kW contratado/día. Referencia: ~0.1082 €/kW/día. Se paga aunque no consumas.
- Impuesto eléctrico: 5.11% sobre TE + TP.
- IVA: 10% (electricidad doméstica).
- Alquiler del contador: 9-20€/año (telegestión: ~0.81€/mes).
- Consumo medio hogar español: 250-350 kWh/mes.

Potencia contratada — trampa habitual:
- Piso pequeño (<70m²): 2.3 kW es suficiente en la mayoría de casos.
- Piso medio (70-100m²): 3.3 kW es adecuado.
- Piso grande / chalet sin climatización eléctrica: 4.4-5.5 kW.
- Chalet con aire acondicionado o calefacción eléctrica: 5.5-6.9 kW.
- Coste extra de cada kW de más: ~39-40€/año (impuesto incluido).
- MUCHOS hogares tienen 3.3 o 4.4 kW cuando les sobra 1 kW → 39-40€/año tirados.

Permanencia y penalizaciones:
- Sin permanencia: lo correcto para contratos de luz.
- Permanencias de 12-24 meses son ABUSIVAS y solo protegen a la comercializadora.
- Penalización típica: precio de cuotas pendientes o monto fijo (50-200€).

Comercializadoras de referencia baratas 2025:
- Octopus Energy: PVPC sin margen, app excelente, sin permanencia.
- Holaluz: tarifa fija competitiva, 100% renovable certificado.
- Som Energia: cooperativa, tarifas honestas, sin permanencia.
- Plenitude (ENI): tarifa indexada competitiva.
- Luz Solidaria: tarifa social accesible.
- Iberdrola, Endesa, Naturgy: caras, altos márgenes comerciales.
- EDP, Repsol Luz: precio medio-alto.

Bono Social Eléctrico:
- 25% descuento para consumidores vulnerables (ingresos <2x IPREM).
- 40% si son vulnerables severos.
- Solo en comercializadoras de referencia.

SEÑALES DE TRAMPA EN CONTRATOS DE LUZ:
🚨 ABUSIVO:
- Permanencia > 12 meses
- Precio kWh fijo > 0.20 €/kWh (muy por encima del mercado)
- Cargos de "mantenimiento" o "servicio premium" no solicitados
- Precio de potencia > 0.15 €/kW/día sin justificación
🔶 PRECAUCIÓN:
- Potencia contratada >1 kW por encima de lo necesario
- Precio introductorio que sube a los 3-6 meses
- Tarifa "indexada" con márgenes > 0.02 €/kWh sobre PVPC
- Sin desglose claro de TE + TP en la oferta

COMPARATIVAS — SIEMPRE CON CIFRAS:
- No digas "es cara". Di: "Pagas X€/mes más que con PVPC, son Y€/año desperdiciados".
- No digas "hay alternativas". Di: "Holaluz te cobraría ~X€/mes, Octopus ~Y€/mes".
- Potencia: "Bajando de X kW a Y kW ahorrarías ~Z€/año".

{$comparePreamble}{$offerSection}{$compareSuffix}
{$datosSection}
{$preCalc}

RESPONDE ÚNICAMENTE con un objeto JSON válido, sin markdown, sin texto antes ni después:
{
  "tipo_contrato": "[exactamente uno de: PVPC, TARIFA_FIJA, TARIFA_INDEXADA, DESCONOCIDO]",
  "comercializadora": "[nombre comercializadora o null]",
  "resumen_oferta": "[descripción directa 1-2 frases: tipo tarifa, precio, condiciones]",
  "veredicto": "[máximo 15 palabras. Directísimo. Si es una estafa, dilo.]",
  "puntuacion_transparencia": [0-100: 100=datos completos sin trampas, 0=diseñado para engañar],
  "numeros_reales": {
    "potencia_kw": [kW contratados, número, o null],
    "precio_kwh": [€/kWh, 4 decimales, o null],
    "consumo_mensual_kwh": [kWh/mes, o null],
    "coste_mensual_estimado": [€/mes total (energía + potencia + impuestos), 2 decimales, o null],
    "coste_anual_estimado": [€/año, 2 decimales, o null],
    "sobrecoste_vs_pvpc_anual": [€/año de más vs PVPC referencia, o null si no calculable]
  },
  "permanencia_meses": [meses de permanencia, número, o null si sin permanencia],
  "penalizacion_salida": "[descripción de la penalización por salida anticipada, o null]",
  "potencia_evaluacion": {
    "estado": "[exactamente uno de: EXCESIVA, ADECUADA, INSUFICIENTE, DESCONOCIDA]",
    "ahorro_potencial_anual": [€/año ahorrables reduciendo potencia, número, o null],
    "recomendacion_kw": [kW recomendados, número, o null],
    "explicacion": "[1-2 frases concretas sobre la potencia]"
  },
  "trampa": [
    "[cláusula abusiva, coste oculto o punto crítico. Siempre 2-4 puntos.]"
  ],
  "ventajas": [
    "[ventaja real y objetiva. [] si no hay ninguna.]"
  ],
  "comparativa": {
    "vs_pvpc": "[SIEMPRE con €/año exactos: 'Pagas X€/año más/menos que con PVPC']",
    "vs_competidores": "[nombres concretos y precios: 'Octopus: ~X€/mes. Holaluz: ~Y€/mes.']",
    "recomendacion": "[2-3 frases directas. Qué haría alguien que no quiere que te estafen.]"
  },
  "preguntas_clave": [
    "[pregunta concreta y útil que el usuario DEBE hacer]",
    "[pregunta 2]",
    "[pregunta 3]"
  ]
}

CRITERIOS TRANSPARENCIA:
- 80-100: Precio claro, sin permanencia, todos los cargos desglosados, competitivo
- 50-79: Precio OK pero algo opaco o mejorable
- 20-49: Permanencia oculta, precio alto sin justificación, información incompleta
- 0-19: Cargos ocultos, trampa de potencia, permanencia abusiva, precio desorbitado
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

// Override with pre-calculated numbers if we have them
if (!empty($calcData)) {
    if (isset($calcData['coste_energia_mes']))        $analysis['numeros_reales']['coste_energia_mes']        = $calcData['coste_energia_mes'];
    if (isset($calcData['coste_potencia_mes']))       $analysis['numeros_reales']['coste_potencia_mes']       = $calcData['coste_potencia_mes'];
    if (isset($calcData['coste_total_mes']))          $analysis['numeros_reales']['coste_mensual_estimado']   = $calcData['coste_total_mes'];
    if (isset($calcData['coste_anual']))              $analysis['numeros_reales']['coste_anual_estimado']     = $calcData['coste_anual'];
    if (isset($calcData['sobrecoste_vs_pvpc_anual'])) $analysis['numeros_reales']['sobrecoste_vs_pvpc_anual'] = $calcData['sobrecoste_vs_pvpc_anual'];
}

// Always override with user-provided explicit values
if ($potenciaKw)   $analysis['numeros_reales']['potencia_kw']  = $potenciaKw;
if ($precioKwh)    $analysis['numeros_reales']['precio_kwh']   = $precioKwh;
if ($consumoKwh)   $analysis['numeros_reales']['consumo_mensual_kwh'] = $consumoKwh;
if ($permanencia)  $analysis['permanencia_meses']              = $permanencia;

// Sanitize
$analysis['puntuacion_transparencia'] = max(0, min(100, (int)($analysis['puntuacion_transparencia'] ?? 50)));

$validTipos = ['PVPC', 'TARIFA_FIJA', 'TARIFA_INDEXADA', 'DESCONOCIDO'];
if (!in_array($analysis['tipo_contrato'] ?? '', $validTipos)) {
    $analysis['tipo_contrato'] = 'DESCONOCIDO';
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
if (!isset($analysis['potencia_evaluacion']) || !is_array($analysis['potencia_evaluacion'])) {
    $analysis['potencia_evaluacion'] = ['estado' => 'DESCONOCIDA'];
}
$validEstados = ['EXCESIVA', 'ADECUADA', 'INSUFICIENTE', 'DESCONOCIDA'];
if (!in_array($analysis['potencia_evaluacion']['estado'] ?? '', $validEstados)) {
    $analysis['potencia_evaluacion']['estado'] = 'DESCONOCIDA';
}

$finalJson = json_encode($analysis, JSON_UNESCAPED_UNICODE);

@file_put_contents($cacheFile, $finalJson, LOCK_EX);
_pruneCache($cacheDir, $cacheTtl);

echo $finalJson;
