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

function fetchCarListing(string $url): ?string {
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
                if (in_array($type, ['Car', 'Vehicle', 'Product', 'Offer'])) {
                    $structuredData = json_encode($item, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                    break 2;
                }
            }
        }
    }

    // ── 3. Fine print / legal text extraction (CRITICAL for car ads) ──
    // These sections hide TAE, conditions, restrictions, etc.
    $finePrintParts = [];

    // 3a. <small> tags
    if (preg_match_all('/<small[^>]*>(.*?)<\/small>/si', $html, $sm)) {
        foreach ($sm[1] as $s) {
            $t = cleanToText($s);
            if (strlen($t) > 15) $finePrintParts[] = $t;
        }
    }

    // 3b. Blocks with legal/disclaimer/conditions class or ID
    // Covers: SEAT (disclaimer-component), VW, Peugeot, Renault, Coches.net, etc.
    $legalClassPattern =
        '/class=["\'][^"\']*(?:disclaimer|legal|footnote|nota-legal|nota[_-]pie|condiciones|'
      . 'terms|aviso|asterisk|financiacion-info|offer-detail|price-footnote|'
      . 'modal-legal|ficha-legal|cuadro-financiero|financial-disclaimer|'
      . 'offer-conditions|legal-text|conditions-text)[^"\']*["\']/i';
    $legalIdPattern =
        '/id=["\'][^"\']*(?:disclaimer|legal|footnote|condiciones|terms|notas|'
      . 'financiacion|aviso|cuadro-financiero)[^"\']*["\']/i';

    // Match div/section/article/p containing these attributes (up to 8000 chars inner content)
    $blockPattern = '/<(?:div|section|article|aside|p|dl)\b([^>]*)>((?:[^<]|<(?!\/?(?:div|section|article|aside|p|dl)\b))*)<\/(?:div|section|article|aside|p|dl)>/si';
    if (preg_match_all($blockPattern, $html, $bm)) {
        foreach ($bm[1] as $i => $attrs) {
            if (preg_match($legalClassPattern, $attrs) || preg_match($legalIdPattern, $attrs)) {
                $t = cleanToText($bm[2][$i]);
                if (strlen($t) > 20) $finePrintParts[] = $t;
            }
        }
    }

    // 3c. Paragraphs and divs that explicitly mention TAE, TIN, cuota, financiación, meses, etc.
    $financingKeywords = '/TAE\s*:|TIN\s*:|cuota\s+(?:mensual|final)|entrada\s*:|plazo\s*:|meses\s*:|residual|comisi[oó]n|importe\s+total|precio\s+a\s+plazos|intereses\s+totales|coste\s+total\s+del\s+cr[eé]dito|financiado\s+por|oferta\s+v[aá]lida/ui';
    if (preg_match_all('/<(?:p|div|li|td|dd)\b[^>]*>(.*?)<\/(?:p|div|li|td|dd)>/si', $html, $pm)) {
        foreach ($pm[1] as $p) {
            $t = cleanToText($p);
            if (strlen($t) > 10 && preg_match($financingKeywords, $t)) {
                $finePrintParts[] = $t;
            }
        }
    }

    // 3d. All <li> in lists near financing context
    if (preg_match_all('/<ul[^>]*>(.*?)<\/ul>/si', $html, $ulm)) {
        foreach ($ulm[1] as $ul) {
            $ulText = cleanToText($ul);
            if (preg_match($financingKeywords, $ulText) && strlen($ulText) > 20) {
                $finePrintParts[] = $ulText;
            }
        }
    }

    // 3e. Tables (financing comparison tables, price breakdowns)
    if (preg_match_all('/<table[^>]*>(.*?)<\/table>/si', $html, $tm)) {
        foreach ($tm[1] as $table) {
            $t = cleanToText($table);
            if (preg_match($financingKeywords, $t) && strlen($t) > 20) {
                $finePrintParts[] = $t;
            }
        }
    }

    // 3f. data-* attributes that contain price/offer info (e.g. data-price="14489", data-tae="9.46")
    // Also covers Spanish car portal conventions: data-pvp, data-precio-contado, data-cash-price, etc.
    $dataMatches = [];
    if (preg_match_all('/data-(?:price|cash|pvp|precio|contado|tae|tin|cuota|monthly|offer|amount|total|entry|months|residual|balloon)[^=\s>]*=["\']([^"\']+)["\']/i', $html, $dm)) {
        foreach ($dm[0] as $i => $fullAttr) {
            $key = preg_replace('/data-([^=]+)=.*/i', '$1', $fullAttr);
            $dataMatches[] = $key . ': ' . $dm[1][$i];
        }
    }
    if ($dataMatches) {
        $finePrintParts[] = "DATOS PRECIO (atributos data-*): " . implode(', ', array_unique($dataMatches));
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
    $result .= "TEXTO PRINCIPAL DEL ANUNCIO:\n" . mb_substr($text, 0, 4500);

    return $result;
}

$input         = json_decode(file_get_contents('php://input'), true);
$rawOfferText  = trim($input['offer_text']    ?? '');
$offerText     = $rawOfferText;
$precioContado = (int)($input['precio_contado'] ?? 0);
$entrada       = (int)($input['entrada']        ?? 0);
$cuota         = (float)($input['cuota']        ?? 0);
$meses         = (int)($input['meses']          ?? 0);
$valorResidual = (int)($input['valor_residual'] ?? 0);
$taeAnunciada  = (float)($input['tae_anunciada'] ?? 0);
$fileBase64A    = $input['file_base64']       ?? null;
$fileMediaTypeA = $input['file_media_type']   ?? null;
$compareMode    = (bool)($input['compare_mode'] ?? false);
$offerTextB     = trim($input['offer_text_b'] ?? '');
$fileBase64B    = $input['file_base64_b']     ?? null;
$fileMediaTypeB = $input['file_media_type_b'] ?? null;

// ── URL detection & fetching ──
$isUrl        = (bool) preg_match('/^https?:\/\//i', $rawOfferText);
$urlSource    = null;   // original URL (shown in prompt context)

if ($isUrl) {
    $fetched = fetchCarListing($rawOfferText);
    if ($fetched && strlen($fetched) > 250) {
        $urlSource = $rawOfferText;
        $offerText = $fetched;
    } else {
        http_response_code(422);
        echo json_encode([
            'error'           => 'No se pudo leer el contenido del enlace automáticamente. Esto pasa con Wallapop y otras webs que cargan con JavaScript. Copia el texto del anuncio y pégalo directamente aquí.',
            'url_fetch_failed' => true,
        ]);
        exit;
    }
}

if (!$offerText && !$fileBase64A && (!$cuota || !$meses)) {
    http_response_code(400);
    echo json_encode(['error' => 'Pega la oferta del concesionario o introduce al menos la cuota y los meses.']);
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
$cacheDir = __DIR__ . '/data/cache_coches';
if (!is_dir($cacheDir)) @mkdir($cacheDir, 0755, true);
$cacheKey  = md5($rawOfferText . '|' . $cuota . '|' . $meses . '|' . $entrada . '|' . $valorResidual . '|' . $precioContado . '|' . ($fileBase64A ? md5($fileBase64A) : '') . '|' . ($compareMode ? '1' : '0') . '|' . $offerTextB . '|' . ($fileBase64B ? md5($fileBase64B) : ''));
$cacheFile = $cacheDir . '/' . $cacheKey . '.json';
$cacheTtl  = 7 * 24 * 3600; // 7 days

if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTtl) {
    echo file_get_contents($cacheFile);
    exit;
}

// ── Pre-calculate numbers in PHP for accuracy ──
$preCalc  = '';
$calcData = [];

if ($cuota > 0 && $meses > 0) {
    $totalCuotas       = round($cuota * $meses, 2);
    $totalPagado       = $entrada + $totalCuotas + $valorResidual;
    $calcData['total_cuotas']  = $totalCuotas;
    $calcData['total_pagado']  = $totalPagado;

    if ($precioContado > 0) {
        $costeFinanciacion         = round($totalPagado - $precioContado, 2);
        $porcentajeExtra           = round($costeFinanciacion / $precioContado * 100, 1);
        $calcData['coste_financiacion'] = $costeFinanciacion;
        $calcData['porcentaje_extra']   = $porcentajeExtra;
    }

    $preCalc = "CÁLCULOS PRE-VERIFICADOS (usa estos valores exactos en tu respuesta JSON, no los recalcules):
- Entrada: " . number_format($entrada, 0, ',', '.') . "€
- Total cuotas: " . number_format($totalCuotas, 2, ',', '.') . "€ ({$cuota}€ × {$meses} meses)
- Pago final / Valor residual: " . number_format($valorResidual, 0, ',', '.') . "€
- TOTAL PAGADO: " . number_format($totalPagado, 2, ',', '.') . "€" .
    ($precioContado > 0 ? "\n- Coste de financiación: " . number_format($costeFinanciacion, 2, ',', '.') . "€ ({$porcentajeExtra}% más que al contado)" : '');
}

if ($urlSource) {
    $offerSection = "CONTENIDO EXTRAÍDO DEL ANUNCIO (fuente: {$urlSource}):\n\"\"\"\n{$offerText}\n\"\"\"\n";
} elseif ($offerText) {
    $offerSection = "TEXTO DEL ANUNCIO / OFERTA:\n\"\"\"\n{$offerText}\n\"\"\"\n";
} else {
    $offerSection = '';
}
$datosSection  = '';
if ($precioContado || $entrada || $cuota || $meses || $valorResidual || $taeAnunciada) {
    $datosSection = "DATOS INTRODUCIDOS POR EL USUARIO:\n";
    if ($precioContado)  $datosSection .= "- Precio al contado: {$precioContado}€\n";
    if ($entrada)        $datosSection .= "- Entrada inicial: {$entrada}€\n";
    if ($cuota)          $datosSection .= "- Cuota mensual: {$cuota}€\n";
    if ($meses)          $datosSection .= "- Número de meses: {$meses}\n";
    if ($valorResidual)  $datosSection .= "- Pago final / VFG: {$valorResidual}€\n";
    if ($taeAnunciada)   $datosSection .= "- TAE anunciada por el concesionario: {$taeAnunciada}%\n";
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
Eres el analizador de coches más brutal y honesto de España. No suavizas nada. Tu misión es proteger al usuario de concesionarios, vendedores particulares y financieras que intentan sacarle el dinero. Usas datos reales del mercado español. Si el trato es una basura, lo dices sin rodeos.

CONOCIMIENTO DEL MERCADO ESPAÑOL QUE DEBES APLICAR:

Fiscalidad segunda mano:
- ITP (Impuesto Transmisiones Patrimoniales): 4-8% del precio según CCAA (media nacional ~6%). Ejemplo: coche a 12.000€ → ~720€ de ITP
- IVTM (Impuesto Vehículos Tracción Mecánica): 60-300€/año según potencia y municipio
- Seguro obligatorio: mínimo 300-600€/año RC. A todo riesgo: 700-1.800€/año según perfil y vehículo

Fiscalidad vehículo nuevo:
- Impuesto matriculación por CO2: 0% (≤120g/km), 4,75% (121-160g/km), 9,75% (161-200g/km), 14,75% (>200g/km)
- IVA incluido en precio de venta (21%)

Financiación — referencias 2024-2025:
- TAE media préstamo personal banco para coche: 7-11%
- TAE media financiación concesionario: 8-14% (aunque anuncian TIN bajo)
- Si TAE real > 15%: abuso claro. Si > 20%: territorio usura
- Seguros vinculados obligatorios añaden típicamente 1-3 puntos a la TAE efectiva
- VFG/Balloon trampa habitual: te meten 30-40% del precio como cuota final y no te avisan del riesgo de depreciación
- Renting particular: nunca es tuyo. Mínimo 36 meses. Penalizaciones por km extra (0,08-0,20€/km) y daños

Precios de mercado segunda mano (orientación por segmento, 2025):
- Utilitario 3-5 años, 50-80k km: 8.000-15.000€ (Polo, Ibiza, 208, Clio...)
- Compacto 3-5 años, 50-80k km: 14.000-22.000€ (Golf, León, Megane, Focus...)
- SUV mediano 3-5 años, 50-80k km: 18.000-30.000€ (Tiguan, Ateca, Peugeot 3008...)
- Diésel >150k km: descontar 15-25% sobre precio base del segmento
- Eléctrico puro >3 años: depreciación alta (35-50%), batería es el riesgo principal
- Híbrido enchufable: descontar 10-20% si batería sin garantía

MODELOS CON PROBLEMAS CONOCIDOS — menciónalo si el anuncio corresponde:
Mecánicos/fiabilidad:
- VW/Seat/Skoda DSG 7 velocidades (DQ200): problemas en arranques, tirones a baja velocidad. Años críticos: 2010-2016
- Peugeot/Citroën 1.2 PureTech 3 cilindros: consumo excesivo de aceite, cadena distribución. Años críticos: 2012-2018
- Ford Kuga PHEV 2020-2021: incendios en carga (recall oficial)
- Renault 1.2 TCe 115CV: problemas de culata y correa distribución. Años: 2012-2017
- BMW N47 (diésel 2.0): cadena distribución crítica trasera. Años: 2007-2014
- Nissan Qashqai 1.6 DCI: distribución compleja, cara de mantener. Años: 2013-2016
- Toyota/Lexus híbridos: muy fiables, batería garantizada 10 años (post-2020)
- Dacia Logan/Sandero/Duster: mecánica sencilla, muy fiables, baratos de mantener

Carrocería/electrónica:
- Land Rover/Range Rover cualquier año: electrónica problemática, carísimos de reparar
- Alfa Romeo post-2010: buena mecánica pero electrónica poco fiable
- Cualquier diésel Euro 5 o anterior en ciudad: riesgo de DPF obstruido si uso urbano

COMPARATIVAS CON CIFRAS CONCRETAS — siempre en estos términos:
- No digas "sale caro". Di: "pagas X€ más que al banco, equivale a Y meses de cuota tirados"
- No digas "busca otras opciones". Di: "en Coches.net encuentras [modelo similar] por Z€ menos"
- No digas "el coche tiene ciertos kilómetros". Di: "con 80.000 km, este motor tiene X años de vida útil estimada"

SEÑALES DE FRAUDE EN SEGUNDA MANO (clasifícalas en alertas_fraude, NO en trampa):
🚨 FRAUDE PROBABLE (riesgo_fraude 70-100):
- Precio 35%+ por debajo de mercado sin justificación clara
- Coche en UK/Alemania/otro país que hay que "importar" o "transferir"
- Vendedor pide pago por transferencia/Bizum antes de ver el coche
- Historia emocional (herencia, divorcio, emigración) para justificar precio bajo
- Solo contacto por WhatsApp o email, no acepta llamadas
- Fotos genéricas o del fabricante, no del coche real
- "No puedo enseñarlo, te lo traen a casa"
- Documentación incompleta o promesa de tramitarla después

🔶 BANDERAS AMARILLAS (riesgo_fraude 30-69):
- Km redondos sospechosos (exactamente 10.000, 20.000, 100.000...)
- Precio 20-35% por debajo de mercado sin explicación
- Sin ITV reciente, sin historial de mantenimiento
- Urgencia de venta injustificada
- Primer registro no coincide con año anunciado
- Muchos propietarios anteriores (>3 en coches con pocos años)

{$comparePreamble}{$offerSection}{$compareSuffix}
{$datosSection}
{$preCalc}

EXTRACCIÓN DEL PRECIO AL CONTADO — reglas estrictas:
- El campo "precio_contado" debe ser el PRECIO TOTAL DEL VEHÍCULO sin financiar, no la cuota mensual
- PRIORIDAD MÁXIMA: Si el anuncio muestra EXPLÍCITAMENTE una etiqueta "Precio al contado: X€" o "PVP: X€", usa ESE valor, aunque haya otro precio más bajo en grande (ese precio menor suele ser el precio con financiación o condicionado a oferta)
- Ejemplo Flexicar: muestra "21.990€" en grande + "343€/mes" Y también "Precio al contado: 25.990€" → precio_contado = 25990 (el etiquetado explícitamente)
- En anuncios de segunda mano sin financiación: el precio pedido único es el precio_contado
- En ofertas de concesionario: busca "PVP", "precio al contado", "precio sin financiar", "precio de venta", "precio sin oferta"
- El precio "Desde X€" o el precio mensual NUNCA es el precio_contado
- NUNCA pongas la cuota mensual ni el total financiado como precio_contado
- Si el anuncio NO menciona un precio de contado claro, pon null (no inventes un precio)

LEE TODA LA LETRA PEQUEÑA — esto es lo más importante:
Cuando el contenido viene de una URL, el bloque "=== LETRA PEQUEÑA / CONDICIONES LEGALES ===" contiene
las condiciones reales de financiación que el anunciante esconde. DEBES:
- Extraer de ahí el TAE real, TIN, comisiones de apertura, cuota final/balloon, importe total adeudado
- Identificar "OAC" (Oferta Sujeta a Aprobación de Crédito), seguros vinculados obligatorios, km mínimos
- Detectar la diferencia entre precio "desde" y precio real del vehículo anunciado
- Leer las fechas de validez de la oferta y si ha expirado
- Buscar: entidad financiera, banco financiador, condiciones de cancelación anticipada
- Señalar cuando el precio anunciado en grande NO incluye la entrada o no es el precio final real

MODO DE ANÁLISIS — determina automáticamente:

A) OFERTA DE FINANCIACIÓN (concesionario, renting, leasing, PCP):
   - Calcula TAE real exacta: usa los datos de la letra pequeña (importe total adeudado vs importe crédito)
   - Si tienes "importe total adeudado" e "importe total del crédito", la diferencia SON los intereses reales
   - Desvela el coste total en €: cuánto pagas en total vs precio al contado
   - Si hay cuota final/balloon grande (ej: 10.000€), destácalo como trampa principal
   - Comisión de apertura: suma al coste real, a menudo no aparece en la cuota mensual
   - Compara con préstamo bancario (TAE ~8-10%) en cifras concretas: "pagarías X€ menos"
   - Di si la oferta ha expirado (fecha de validez en letra pequeña)

B) ANUNCIO DE COCHE DE SEGUNDA MANO:
   - Compara el precio pedido con precio de mercado real para ese modelo/año/km exactos
   - Calcula ITP, IVTM, seguro mínimo como costes adicionales reales
   - Detecta señales de fraude aplicando la lista anterior
   - Sé específico con cifras: "este coche vale entre X€ y Y€ en el mercado hoy"

RESPONDE ÚNICAMENTE con un objeto JSON válido, sin markdown, sin texto antes ni después:

{
  "modo_analisis": "[exactamente uno de: FINANCIACION, SEGUNDA_MANO]",
  "resumen_oferta": "[descripción directa en 1-2 frases: vehículo, precio, condiciones principales]",
  "tipo_financiacion": "[uno de: PRESTAMO_PERSONAL, PRESTAMO_CONCESIONARIO, LEASING, RENTING, PCP, COMPRA_DIRECTA]",
  "numeros_reales": {
    "precio_contado": [precio de venta o precio al contado en €, número entero, o null],
    "entrada": [entrada en €, número entero, o null],
    "total_cuotas": [suma de cuotas en €, 2 decimales, o null],
    "pago_final": [VFG/balloon en €, número entero, o null],
    "total_pagado": [coste total real en €, 2 decimales, o null],
    "coste_financiacion": [sobrecoste por financiar en €, o null],
    "porcentaje_extra": [sobrecoste en %, 1 decimal, o null]
  },
  "tae_real": [TAE real calculada, 2 decimales, o null si no aplica],
  "tae_anunciada": [TAE que anuncian, número, o null],
  "puntuacion_transparencia": [0-100: 100=completamente honesto y claro, 0=diseñado para engañar],
  "riesgo_fraude": [0-100: 0=ningún riesgo, 100=fraude prácticamente seguro. Para financiación normalmente 0-20 salvo condiciones abusivas],
  "veredicto": "[máximo 15 palabras. Sin adornos. Di exactamente si es buena, mala o una trampa]",
  "alertas_fraude": [
    "[señal clara de posible estafa o fraude. Array vacío [] si no hay señales de fraude]"
  ],
  "trampa": [
    "[cláusula abusiva, coste oculto o punto de atención importante que el usuario DEBE conocer]"
  ],
  "ventajas": [
    "[ventaja real y objetiva, si existe. Array vacío [] si no hay ninguna]"
  ],
  "costes_adicionales": {
    "aplica": [true si es segunda mano, false si es financiación de nuevo],
    "itp_estimado": "[ITP estimado en € (precio × 6% aprox). Ejemplo: '720€ (6% de 12.000€ aprox.)']",
    "ivtm_anual": "[IVTM orientativo en €/año según potencia]",
    "seguro_minimo_anual": "[seguro RC mínimo estimado en €/año]",
    "total_extra_estimado": [suma orientativa de todos los costes adicionales el primer año, número entero],
    "nota": "[en 1 frase: 'Además del precio, calcula estos gastos el primer año']"
  },
  "comparativa": {
    "vs_contado": "[SIEMPRE con cifras exactas en €. Financiación: 'Pagas X€ más que al contado, equivale a Y meses de cuota extra'. Segunda mano: 'Este modelo/año/km vale entre X€ y Y€ en el mercado, te piden Z€, está [por encima/debajo/en línea]']",
    "vs_banco": "[SIEMPRE con cifras en €. Financiación: 'Con préstamo bancario al 9% TAE pagarías X€ menos en intereses'. Segunda mano: 'Existen alternativas similares en el mercado por X-Y€ menos']",
    "recomendacion": "[2-3 frases directas. Qué haría una persona con criterio que NO tiene interés en venderte nada. Menciona si el modelo tiene problemas conocidos. Sin suavizar]"
  },
  "preguntas_clave": [
    "[pregunta directa y específica que el usuario DEBE hacer antes de firmar o comprar]"
  ]
}

CRITERIOS DE PUNTUACIÓN:
Transparencia (puntuacion_transparencia):
- 80-100: Datos completos, precio de mercado, sin letra pequeña, honesto
- 50-79: Datos principales OK pero algo opaco o mejorable
- 20-49: Información incompleta, condiciones enterradas, precio sospechoso
- 0-19: Diseñado para confundir o engañar activamente

Riesgo de fraude (riesgo_fraude):
- 0-20: Normal, sin señales de fraude
- 21-50: Alguna bandera amarilla, proceder con cautela
- 51-80: Múltiples banderas rojas, muy probable engaño
- 81-100: Fraude casi seguro, no compres

REGLAS DE CONTENIDO:
- "alertas_fraude": Solo si hay señales reales de fraude/estafa. [] si no hay.
- "trampa": Siempre 2-4 puntos (cláusulas abusivas, costes ocultos, puntos de atención)
- "ventajas": 0-3 puntos. [] si no hay ninguna ventaja real
- "preguntas_clave": Exactamente 3 preguntas específicas y útiles
- "costes_adicionales": Siempre rellénalo, aplica=true para segunda mano, aplica=false para financiación nueva (y pon null/0 en los campos numéricos si no aplica)
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
    if (isset($calcData['total_cuotas']))       $analysis['numeros_reales']['total_cuotas']       = $calcData['total_cuotas'];
    if (isset($calcData['total_pagado']))        $analysis['numeros_reales']['total_pagado']        = $calcData['total_pagado'];
    if (isset($calcData['coste_financiacion']))  $analysis['numeros_reales']['coste_financiacion']  = $calcData['coste_financiacion'];
    if (isset($calcData['porcentaje_extra']))    $analysis['numeros_reales']['porcentaje_extra']    = $calcData['porcentaje_extra'];
}
// Always override with user-provided explicit values (independent of calcData)
if ($entrada)        $analysis['numeros_reales']['entrada']        = $entrada;
if ($valorResidual)  $analysis['numeros_reales']['pago_final']     = $valorResidual;
if ($precioContado)  $analysis['numeros_reales']['precio_contado'] = $precioContado;

// For direct purchases (segunda mano / compra directa): total_pagado = precio_contado
$isDirecta = (
    ($analysis['tipo_financiacion'] ?? '') === 'COMPRA_DIRECTA' ||
    ($analysis['modo_analisis']     ?? '') === 'SEGUNDA_MANO'
);
if ($isDirecta && empty($analysis['numeros_reales']['total_pagado'])) {
    $pc = $analysis['numeros_reales']['precio_contado'] ?? null;
    if ($pc) $analysis['numeros_reales']['total_pagado'] = $pc;
}

// Sanitize
$analysis['puntuacion_transparencia'] = max(0, min(100, (int)($analysis['puntuacion_transparencia'] ?? 50)));
$analysis['riesgo_fraude']            = max(0, min(100, (int)($analysis['riesgo_fraude']            ?? 0)));

$validModos = ['FINANCIACION', 'SEGUNDA_MANO'];
if (!in_array($analysis['modo_analisis'] ?? '', $validModos)) {
    $analysis['modo_analisis'] = 'FINANCIACION';
}

$validTipos = ['PRESTAMO_PERSONAL','PRESTAMO_CONCESIONARIO','LEASING','RENTING','PCP','COMPRA_DIRECTA'];
if (!in_array($analysis['tipo_financiacion'] ?? '', $validTipos)) {
    $analysis['tipo_financiacion'] = 'COMPRA_DIRECTA';
}

if (!isset($analysis['alertas_fraude']) || !is_array($analysis['alertas_fraude'])) {
    $analysis['alertas_fraude'] = [];
}
if (!isset($analysis['costes_adicionales']) || !is_array($analysis['costes_adicionales'])) {
    $analysis['costes_adicionales'] = ['aplica' => false];
}

$finalJson = json_encode($analysis, JSON_UNESCAPED_UNICODE);

@file_put_contents($cacheFile, $finalJson, LOCK_EX);
_pruneCache($cacheDir, $cacheTtl);

echo $finalJson;
