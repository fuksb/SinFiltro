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

// ── HTML → plain text helper ──
function cleanToText(string $html): string {
    $text = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = strip_tags($text);
    $text = preg_replace('/[ \t]{2,}/', ' ', $text);
    $text = preg_replace('/(\s*\n\s*){3,}/', "\n\n", $text);
    return trim($text);
}

// ── URL fetching & fine-print extraction for insurance pages ──
function fetchInsurancePage(string $url): ?string {
    $cookieJar = sys_get_temp_dir() . '/sf_ck_' . md5($url) . '_' . getmypid() . '.txt';

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
                if (in_array($type, ['InsuranceProduct', 'Product', 'Offer', 'FinancialProduct'])) {
                    $structuredData = json_encode($item, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                    break 2;
                }
            }
        }
    }

    // ── 3. Fine print / legal text extraction (CRITICAL for insurance pages) ──
    // These sections hide exclusions, carencias, franquicias, etc.
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
      . 'terms|aviso|asterisk|exclusion|cobertura|poliza|carencia|franquicia|'
      . 'modal-legal|ficha-legal|condiciones-generales|nota-pie|pie-oferta|'
      . 'offer-conditions|legal-text|conditions-text|insurance-detail)[^"\']*["\']/i';
    $legalIdPattern =
        '/id=["\'][^"\']*(?:disclaimer|legal|footnote|condiciones|terms|notas|'
      . 'exclusiones|coberturas|carencias|franquicia|poliza|aviso)[^"\']*["\']/i';

    $blockPattern = '/<(?:div|section|article|aside|p|dl)\b([^>]*)>((?:[^<]|<(?!\/?(?:div|section|article|aside|p|dl)\b))*)<\/(?:div|section|article|aside|p|dl)>/si';
    if (preg_match_all($blockPattern, $html, $bm)) {
        foreach ($bm[1] as $i => $attrs) {
            if (preg_match($legalClassPattern, $attrs) || preg_match($legalIdPattern, $attrs)) {
                $t = cleanToText($bm[2][$i]);
                if (strlen($t) > 20) $finePrintParts[] = $t;
            }
        }
    }

    // 3c. Paragraphs and divs that explicitly mention insurance keywords
    $insuranceKeywords = '/cobertura|exclusi[oó]n|franquicia|prima|siniestro|carencia|p[oó]liza|capital\s+asegurado|responsabilidad\s+civil|indemnizaci[oó]n|prest[aá]ci[oó]n|beneficiario|rescisi[oó]n|renovaci[oó]n|anuali|mensual(?:idad)?|condiciones\s+generales|tomador|asegurado|riesgo\s+excluido|coste\s+total/ui';
    if (preg_match_all('/<(?:p|div|li|td|dd)\b[^>]*>(.*?)<\/(?:p|div|li|td|dd)>/si', $html, $pm)) {
        foreach ($pm[1] as $p) {
            $t = cleanToText($p);
            if (strlen($t) > 10 && preg_match($insuranceKeywords, $t)) {
                $finePrintParts[] = $t;
            }
        }
    }

    // 3d. All <li> in lists near insurance context
    if (preg_match_all('/<ul[^>]*>(.*?)<\/ul>/si', $html, $ulm)) {
        foreach ($ulm[1] as $ul) {
            $ulText = cleanToText($ul);
            if (preg_match($insuranceKeywords, $ulText) && strlen($ulText) > 20) {
                $finePrintParts[] = $ulText;
            }
        }
    }

    // 3e. Tables (coverage comparison tables, price breakdowns)
    if (preg_match_all('/<table[^>]*>(.*?)<\/table>/si', $html, $tm)) {
        foreach ($tm[1] as $table) {
            $t = cleanToText($table);
            if (preg_match($insuranceKeywords, $t) && strlen($t) > 20) {
                $finePrintParts[] = $t;
            }
        }
    }

    // 3f. data-* attributes that contain insurance/price info
    $dataMatches = [];
    if (preg_match_all('/data-(?:price|prima|premium|capital|franquicia|cobertura|carencia|monthly|annual|offer|amount|total)[^=\s>]*=["\']([^"\']+)["\']/i', $html, $dm)) {
        foreach ($dm[0] as $i => $fullAttr) {
            $key = preg_replace('/data-([^=]+)=.*/i', '$1', $fullAttr);
            $dataMatches[] = $key . ': ' . $dm[1][$i];
        }
    }
    if ($dataMatches) {
        $finePrintParts[] = "DATOS PÓLIZA (atributos data-*): " . implode(', ', array_unique($dataMatches));
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
    $result .= "TEXTO PRINCIPAL DE LA PÓLIZA/OFERTA:\n" . mb_substr($text, 0, 4500);

    return $result;
}

// ── Input parsing ──
$input        = json_decode(file_get_contents('php://input'), true);
$rawOfferText = trim($input['offer_text']     ?? '');
$offerText    = $rawOfferText;
$tipoSeguro   = trim($input['tipo_seguro']    ?? '');
$primaAnual   = (float)($input['prima_anual'] ?? 0);
$capitalAseg  = (float)($input['capital_aseg'] ?? 0);
$vinculadoHip = (bool)($input['vinculado_hip'] ?? false);
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
    
    $fetched = fetchInsurancePage($rawOfferText);
    if ($fetched && strlen($fetched) > 250) {
        $urlSource = $rawOfferText;
        $offerText = $fetched;
    } else {
        // Fetch failed - let AI handle the URL directly instead of asking user to copy-paste
        $urlSource = $rawOfferText;
        $aiShouldFetchUrl = true;
    }
}


if (!$offerText && !$fileBase64A && !$aiShouldFetchUrl) {
    http_response_code(400);
    echo json_encode(['error' => 'Pega el texto de tu póliza o las condiciones del seguro.']);
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

// ── Cache ──
$cacheDir = __DIR__ . '/../../data/cache_seguros';
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}
$cacheKey  = md5($rawOfferText . '|' . $tipoSeguro . '|' . $primaAnual . '|' . $capitalAseg . '|' . ($vinculadoHip ? '1' : '0') . '|' . ($fileBase64A ? md5($fileBase64A) : '') . '|' . ($compareMode ? '1' : '0') . '|' . $offerTextB . '|' . ($fileBase64B ? md5($fileBase64B) : ''));
$cacheFile = $cacheDir . '/' . $cacheKey . '.json';
$cacheTtl  = 5 * 60; // 5 min — dedup only, responses vary by design

if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTtl) {
    $fp = @fopen($cacheFile, 'r');
    if ($fp) {
        flock($fp, LOCK_SH);
        $cached = stream_get_contents($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
        if ($cached) {
            echo $cached;
            exit;
        }
    }
}

// ── PHP pre-calculations ──
$preCalc  = '';
$calcData = [];

if ($primaAnual > 0) {
    $primaMensual = round($primaAnual / 12, 2);
    $calcData['prima_mensual'] = $primaMensual;
}

if ($primaAnual > 0 && $capitalAseg > 0) {
    $tasaCapital = round(($primaAnual / $capitalAseg) * 100, 4);
    $calcData['tasa_sobre_capital_pct'] = $tasaCapital;
}

// If vinculado_hip and prima anual provided, estimate market price
if ($vinculadoHip && $primaAnual > 0) {
    // Bank linked insurance is typically 2-4x market price
    // Market reference: vida ~0.15% capital/year, hogar ~250-350€/year
    $mercadoRef = ($tipoSeguro === 'VIDA' && $capitalAseg > 0) ? ($capitalAseg * 0.0015) : 300;
    $sobrecosteHip = round($primaAnual - $mercadoRef, 0);
    if ($sobrecosteHip > 0) {
        $calcData['sobrecoste_vinculacion_anual'] = $sobrecosteHip;
    }
}

if (!empty($calcData)) {
    $preCalc = "DATOS PRE-CALCULADOS (usa estos valores exactos en tu respuesta JSON, no los recalcules):\n";
    if (isset($calcData['prima_mensual']))
        $preCalc .= "- Prima mensual equivalente: " . number_format($calcData['prima_mensual'], 2, ',', '.') . "€/mes\n";
    if (isset($calcData['tasa_sobre_capital_pct']))
        $preCalc .= "- Tasa prima/capital: " . number_format($calcData['tasa_sobre_capital_pct'], 4, ',', '.') . "%\n";
    if (isset($calcData['sobrecoste_vinculacion_anual']))
        $preCalc .= "- Sobrecoste estimado vs mercado (vinculado hipoteca): " . number_format($calcData['sobrecoste_vinculacion_anual'], 0, ',', '.') . "€/año\n";
}

// ── Build offer/datos sections ──
if ($urlSource) {
    if ($aiShouldFetchUrl) {
        // AI will fetch the URL directly - include clear instructions
        $offerSection = "ENLACE A ANALIZAR: {$urlSource}\n\nPor favor, visita este enlace y extrae toda la información relevante del seguro (cobertura, prima, franquicias, exclusiones, períodos de carencia, condiciones de renovación, servicios adicionales, etc.). Analiza si las condiciones son competitivas y adecuadas para el perfil del cliente.\n";
    } else {
        $offerSection = "CONTENIDO EXTRAÍDO DE LA WEB (fuente: {$urlSource}):\n\"\"\"\n{$offerText}\n\"\"\"\n";
    }
} elseif ($offerText) {
    $offerSection = "TEXTO DE LA PÓLIZA / OFERTA:\n\"\"\"\n{$offerText}\n\"\"\"\n";
} else {
    $offerSection = '';
}


$datosSection = '';
if ($tipoSeguro || $primaAnual || $capitalAseg || $vinculadoHip) {
    $datosSection = "DATOS INTRODUCIDOS POR EL USUARIO:\n";
    if ($tipoSeguro)   $datosSection .= "- Tipo de seguro: {$tipoSeguro}\n";
    if ($primaAnual)   $datosSection .= "- Prima anual: " . number_format($primaAnual, 2, ',', '.') . "€/año\n";
    if ($capitalAseg)  $datosSection .= "- Capital asegurado: " . number_format($capitalAseg, 0, ',', '.') . "€\n";
    if ($vinculadoHip) $datosSection .= "- Vinculado a hipoteca: SÍ (el banco lo exige)\n";
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

// ── Claude prompt ──
$prompt = <<<PROMPT
Eres el analizador de seguros más brutal y honesto de España. Tu misión es desvelar las exclusiones que esconden los seguros, detectar coberturas duplicadas que ya tienes gratis, y desmontar el negocio de los seguros vinculados a hipotecas y coches que los bancos y concesionarios te meten.

CONOCIMIENTO DEL MERCADO DE SEGUROS ESPAÑOL 2025:

SEGURO DE HOGAR:
- Prima media mercado 2025: 200-400€/año (piso estándar).
- Capitales habituales: continente 80.000-200.000€ (la estructura del edificio), contenido 20.000-40.000€ (muebles y enseres).
- Franquicia habitual: 150-300€ (lo que pagas tú antes que el seguro cubra).
- Exclusiones más frecuentes QUE DEBES MENCIONAR: daños por agua si la instalación tiene >X años (varía), robos sin efracción (sin forzar entrada), inundaciones (excluidas en zonas de riesgo), terremotos (generalmente excluidos), daños estéticos sin pérdida funcional.
- Coberturas DUPLICADAS: RC familiar del hogar (ya cubre accidentes a terceros que causa el asegurado y su familia — puede duplicar la RC del coche), seguro de accidentes de la cuenta bancaria.
- Trampa común: capitales asegurados bajísimos que no cubren ni la mitad del valor real.
- Renovación tácita: se renueva sola si no avisas 1 mes antes del vencimiento.

SEGURO DE VIDA:
- Prima referencia 2025 (persona 40 años, no fumador): 0.3-0.8% del capital/año.
- Para fumadores: 1.5-3x más caro.
- Banco vinculado a hipoteca: el banco cobra 2-5x el precio de mercado.
- El cliente tiene DERECHO a contratar en compañía externa (Ley 5/2019). El banco NO puede negarle la hipoteca ni empeorar condiciones si el seguro de vida externo tiene cobertura equivalente.
- Comparativas mercado (perfil 40a, no fumador, 200.000€ capital): Generali ~200€/año, Zurich ~220€/año, AXA ~250€/año. vs banco: 800-1.500€/año. Diferencia: hasta 1.300€/año de ahorro.
- Exclusiones habituales: suicidio (1er año), enfermedades preexistentes no declaradas, actividades de alto riesgo, muerte bajo efectos de alcohol/drogas.
- Gran trampa: el seguro de vida del banco baja el capital asegurado cada año al mismo ritmo que baja la hipoteca pero la prima NO baja proporcionalmente.

SEGURO DE COCHE:
- RC obligatorio: 300-600€/año para perfil estándar.
- Todo riesgo sin franquicia: 700-1.800€/año según vehículo, conductor y zona.
- Todo riesgo con franquicia (300-600€): 30-40% más barato.
- Coberturas típicas a revisar: lunas (¿franquicia?), asistencia en viaje (¿incluye España?), coche de sustitución (¿días? ¿mismo segmento?).
- Trampa franquicia: el seguro "todo riesgo" del concesionario puede tener franquicia de 1.000-2.000€ → no es realmente todo riesgo.

SEGURO DE SALUD PRIVADO:
- Prima media adulto 35-45 años: 50-100€/mes. Niños: 20-40€/mes.
- Lo más importante: CUADRO MÉDICO (lista de médicos y hospitales), no el precio.
- Exclusiones habituales: enfermedades preexistentes (primeros 6-12 meses carencia), maternidad (carencia 8-10 meses), prótesis dentales, cirugía estética.
- Trampa copago: "sin copago" vs "con copago 3-6€/visita" — el copago baja mucho la prima pero a largo plazo sale igual.
- Comparativa referencia 2025: Adeslas/Asisa/DKV: 60-120€/mes. Sanitas: 80-150€/mes. IMQ/Asistencia Sanitaria: más barato en algunas CCAA.

SEGUROS VINCULADOS A HIPOTECA:
- El banco EXIGE vida + hogar para dar la hipoteca a mejor tipo.
- DERECHO del cliente: contratar en cualquier compañía mientras la cobertura sea equivalente (Ley 5/2019 Contratos de Crédito Inmobiliario, art. 17).
- Ahorro típico: 500-1.500€/año cambiando los seguros de banco a mercado libre.
- Ejemplo concreto: banco cobra 1.200€/año por vida + hogar. En mercado: vida 250€ + hogar 300€ = 550€. Ahorro: 650€/año = 19.500€ a lo largo de 30 años de hipoteca.

SEÑALES DE TRAMPA:
🚨 ABUSIVO:
- Prima >2x el precio de mercado para esa cobertura y perfil
- Franquicia >600€ en un "todo riesgo"
- Carencia >12 meses para coberturas básicas
- Capital asegurado muy por debajo del valor real del bien
- Seguro vinculado donde no informan del derecho a cambiar
🔶 PRECAUCIÓN:
- Exclusiones no mencionadas en el resumen principal
- Renovación tácita sin aviso previo por email
- Coberturas duplicadas que ya tienes incluidas en otro producto
- Prima que sube >5% anual sin mejora de cobertura

COMPARATIVAS — SIEMPRE CON €:
- No "es caro". Di: "Pagas X€/año, el mercado ofrece la misma cobertura por Y€/año. Son Z€ de ahorro".
- Nombra compañías concretas: "Generali, Mapfre, Mutua Madrileña te cobrarían ~X€/año".

{$comparePreamble}{$offerSection}{$compareSuffix}
{$datosSection}
{$preCalc}

RESPONDE ÚNICAMENTE con un objeto JSON válido, sin markdown, sin texto antes ni después:

{
  "tipo_seguro": "[exactamente uno de: HOGAR, VIDA, COCHE, SALUD, MULTIRRIESGO, OTRO]",
  "aseguradora": "[nombre o null]",
  "resumen_poliza": "[1-2 frases: tipo, capital, prima, coberturas principales]",
  "veredicto": "[máximo 15 palabras. Sin rodeos.]",
  "puntuacion_transparencia": [0-100],
  "numeros_reales": {
    "prima_anual": [€/año, número, o null],
    "prima_mensual": [€/mes, 2 decimales, o null],
    "capital_asegurado": [€, número, o null],
    "franquicia_euros": [€ de franquicia, o null],
    "tasa_sobre_capital_pct": [% prima/capital, 4 decimales, o null]
  },
  "vinculado_hipoteca": [true si es seguro que el banco obliga, false si no],
  "sobrecoste_vinculacion_anual": [€/año de sobreprecio vs mercado si vinculado, o null],
  "exclusiones_criticas": [
    "[exclusión importante que el usuario debería saber. 2-4 items]"
  ],
  "coberturas_duplicadas": [
    "[cobertura que probablemente ya tiene incluida en otro producto. [] si ninguna]"
  ],
  "trampa": ["2-4 puntos críticos: cláusulas abusivas, costes ocultos, carencias"],
  "ventajas": ["0-3 ventajas reales. [] si ninguna."],
  "comparativa": {
    "vs_mercado": {
      "diferencia_eur": [diferencia anual en € vs prima de mercado equivalente (positivo=esta póliza es más cara, negativo=más barata). null si no aplica],
      "descripcion": "[1-2 frases: 'Prima X€ vs mercado Y€. Compañías: Generali/Mapfre/Mutua a Z€/año']",
      "veredicto": "[exactamente uno de: CARO, NORMAL, BARATO, POSITIVO]"
    },
    "alternativas": {
      "diferencia_eur": [ahorro anual estimado vs la alternativa más económica mencionada (positivo=hay ahorro, negativo=no merece cambiarse). null si no aplica],
      "descripcion": "[2-3 compañías concretas con precios orientativos y coberturas equivalentes]",
      "veredicto": "[exactamente uno de: CARO, NORMAL, BARATO, POSITIVO]"
    },
    "recomendacion": "[2-3 frases directas. Qué haría alguien que quiere protegerte.]"
  },
  "preguntas_clave": ["pregunta 1", "pregunta 2", "pregunta 3"]
}

CRITERIOS puntuacion_transparencia:
- 80-100: Prima competitiva, coberturas claras, sin exclusiones sorpresa, sin carencias abusivas
- 50-79: Precio OK pero exclusiones importantes o alguna cláusula mejorable
- 20-49: Sobrecoste significativo, exclusiones amplias, carencias largas
- 0-19: Prima 3x mercado, exclusiones que vacían la cobertura, vinculado a hipoteca con engaño
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

// ── Override with pre-calculated numbers ──
if (!empty($calcData)) {
    if (isset($calcData['prima_mensual']))
        $analysis['numeros_reales']['prima_mensual'] = $calcData['prima_mensual'];
    if (isset($calcData['tasa_sobre_capital_pct']))
        $analysis['numeros_reales']['tasa_sobre_capital_pct'] = $calcData['tasa_sobre_capital_pct'];
    if (isset($calcData['sobrecoste_vinculacion_anual']))
        $analysis['sobrecoste_vinculacion_anual'] = $calcData['sobrecoste_vinculacion_anual'];
}

// Always override with user-provided explicit values
if ($primaAnual)  $analysis['numeros_reales']['prima_anual']       = $primaAnual;
if ($capitalAseg) $analysis['numeros_reales']['capital_asegurado'] = $capitalAseg;
if ($vinculadoHip) $analysis['vinculado_hipoteca'] = true;

// ── Sanitize ──
$analysis['puntuacion_transparencia'] = max(0, min(100, (int)($analysis['puntuacion_transparencia'] ?? 50)));

$validTipos = ['HOGAR', 'VIDA', 'COCHE', 'SALUD', 'MULTIRRIESGO', 'OTRO'];
if (!in_array($analysis['tipo_seguro'] ?? '', $validTipos)) {
    $analysis['tipo_seguro'] = 'OTRO';
}

if (!isset($analysis['exclusiones_criticas']) || !is_array($analysis['exclusiones_criticas'])) {
    $analysis['exclusiones_criticas'] = [];
}
if (!isset($analysis['coberturas_duplicadas']) || !is_array($analysis['coberturas_duplicadas'])) {
    $analysis['coberturas_duplicadas'] = [];
}
if (!isset($analysis['trampa']) || !is_array($analysis['trampa'])) {
    $analysis['trampa'] = [];
}
if (!isset($analysis['ventajas']) || !is_array($analysis['ventajas'])) {
    $analysis['ventajas'] = [];
}

$finalJson = json_encode($analysis, JSON_UNESCAPED_UNICODE);

@file_put_contents($cacheFile, $finalJson, LOCK_EX);
_pruneCache($cacheDir, $cacheTtl);

echo $finalJson;
