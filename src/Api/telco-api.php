<?php
header('Content-Type: application/json; charset=UTF-8');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../ai-helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// ── Shared HTML-to-text helper ──
function cleanToText(string $html): string {
    $text = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = strip_tags($text);
    $text = preg_replace('/[ \t]{2,}/', ' ', $text);
    $text = preg_replace('/(\s*\n\s*){3,}/', "\n\n", $text);
    return trim($text);
}

// ── URL fetching & HTML extraction ──
function fetchTelcoPage(string $url): ?string {
    $cookieJar = sys_get_temp_dir() . '/sf_tk_' . md5($url) . '_' . getmypid() . '.txt';

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
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
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
                if (in_array($type, ['Product', 'Offer', 'Service', 'TelephoneService'])) {
                    $structuredData = json_encode($item, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                    break 2;
                }
            }
        }
    }

    // ── 3. Fine print / legal text extraction (CRITICAL for telco offers) ──
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
      . 'terms|aviso|asterisk|permanencia|periodo-promo|precio-despues|tarifa-legal|'
      . 'modal-legal|ficha-legal|offer-conditions|legal-text|conditions-text|'
      . 'precio-real|detalles-tarifa|contrato-info|fiber-conditions|mobile-terms)[^"\']*["\']/i';
    $legalIdPattern =
        '/id=["\'][^"\']*(?:disclaimer|legal|footnote|condiciones|terms|notas|'
      . 'permanencia|tarifa|aviso|precio-legal|detalles)[^"\']*["\']/i';

    $blockPattern = '/<(?:div|section|article|aside|p|dl)\b([^>]*)>((?:[^<]|<(?!\/?(?:div|section|article|aside|p|dl)\b))*)<\/(?:div|section|article|aside|p|dl)>/si';
    if (preg_match_all($blockPattern, $html, $bm)) {
        foreach ($bm[1] as $i => $attrs) {
            if (preg_match($legalClassPattern, $attrs) || preg_match($legalIdPattern, $attrs)) {
                $t = cleanToText($bm[2][$i]);
                if (strlen($t) > 20) $finePrintParts[] = $t;
            }
        }
    }

    // 3c. Paragraphs and divs that explicitly mention telco-specific terms
    $telcoKeywords = '/tarifa|precio\/mes|permanencia|penalizaci[oó]n|cancela|velocidad|datos|GB|Mbps|fibra|m[oó]vil|l[ií]nea|router|encoder|contrato|periodo\s+promocional|cuota\s+mensual|precio\s+despu[eé]s|promocional|mes\s+gratis|descuento/ui';
    if (preg_match_all('/<(?:p|div|li|td|dd)\b[^>]*>(.*?)<\/(?:p|div|li|td|dd)>/si', $html, $pm)) {
        foreach ($pm[1] as $p) {
            $t = cleanToText($p);
            if (strlen($t) > 10 && preg_match($telcoKeywords, $t)) {
                $finePrintParts[] = $t;
            }
        }
    }

    // 3d. All <li> in lists near telco context
    if (preg_match_all('/<ul[^>]*>(.*?)<\/ul>/si', $html, $ulm)) {
        foreach ($ulm[1] as $ul) {
            $ulText = cleanToText($ul);
            if (preg_match($telcoKeywords, $ulText) && strlen($ulText) > 20) {
                $finePrintParts[] = $ulText;
            }
        }
    }

    // 3e. Tables (price comparison tables, tariff breakdowns)
    if (preg_match_all('/<table[^>]*>(.*?)<\/table>/si', $html, $tm)) {
        foreach ($tm[1] as $table) {
            $t = cleanToText($table);
            if (preg_match($telcoKeywords, $t) && strlen($t) > 20) {
                $finePrintParts[] = $t;
            }
        }
    }

    // 3f. data-* attributes with price/tariff info
    $dataMatches = [];
    if (preg_match_all('/data-(?:price|precio|tarifa|permanencia|gb|speed|velocidad|promo|monthly|cuota|descuento|offer)[^=\s>]*=["\']([^"\']+)["\']/i', $html, $dm)) {
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

// ── Read input ──
$input             = json_decode(file_get_contents('php://input'), true);
$rawOfferText      = trim($input['offer_text']             ?? '');
$offerText         = $rawOfferText;
$precioPromo       = (float)($input['precio_promo']        ?? 0);
$precioDespues     = (float)($input['precio_despues']      ?? 0);
$mesesPermanencia  = (int)($input['meses_permanencia']     ?? 0);
$velocidadMb       = (int)($input['velocidad_mb']          ?? 0);
$gbDatos           = (float)($input['gb_datos']            ?? 0);
$numLineas         = (int)($input['num_lineas']            ?? 1);
$fileBase64A    = $input['file_base64']       ?? null;
$fileMediaTypeA = $input['file_media_type']   ?? null;
$compareMode    = (bool)($input['compare_mode'] ?? false);
$offerTextB     = trim($input['offer_text_b'] ?? '');
$fileBase64B    = $input['file_base64_b']     ?? null;
$fileMediaTypeB = $input['file_media_type_b'] ?? null;

// ── URL detection & fetching ──
$isUrl     = (bool) preg_match('/^https?:\/\//i', $rawOfferText);
$urlSource = null;
$aiShouldFetchUrl = false; // Flag to tell AI to fetch the URL itself

if ($isUrl) {
    // Validate URL to prevent SSRF attacks
    if (!_isUrlSafe($rawOfferText)) {
        http_response_code(400);
        echo json_encode(['error' => 'URL no permitida por razones de seguridad.']);
        exit;
    }
    
    $fetched = fetchTelcoPage($rawOfferText);
    if ($fetched && strlen($fetched) > 250) {
        $urlSource = $rawOfferText;
        $offerText = $fetched;
    } else {
        // Fetch failed - let AI handle the URL directly instead of asking user to copy-paste
        $urlSource = $rawOfferText;
        $aiShouldFetchUrl = true;
    }
}


if (!$offerText && !$fileBase64A && !$aiShouldFetchUrl && !$precioPromo) {
    http_response_code(400);
    echo json_encode(['error' => 'Pega el texto de la oferta, o introduce al menos el precio mensual.']);
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

// ── Cache setup ──
$cacheDir = __DIR__ . '/../../data/cache_telco';
if (!is_dir($cacheDir)) {
    @mkdir($cacheDir, 0755, true);
}
$cacheKey  = md5($rawOfferText . '|' . $precioPromo . '|' . $precioDespues . '|' . $mesesPermanencia . '|' . $velocidadMb . '|' . $gbDatos . '|' . $numLineas . '|' . ($fileBase64A ? md5($fileBase64A) : '') . '|' . ($compareMode ? '1' : '0') . '|' . $offerTextB . '|' . ($fileBase64B ? md5($fileBase64B) : ''));
$cacheFile = $cacheDir . '/' . $cacheKey . '.json';
$cacheTtl  = 5 * 60; // 5 min — dedup only, responses vary by design

if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTtl) {
    $fp = @fopen($cacheFile, 'r');
    if ($fp) {
        flock($fp, LOCK_SH);
        $cached = stream_get_contents($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
        if ($cached) { header('X-Cache: HIT'); echo $cached; exit; }
    }
}

// ── Pre-calculate numbers in PHP for accuracy ──
$preCalc  = '';
$calcData = [];

if ($precioPromo > 0 && $mesesPermanencia > 0) {
    $costeTotalPerm  = round($precioPromo * $mesesPermanencia, 2);
    $penalizacionMax = round($precioDespues > 0
        ? $precioDespues * ($mesesPermanencia / 2)   // worst case ~50% of remaining
        : $precioPromo * ($mesesPermanencia / 2), 2);
    $subidaPrecio    = $precioDespues > 0 ? round($precioDespues - $precioPromo, 2) : null;

    $calcData['coste_total_permanencia'] = $costeTotalPerm;
    $calcData['penalizacion_salida_max'] = $penalizacionMax;
    if ($subidaPrecio !== null) $calcData['subida_precio_despues'] = $subidaPrecio;

    $preCalc  = "CÁLCULOS PRE-VERIFICADOS (usa estos valores exactos en tu respuesta JSON, no los recalcules):\n";
    $preCalc .= "- Coste total durante permanencia: " . number_format($costeTotalPerm, 2, ',', '.') . "€ ({$mesesPermanencia} meses × {$precioPromo}€/mes)\n";
    $preCalc .= "- Penalización máx. estimada salida anticipada: " . number_format($penalizacionMax, 2, ',', '.') . "€\n";
    if ($subidaPrecio !== null)
        $preCalc .= "- Subida de precio después de permanencia: +" . number_format($subidaPrecio, 2, ',', '.') . "€/mes\n";
}

if ($precioPromo > 0 && $gbDatos > 0) {
    $calcData['precio_gb_datos'] = round($precioPromo / $gbDatos, 4);
}

// ── Build prompt sections ──
if ($urlSource) {
    if ($aiShouldFetchUrl) {
        // AI will fetch the URL directly - include clear instructions
        $offerSection = "ENLACE A ANALIZAR: {$urlSource}\n\nPor favor, visita este enlace y extrae toda la información relevante de la oferta de telecomunicaciones (precio mensual, permanencia, velocidad de fibra, GB de datos móviles, líneas adicionales, servicios incluidos, condiciones de renovación, etc.). Analiza si el precio es competitivo comparando con el mercado actual.\n";
    } else {
        $offerSection = "CONTENIDO EXTRAÍDO DE LA PÁGINA WEB DEL OPERADOR (fuente: {$urlSource}):\n\"\"\"\n{$offerText}\n\"\"\"\n";
    }
} elseif ($offerText) {
    $offerSection = "TEXTO DE LA OFERTA / TARIFA:\n\"\"\"\n{$offerText}\n\"\"\"\n";
} else {
    $offerSection = '';
}


$datosSection = '';
if ($precioPromo || $precioDespues || $mesesPermanencia || $velocidadMb || $gbDatos || $numLineas > 1) {
    $datosSection = "DATOS INTRODUCIDOS POR EL USUARIO:\n";
    if ($precioPromo)      $datosSection .= "- Precio mensual promo: {$precioPromo}€/mes\n";
    if ($precioDespues)    $datosSection .= "- Precio después de permanencia: {$precioDespues}€/mes\n";
    if ($mesesPermanencia) $datosSection .= "- Meses de permanencia: {$mesesPermanencia} meses\n";
    if ($velocidadMb)      $datosSection .= "- Velocidad fibra anunciada: {$velocidadMb} Mbps\n";
    if ($gbDatos)          $datosSection .= "- GB datos móvil por línea: {$gbDatos} GB\n";
    if ($numLineas > 1)    $datosSection .= "- Número de líneas móvil: {$numLineas}\n";
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
Eres el analizador de tarifas de telefonía e internet más brutal y honesto de España. Tu misión es desvelar el precio real de las ofertas de móvil y fibra, detectar permanencias abusivas y comparar con las alternativas baratas que los operadores grandes nunca te mencionan.

CONOCIMIENTO DEL MERCADO DE TELEFONÍA ESPAÑOL 2025:

Operadores de referencia baratos (deben siempre mencionarse como comparativa):
- DIGI: fibra simétrica 600Mb + móvil 25GB: 15-18€/mes. SIN PERMANENCIA. Red Vodafone. Referencia más barata del mercado.
- Simyo: fibra 600Mb + móvil ilimitado: desde 20-25€/mes. Sin permanencia. Red Movistar.
- Pepephone: sin permanencia. Mejor soporte al cliente. Red Movistar/Vodafone. Fibra + móvil ~25-35€/mes.
- Finetwork: fibra + móvil desde 20€/mes. Sin permanencia.
- MásMóvil: convergente desde 25€/mes. Permanencia variable.

Operadores grandes CAROS (2-3x más caros que baratos):
- Movistar: convergente 50-90€/mes. Incluye servicios que no necesitas.
- Vodafone: convergente 45-80€/mes. Precios altos post-Digi.
- Orange: convergente 40-75€/mes.
- Estos cobran 2-3x más por la misma infraestructura que usan los OMVs.

Precios de referencia 2025:
- Solo fibra 600Mb: 15-20€/mes (Digi) | 30-45€ operadores grandes. Si te cobran >25€/mes por solo fibra sin móvil, te están timando.
- Solo móvil 30-50GB: 6-12€/mes (OMVs) | 25-40€ operadores grandes.
- Convergente (fibra + 1 móvil): 15-25€/mes barato | 50-85€ grande.
- Convergente (fibra + 2 móviles): 25-40€/mes barato | 65-110€ grande.

Velocidades y realidad:
- FTTH (fibra óptica pura hasta el hogar): 95-100% del anunciado.
- HFC/FTTN (cable o fibra hasta armario): 50-80% del anunciado en horas punta.
- 1 Gbps simétrico: para uso normal en casa es marketing, con 600Mb simétrico sobra.
- "Velocidad máxima" vs "velocidad mínima garantizada": la mínima es la real.

Trampas habituales del sector:
ABUSIVAS:
- Permanencia > 18 meses (25-36 meses es desproporcional)
- Precio promo que sube >40% después del período inicial
- Penalización de salida > 200€ o equivalente a >6 meses de cuota
- Router/encoder en alquiler no incluido en precio anunciado (3-5€/mes extra)
- Precio anunciado sin IVA (añade 21%)
- "Llamadas incluidas" cuando ya nadie llama por teléfono normal
PRECAUCIÓN:
- Permanencia de 12-18 meses (discutible)
- Subida de precio del 15-30% después del período promo
- GB "hasta agotar" con throttling a 1Mbps (velocidad inútil)
- Servicios de streaming incluidos que ya tienes (Disney+, Netflix...)

COMPARATIVAS — SIEMPRE CON CIFRAS EXACTAS:
- "Pagas X€/mes, Digi ofrece lo mismo por Y€/mes. Son Z€/año de diferencia."
- "Tu permanencia de X meses = penalización máx. de Y€ si quieres irte a algo mejor."
- "Con los Z€/año de ahorro cambiando a un OMV pagarías X meses de cuota de más."

{$comparePreamble}{$offerSection}{$compareSuffix}
{$datosSection}
{$preCalc}

RESPONDE ÚNICAMENTE con un objeto JSON válido, sin markdown, sin texto antes ni después:

{
  "tipo_servicio": "[exactamente uno de: SOLO_MOVIL, SOLO_FIBRA, CONVERGENTE]",
  "operadora": "[nombre del operador o null]",
  "resumen_oferta": "[1-2 frases: qué incluye, precio, período promo, permanencia]",
  "veredicto": "[máximo 15 palabras. Sin adornos. Si es una trampa, dilo.]",
  "puntuacion_transparencia": [0-100],
  "numeros_reales": {
    "precio_mensual_promo": [€/mes durante promo, número, o null],
    "precio_mensual_despues": [€/mes después de permanencia, número, o null],
    "meses_permanencia": [meses, número, o null si sin permanencia],
    "coste_total_permanencia": [€ totales durante permanencia, 2 decimales, o null],
    "penalizacion_salida_max": [€ penalización máxima estimada, o null],
    "subida_precio_despues": [€/mes de subida tras promo, o null],
    "precio_gb_datos": [€/GB, 4 decimales, o null],
    "velocidad_bajada_mb": [Mbps anunciados, número, o null]
  },
  "trampa": ["2-4 puntos concretos"],
  "ventajas": ["0-3 ventajas reales. [] si ninguna."],
  "comparativa": {
    "vs_digi": {
      "diferencia_eur": [diferencia anual en € vs DIGI (positivo=esta tarifa es más cara, negativo=más barata). null si no aplica],
      "descripcion": "[Digi ofrece X con Y€/mes sin permanencia. Diferencia: Z€/mes, W€/año]",
      "veredicto": "[exactamente uno de: CARO, NORMAL, BARATO, POSITIVO]"
    },
    "vs_omv": {
      "diferencia_eur": [diferencia anual en € vs Simyo/Pepephone (positivo=esta tarifa es más cara, negativo=más barata). null si no aplica],
      "descripcion": "[Simyo/Pepephone a X€/mes. Sin permanencia. Son Y€/año menos]",
      "veredicto": "[exactamente uno de: CARO, NORMAL, BARATO, POSITIVO]"
    },
    "recomendacion": "[2-3 frases directas. Qué haría alguien inteligente.]"
  },
  "preguntas_clave": ["pregunta 1", "pregunta 2", "pregunta 3"]
}

CRITERIOS:
- 80-100: Sin permanencia o ≤6 meses, precio competitivo, sin cargos ocultos
- 50-79: Permanencia razonable (≤12m), precio algo alto pero sin trampas graves
- 20-49: Permanencia larga, precio post-promo abusivo, información incompleta
- 0-19: Permanencia >24m, penalización enorme, cargos ocultos, precio 3x mercado
{$compareSchemaExtra}
PROMPT;

$content = callAI($prompt, $fileBase64A, $fileMediaTypeA, $compareMode ? $fileBase64B : null, $compareMode ? $fileMediaTypeB : null, $compareMode ? 4000 : 2000);

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

// ── Override with pre-calculated numbers if we have them ──
if (!empty($calcData)) {
    if (isset($calcData['coste_total_permanencia']))
        $analysis['numeros_reales']['coste_total_permanencia'] = $calcData['coste_total_permanencia'];
    if (isset($calcData['penalizacion_salida_max']))
        $analysis['numeros_reales']['penalizacion_salida_max'] = $calcData['penalizacion_salida_max'];
    if (isset($calcData['subida_precio_despues']))
        $analysis['numeros_reales']['subida_precio_despues']   = $calcData['subida_precio_despues'];
    if (isset($calcData['precio_gb_datos']))
        $analysis['numeros_reales']['precio_gb_datos']         = $calcData['precio_gb_datos'];
}

// Always override with user-provided explicit values
if ($precioPromo)      $analysis['numeros_reales']['precio_mensual_promo']    = $precioPromo;
if ($precioDespues)    $analysis['numeros_reales']['precio_mensual_despues']  = $precioDespues;
if ($mesesPermanencia) $analysis['numeros_reales']['meses_permanencia']       = $mesesPermanencia;
if ($velocidadMb)      $analysis['numeros_reales']['velocidad_bajada_mb']     = $velocidadMb;
if ($gbDatos)          $analysis['numeros_reales']['precio_gb_datos']         = $calcData['precio_gb_datos'] ?? $analysis['numeros_reales']['precio_gb_datos'] ?? null;

// ── Sanitize ──
$analysis['puntuacion_transparencia'] = max(0, min(100, (int)($analysis['puntuacion_transparencia'] ?? 50)));

$validTipos = ['SOLO_MOVIL', 'SOLO_FIBRA', 'CONVERGENTE'];
if (!in_array($analysis['tipo_servicio'] ?? '', $validTipos)) {
    $analysis['tipo_servicio'] = 'CONVERGENTE';
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

$finalJson = json_encode($analysis, JSON_UNESCAPED_UNICODE);

@file_put_contents($cacheFile, $finalJson, LOCK_EX);
_pruneCache($cacheDir, $cacheTtl);

echo $finalJson;
