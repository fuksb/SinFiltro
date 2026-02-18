<?php
header('Content-Type: application/json; charset=UTF-8');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../ai-helper.php';
require_once __DIR__ . '/../scraper/puppeteer-wrapper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// =========================================================
// VIN DECODER
// =========================================================

function decodeVin(string $raw): array {
    $vin = strtoupper(trim($raw));

    if (strlen($vin) !== 17)
        return ['valid' => false, 'error' => 'El VIN debe tener exactamente 17 caracteres (tiene ' . strlen($vin) . ')'];
    if (preg_match('/[IOQ]/', $vin))
        return ['valid' => false, 'error' => 'El VIN no puede contener las letras I, O o Q'];
    if (!preg_match('/^[A-HJ-NPR-Z0-9]{17}$/', $vin))
        return ['valid' => false, 'error' => 'El VIN contiene caracteres no válidos'];

    $wmi = substr($vin, 0, 3);

    return [
        'valid'        => true,
        'vin'          => $vin,
        'country'      => _vinCountry($vin[0], $vin[1]),
        'manufacturer' => _vinManufacturer($wmi),
        'wmi'          => $wmi,
        'model_year'   => _vinModelYear($vin[9]),
        'plant_code'   => $vin[10],
        'sequence'     => substr($vin, 11),
        'check_ok'     => _vinCheckDigit($vin),
    ];
}

function _vinCountry(string $c1, string $c2): string {
    // Specific sub-ranges
    if ($c1 === 'V') {
        if ($c2 >= 'A' && $c2 <= 'E') return 'Austria';
        if ($c2 >= 'F' && $c2 <= 'R') return 'Francia';
        if ($c2 >= 'S' && $c2 <= 'W') return 'España';
    }
    if ($c1 === 'S') {
        if ($c2 >= 'A' && $c2 <= 'M') return 'Reino Unido';
        if ($c2 >= 'N' && $c2 <= 'T') return 'Alemania';
        if ($c2 >= 'U' && $c2 <= 'Z') return 'Polonia';
    }
    if ($c1 === 'T') {
        if (in_array($c2, ['A','B','C','D','E','F','G','H'])) return 'Suiza';
        if (in_array($c2, ['J','K','L','M','N','P']))          return 'República Checa';
        if (in_array($c2, ['R','S','T','U','V']))              return 'Hungría';
        if (in_array($c2, ['W','X','Y','Z','1']))              return 'Portugal';
    }
    $map = [
        'A'=>'Sudáfrica','J'=>'Japón','K'=>'Corea del Sur','L'=>'China',
        'M'=>'India','N'=>'Turquía','P'=>'Filipinas','R'=>'Indonesia',
        'W'=>'Alemania','X'=>'Rusia','Y'=>'Países nórdicos / Bélgica','Z'=>'Italia',
        '1'=>'EE.UU.','2'=>'Canadá','3'=>'México','4'=>'EE.UU.','5'=>'EE.UU.',
        '6'=>'Australia','7'=>'Nueva Zelanda',
        '8'=>'Argentina / Chile / Ecuador / Venezuela','9'=>'Brasil / Colombia',
    ];
    return $map[$c1] ?? 'País desconocido';
}

function _vinManufacturer(string $wmi): string {
    $brands = [
        // Alemania
        'WBA'=>'BMW','WBS'=>'BMW M GmbH','WBY'=>'BMW (eléctrico)',
        'WDB'=>'Mercedes-Benz','WDD'=>'Mercedes-Benz','WDC'=>'Mercedes-Benz (SUV)',
        'WDF'=>'Mercedes-Benz (furgoneta)','W1K'=>'Mercedes-Benz','WME'=>'Smart',
        'WVW'=>'Volkswagen','WVG'=>'Volkswagen (SUV)',
        'WV1'=>'Volkswagen (comercial)','WV2'=>'Volkswagen (autobús)',
        'WAU'=>'Audi','WUA'=>'Audi quattro GmbH',
        'WP0'=>'Porsche','WP1'=>'Porsche Cayenne','WAP'=>'Porsche',
        'WF0'=>'Ford (Europa)','W0L'=>'Opel','W0V'=>'Opel',
        'WMA'=>'MAN (camión)',
        // España
        'VSS'=>'SEAT','VSE'=>'SEAT (eléctrico)',
        'VS6'=>'Ford (España)','VS7'=>'Citroën (España)',
        // Francia
        'VF1'=>'Renault','VF3'=>'Renault (Dacia)','VF4'=>'Renault',
        'VF6'=>'Peugeot','VF7'=>'Citroën','VF8'=>'Matra',
        'VNK'=>'Toyota (Francia)','VFC'=>'Renault Trucks',
        // Italia
        'ZAR'=>'Alfa Romeo','ZFA'=>'Fiat','ZFF'=>'Ferrari',
        'ZHW'=>'Lamborghini','ZAM'=>'Maserati','ZCF'=>'Iveco',
        // Reino Unido
        'SAL'=>'Land Rover','SAJ'=>'Jaguar','SCF'=>'Aston Martin',
        'SCC'=>'Lotus','SHH'=>'Honda (UK)',
        // Rep. Checa
        'TM8'=>'Škoda','TMB'=>'Škoda',
        // Hungría
        'TRU'=>'Audi (Hungría)',
        // Japón
        'JHM'=>'Honda','JH4'=>'Acura','JHL'=>'Honda',
        'JM1'=>'Mazda','JMB'=>'Mitsubishi','JMZ'=>'Mazda',
        'JN1'=>'Nissan','JN6'=>'Nissan','JNA'=>'Infiniti',
        'JS1'=>'Suzuki','JS3'=>'Suzuki',
        'JT2'=>'Toyota','JT3'=>'Toyota','JTD'=>'Toyota',
        'JTJ'=>'Lexus','JTK'=>'Lexus',
        'JA3'=>'Mitsubishi','JA4'=>'Mitsubishi',
        // Corea
        'KMH'=>'Hyundai','KMF'=>'Hyundai (comercial)',
        'KNA'=>'Kia','KNM'=>'Kia',
        // Suecia
        'YV1'=>'Volvo','YV4'=>'Volvo (SUV)','YS3'=>'Saab',
        // EE.UU.
        '1FA'=>'Ford (EE.UU.)','1G1'=>'Chevrolet','1G6'=>'Cadillac',
        '1HG'=>'Honda (EE.UU.)','1N4'=>'Nissan (EE.UU.)','1VW'=>'Volkswagen (EE.UU.)',
        // Canadá
        '2HG'=>'Honda (Canadá)',
        // México
        '3VW'=>'Volkswagen (México)',
    ];
    return $brands[$wmi] ?? null;
}

function _vinModelYear(string $c): string {
    $map = [
        'A'=>'1980 o 2010','B'=>'1981 o 2011','C'=>'1982 o 2012',
        'D'=>'1983 o 2013','E'=>'1984 o 2014','F'=>'1985 o 2015',
        'G'=>'1986 o 2016','H'=>'1987 o 2017','J'=>'1988 o 2018',
        'K'=>'1989 o 2019','L'=>'1990 o 2020','M'=>'1991 o 2021',
        'N'=>'1992 o 2022','P'=>'1993 o 2023','R'=>'1994 o 2024',
        'S'=>'1995 o 2025','T'=>'1996 o 2026','V'=>'1997',
        'W'=>'1998','X'=>'1999','Y'=>'2000',
        '1'=>'2001','2'=>'2002','3'=>'2003','4'=>'2004',
        '5'=>'2005','6'=>'2006','7'=>'2007','8'=>'2008','9'=>'2009',
    ];
    return $map[$c] ?? 'Desconocido';
}

function _vinCheckDigit(string $vin): bool {
    $weights = [8,7,6,5,4,3,2,10,0,9,8,7,6,5,4,3,2];
    $vals = [
        '0'=>0,'1'=>1,'2'=>2,'3'=>3,'4'=>4,'5'=>5,'6'=>6,'7'=>7,'8'=>8,'9'=>9,
        'A'=>1,'B'=>2,'C'=>3,'D'=>4,'E'=>5,'F'=>6,'G'=>7,'H'=>8,
        'J'=>1,'K'=>2,'L'=>3,'M'=>4,'N'=>5,'P'=>7,'R'=>9,
        'S'=>2,'T'=>3,'U'=>4,'V'=>5,'W'=>6,'X'=>7,'Y'=>8,'Z'=>9,
    ];
    $sum = 0;
    for ($i = 0; $i < 17; $i++) $sum += ($vals[$vin[$i]] ?? 0) * $weights[$i];
    $remainder = $sum % 11;
    $check = $remainder === 10 ? 'X' : (string)$remainder;
    return $check === $vin[8];
}

// ── URL fetching & HTML extraction ──
function cleanToText(string $html): string {
    $text = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = strip_tags($text);
    $text = preg_replace('/[ \t]{2,}/', ' ', $text);
    $text = preg_replace('/(\s*\n\s*){3,}/', "\n\n", $text);
    return trim($text);
}

/**
 * Recursively extracts key:value pairs from a nested JSON structure.
 * Filters by relevant keywords, skips URLs/images, limits depth.
 */
function _extractJsonFields(mixed $node, array $kw, int $maxDepth = 7, int $depth = 0): array {
    $out = [];
    if ($depth >= $maxDepth || !is_array($node)) return $out;
    foreach ($node as $k => $v) {
        $kl = strtolower((string)$k);
        // Skip keys that are clearly not useful
        if (preg_match('/^(?:@|image|icon|logo|photo|thumb|src|href|url|link|css|class|style|token|key|hash|id$|uuid|slug|locale|lang|trans|i18n|color|colour)/i', $kl)) continue;
        if (is_scalar($v) && $v !== null && $v !== '') {
            $vs = (string)$v;
            // Skip URLs, very long strings, and booleans stored as 0/1 with non-matching keys
            if (strlen($vs) > 300 || str_starts_with($vs, 'http') || str_starts_with($vs, '/static')) continue;
            foreach ($kw as $needle) {
                if (str_contains($kl, $needle)) { $out[] = "$k: $vs"; break; }
            }
        } elseif (is_array($v)) {
            $sub = _extractJsonFields($v, $kw, $maxDepth, $depth + 1);
            if ($sub) $out = array_merge($out, $sub);
        }
    }
    return $out;
}

/**
 * Perform a single HTTP GET with the given User-Agent.
 */
function _curlGet(string $url, string $ua, string $cookieJar): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 5,
        CURLOPT_USERAGENT      => $ua,
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
            'Referer: https://www.google.es/',
        ],
        CURLOPT_ENCODING       => '',
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_COOKIEJAR      => $cookieJar,
        CURLOPT_COOKIEFILE     => $cookieJar,
    ]);
    $html  = curl_exec($ch);
    $errno = curl_errno($ch);
    $code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$html, $errno, $code];
}

function fetchCarListing(string $url): ?string {
    // Try Puppeteer first (for JS-rendered pages like Wallapop)
    $puppeteerContent = scrapeWithPuppeteerSimple($url, 45);
    if ($puppeteerContent && strlen($puppeteerContent) > 250) {
        return cleanToText($puppeteerContent);
    }
    
    // Fallback to curl
    $cookieJar = sys_get_temp_dir() . '/sf_ck_' . md5($url) . '_' . getmypid() . '.txt';

    $desktopUA = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36';
    $mobileUA  = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1';

    [$html, $errno, $code] = _curlGet($url, $desktopUA, $cookieJar);

    if ($errno || !$html || $code < 200 || $code >= 400) {
        @unlink($cookieJar);
        return null;
    }

    // ── Retry with mobile UA if desktop got blocked or returned thin content ──
    $textLen = strlen(strip_tags($html));
    if ($textLen < 2000 || $code === 403) {
        $cookieJar2 = sys_get_temp_dir() . '/sf_ck_' . md5($url) . '_m_' . getmypid() . '.txt';
        [$html2, $errno2, $code2] = _curlGet($url, $mobileUA, $cookieJar2);
        @unlink($cookieJar2);
        if (!$errno2 && $html2 && $code2 >= 200 && $code2 < 400 && strlen(strip_tags($html2)) > $textLen) {
            $html = $html2;
        }
    }
    @unlink($cookieJar);

    // ── 1. Open Graph + extended meta tags ──
    $meta = [];
    if (preg_match('/<title[^>]*>([^<]+)<\/title>/i', $html, $m))
        $meta['title'] = html_entity_decode(trim($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');

    // og:, product:, twitter: meta tags
    $metaProps = [
        'og:title', 'og:description', 'og:price:amount', 'og:price:currency',
        'product:price.amount', 'product:price.currency',
        'twitter:title', 'twitter:description',
    ];
    foreach ($metaProps as $tag) {
        $pattern = '/<meta[^>]+(?:property|name)=["\']' . preg_quote($tag, '/') . '["\'][^>]+content=["\']([^"\']*)["\'][^>]*\/?>/i';
        if (preg_match($pattern, $html, $m)) {
            $key = preg_replace('/^(?:og:|product:|twitter:)/', '', $tag);
            $meta[$key] = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
    }
    // Also catch: <meta itemprop="price" content="15990">
    if (preg_match('/<meta[^>]+itemprop=["\']price["\'][^>]+content=["\']([^"\']+)["\'][^>]*\/?>/i', $html, $m))
        $meta['itemprop:price'] = $m[1];

    // ── 2. JSON-LD structured data (expanded: nested offers, ItemList, etc.) ──
    $structuredData = '';
    $jsonLdTypes = ['Car','Vehicle','Product','Offer','ItemList','AutoDealer','OfferCatalog'];
    if (preg_match_all('/<script[^>]+type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/si', $html, $matches)) {
        foreach ($matches[1] as $jsonStr) {
            $decoded = json_decode(trim($jsonStr), true);
            if (!$decoded) continue;
            $items = isset($decoded[0]) ? $decoded : [$decoded];
            foreach ($items as $item) {
                $type = $item['@type'] ?? '';
                if (in_array($type, $jsonLdTypes)) {
                    $structuredData = json_encode($item, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                    break 2;
                }
                // Also check nested: {"@graph": [...]}
                foreach ($item['@graph'] ?? [] as $node) {
                    if (in_array($node['@type'] ?? '', $jsonLdTypes)) {
                        $structuredData = json_encode($node, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                        break 3;
                    }
                }
            }
        }
    }

    // ── 2b. Server-injected JSON (__NEXT_DATA__, __NUXT__, window.serverData, etc.) ──
    $serverJsonData = '';
    $vehicleKw = [
        'price','precio','pvp','importe','coste','amount','cuota','monthly','tae','tin',
        'entrada','deposit','meses','months','plazo','residual','balloon','financiacion',
        'km','mileage','kilometre','year','ano','matricul','make','brand','marca',
        'model','version','motor','engine','fuel','combustible','cambio','gearbox',
        'doors','puertas','power','potencia','descuento','ahorro','saving','oferta',
        'nombre','titulo','title','description',
    ];
    $serverJsonPatterns = [
        '/<script\b[^>]+id=["\']__NEXT_DATA__["\'][^>]*>(.*?)<\/script>/si',
        '/<script\b[^>]+id=["\']__NUXT_DATA__["\'][^>]*>(.*?)<\/script>/si',
        // window.__INITIAL_STATE__ = {...};
        '/<script\b[^>]*>\s*(?:window\.)?__(?:INITIAL|SERVER|PAGE)_(?:STATE|DATA|PROPS?)__\s*=\s*(\{.{50,}?\})\s*;?\s*<\/script>/si',
        // window.serverData = {...};
        '/<script\b[^>]*>\s*(?:var|let|const|window\.)\s*(?:serverData|pageData|initialData|listingData|vehicleData|appData)\s*=\s*(\{.{50,}?\})\s*;/si',
    ];
    foreach ($serverJsonPatterns as $pat) {
        if (preg_match($pat, $html, $sm)) {
            $blob = trim($sm[1]);
            if (strlen($blob) < 1_500_000) {
                $parsed = json_decode($blob, true);
                if ($parsed) {
                    $fields = array_unique(_extractJsonFields($parsed, $vehicleKw));
                    if (count($fields) >= 3) {
                        $serverJsonData = implode("\n", array_slice($fields, 0, 60));
                        break;
                    }
                }
            }
        }
    }

    // ── 2c. itemprop microdata ──
    $microdata = [];
    // <meta itemprop="..."> and <span itemprop="..." content="...">
    if (preg_match_all('/itemprop=["\']([^"\']+)["\'][^>]*content=["\']([^"\']{1,300})["\']|content=["\']([^"\']{1,300})["\'][^>]*itemprop=["\']([^"\']+)["\']/i', $html, $ipm)) {
        for ($i = 0; $i < count($ipm[0]); $i++) {
            $prop = trim($ipm[1][$i] ?: $ipm[4][$i]);
            $val  = trim($ipm[2][$i] ?: $ipm[3][$i]);
            if ($prop && $val && !str_starts_with($val, 'http')) $microdata[$prop] = $val;
        }
    }
    // <span itemprop="price">15.990 €</span> (text node, no content attr)
    if (preg_match_all('/<(?:span|div|p|strong|h[1-6]|td|li)\b[^>]*itemprop=["\']([^"\']+)["\'][^>]*>([^<]{1,200})<\//i', $html, $itm)) {
        for ($i = 0; $i < count($itm[1]); $i++) {
            $prop = trim($itm[1][$i]);
            $val  = trim(cleanToText($itm[2][$i]));
            if ($prop && $val && !isset($microdata[$prop])) $microdata[$prop] = $val;
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

    // 3g. Financing calculator/simulator inputs (<input type="range"> sliders)
    // Covers simulators used by Coches.net, Flexicar, dealership sites, etc.
    $simulatorLines = [];
    if (preg_match_all('/<input\b[^>]+type=["\']range["\'][^>]*>/i', $html, $rangeInputs)) {
        foreach ($rangeInputs[0] as $inp) {
            $attrs = [];
            foreach (['name','id','min','max','value','step','data-label','data-field'] as $attr) {
                if (preg_match('/' . $attr . '=["\']([^"\']*)["\']/', $inp, $m)) {
                    $attrs[$attr] = $m[1];
                }
            }
            if (!empty($attrs)) {
                $label = $attrs['data-label'] ?? $attrs['name'] ?? $attrs['id'] ?? 'campo';
                $line  = "Simulador-slider [{$label}]: ";
                if (isset($attrs['min'], $attrs['max'])) $line .= "rango {$attrs['min']}..{$attrs['max']}";
                if (isset($attrs['value']))               $line .= ", valor-actual={$attrs['value']}";
                if (isset($attrs['step']))                $line .= ", paso={$attrs['step']}";
                $simulatorLines[] = $line;
            }
        }
    }
    if ($simulatorLines) {
        $finePrintParts[] = "SIMULADOR DE FINANCIACIÓN (sliders detectados): " . implode(' | ', array_unique($simulatorLines));
    }

    // 3h. Footnote / asterisk patterns (very common in car ads: "* Precio válido hasta...")
    $footnotePattern = '/(?:^|\n)\s*\*{1,3}[^*\n].{20,400}/m';
    $htmlDecoded = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    if (preg_match_all($footnotePattern, $htmlDecoded, $fnm)) {
        foreach ($fnm[0] as $fn) {
            $fn = trim($fn);
            if (strlen($fn) > 25) $finePrintParts[] = $fn;
        }
    }

    // Deduplicate and limit fine print
    $finePrintParts = array_values(array_unique($finePrintParts));
    $finePrint = '';
    $fpLen = 0;
    foreach ($finePrintParts as $fp) {
        if ($fpLen + strlen($fp) > 5000) break;
        $finePrint .= $fp . "\n";
        $fpLen += strlen($fp);
    }

    // ── 4. Main body text — prefer main/article content, strip chrome ──
    // Try to extract main content area first (product/listing zone)
    $mainHtml = '';
    foreach (['<main\b[^>]*>(.*?)<\/main>', '<article\b[^>]*>(.*?)<\/article>',
              '<div[^>]+(?:id|class)=["\'][^"\']*(?:listing|product|ficha|detail|anuncio|vehicle|car-detail)[^"\']*["\'][^>]*>(.*?)<\/div>'] as $mp) {
        if (preg_match('/' . $mp . '/si', $html, $mm)) {
            $mainHtml = $mm[1];
            break;
        }
    }

    $sourceHtml = $mainHtml ?: $html;
    $clean = preg_replace([
        '/<script\b[^>]*>.*?<\/script>/si',
        '/<style\b[^>]*>.*?<\/style>/si',
        '/<nav\b[^>]*>.*?<\/nav>/si',
        '/<header\b[^>]*>.*?<\/header>/si',
        '/<footer\b[^>]*>.*?<\/footer>/si',
        '/<!--.*?-->/si',
    ], '', $sourceHtml);

    $text = cleanToText($clean ?: $html);

    // Also check <noscript> blocks (some SSR sites render full content there)
    $noscriptContent = '';
    if (preg_match_all('/<noscript[^>]*>(.*?)<\/noscript>/si', $html, $nsm)) {
        foreach ($nsm[1] as $ns) {
            $t = cleanToText($ns);
            if (strlen($t) > 200) { $noscriptContent .= $t . "\n"; }
        }
    }

    $hasContent = strlen($text) >= 250 || $structuredData || $finePrint || $serverJsonData || count($microdata) >= 3;
    if (!$hasContent) return null;

    // ── Assemble result — structured data first, fine print second, body last ──
    $result = '';
    if (!empty($meta['title']))       $result .= "TÍTULO: {$meta['title']}\n";
    if (!empty($meta['description'])) $result .= "DESCRIPCIÓN OG: {$meta['description']}\n";
    // Extra meta (price, etc.)
    foreach (['price.amount','itemprop:price'] as $mk) {
        if (!empty($meta[$mk])) $result .= "META PRECIO: {$meta[$mk]}\n";
    }
    $result .= "\n";

    if ($structuredData)  $result .= "DATOS ESTRUCTURADOS (JSON-LD):\n{$structuredData}\n\n";

    if ($serverJsonData)  $result .= "=== DATOS DEL SERVIDOR (Next.js/app state) ===\n{$serverJsonData}\n=== FIN DATOS SERVIDOR ===\n\n";

    if ($microdata) {
        $mdLines = [];
        foreach ($microdata as $prop => $val) $mdLines[] = "$prop: $val";
        $result .= "MICRODATA (itemprop): " . implode(' | ', array_slice($mdLines, 0, 25)) . "\n\n";
    }

    if ($finePrint)       $result .= "=== LETRA PEQUEÑA / CONDICIONES LEGALES (PRIORIDAD MÁXIMA) ===\n" . trim($finePrint) . "\n=== FIN LETRA PEQUEÑA ===\n\n";

    $result .= "TEXTO PRINCIPAL DEL ANUNCIO:\n" . mb_substr($text, 0, 3800);

    if ($noscriptContent) $result .= "\n\nCONTENIDO NOSCRIPT:\n" . mb_substr($noscriptContent, 0, 600);

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
$vinRaw         = strtoupper(trim($input['vin'] ?? ''));

// ── VIN decode (if provided) ──
$vinDecode = null;
if ($vinRaw !== '') {
    $vinDecode = decodeVin($vinRaw);
}

// ── URL detection & fetching ──
$isUrl        = (bool) preg_match('/^https?:\/\//i', $rawOfferText);
$urlSource    = null;   // original URL (shown in prompt context)
$aiShouldFetchUrl = false; // Flag to tell AI to fetch the URL itself

if ($isUrl) {
    // Validate URL to prevent SSRF attacks
    if (!_isUrlSafe($rawOfferText)) {
        http_response_code(400);
        echo json_encode(['error' => 'URL no permitida por razones de seguridad.']);
        exit;
    }
    
    $fetched = fetchCarListing($rawOfferText);
    if ($fetched && strlen($fetched) > 250) {
        $urlSource = $rawOfferText;
        $offerText = $fetched;
    } else {
        // Fetch failed - let AI handle the URL directly instead of asking user to copy-paste
        $urlSource = $rawOfferText;
        $aiShouldFetchUrl = true;
    }
}

if (!$offerText && !$fileBase64A && !$aiShouldFetchUrl && (!$cuota || !$meses)) {
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
$cacheDir = __DIR__ . '/../../data/cache_coches';
if (!is_dir($cacheDir)) @mkdir($cacheDir, 0755, true);
$cacheKey  = md5($rawOfferText . '|' . $cuota . '|' . $meses . '|' . $entrada . '|' . $valorResidual . '|' . $precioContado . '|' . ($fileBase64A ? md5($fileBase64A) : '') . '|' . ($compareMode ? '1' : '0') . '|' . $offerTextB . '|' . ($fileBase64B ? md5($fileBase64B) : '') . '|' . $vinRaw);
$cacheFile = $cacheDir . '/' . $cacheKey . '.json';
$cacheTtl  = 5 * 60; // 5 min — dedup only, responses vary by design

if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTtl) {
    $fp = @fopen($cacheFile, 'r');
    if ($fp) {
        flock($fp, LOCK_SH);
        $cached = stream_get_contents($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
        if ($cached) { echo $cached; exit; }
    }
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
    if ($aiShouldFetchUrl) {
        // AI will fetch the URL directly - include clear instructions
        $offerSection = "ENLACE A ANALIZAR: {$urlSource}\n\nPor favor, visita este enlace y extrae toda la información relevante del anuncio de coche (marca, modelo, año, kilometraje, precio, equipamiento, estado del vehículo, etc.). Analiza si el precio es razonable comparando con el mercado actual.\n";
    } else {
        $offerSection = "CONTENIDO EXTRAÍDO DEL ANUNCIO (fuente: {$urlSource}):\n\"\"\"\n{$offerText}\n\"\"\"\n";
    }
} elseif ($offerText) {
    $offerSection = "TEXTO DEL ANUNCIO / OFERTA:\n\"\"\"\n{$offerText}\n\"\"\"\n";
} else {
    $offerSection = '';
}
if ($precioContado || $entrada || $cuota || $meses || $valorResidual || $taeAnunciada) {
    $datosSection = "DATOS INTRODUCIDOS POR EL USUARIO:\n";
    if ($precioContado)  $datosSection .= "- Precio al contado: {$precioContado}€\n";
    if ($entrada)        $datosSection .= "- Entrada inicial: {$entrada}€\n";
    if ($cuota)          $datosSection .= "- Cuota mensual: {$cuota}€\n";
    if ($meses)          $datosSection .= "- Número de meses: {$meses}\n";
    if ($valorResidual)  $datosSection .= "- Pago final / VFG: {$valorResidual}€\n";
    if ($taeAnunciada)   $datosSection .= "- TAE anunciada por el concesionario: {$taeAnunciada}%\n";
}

// ── VIN context for prompt ──
$vinSection = '';
if ($vinDecode && $vinDecode['valid']) {
    $v = $vinDecode;
    $mfrLabel = $v['manufacturer'] ? $v['manufacturer'] : 'Fabricante desconocido (WMI: ' . $v['wmi'] . ')';
    $vinSection = "NÚMERO DE BASTIDOR (VIN) PROPORCIONADO POR EL USUARIO:\n"
        . "- VIN: {$v['vin']}\n"
        . "- País de fabricación: {$v['country']}\n"
        . "- Fabricante (según WMI): {$mfrLabel}\n"
        . "- Año de modelo (posición 10, cód. '{$v['vin'][9]}'): {$v['model_year']}\n"
        . "- Código planta: {$v['plant_code']}\n"
        . "- Dígito de control: " . ($v['check_ok'] ? 'VÁLIDO' : 'INVÁLIDO — posible VIN manipulado o error de transcripción') . "\n"
        . "INSTRUCCIÓN: Cruza estos datos con el anuncio. Si el fabricante, país o año no coincide con lo anunciado, "
        . "indícalo en las trampas o alertas_fraude. Un dígito de control inválido en segunda mano es señal de alerta.\n";
} elseif ($vinDecode && !$vinDecode['valid']) {
    $vinSection = "VIN PROPORCIONADO INVÁLIDO: {$vinDecode['error']}\n";
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
{$vinSection}
{$preCalc}

EXTRACCIÓN DEL PRECIO AL CONTADO — reglas estrictas:
- El campo "precio_contado" debe ser el PRECIO TOTAL DEL VEHÍCULO sin financiar, no la cuota mensual
- PRIORIDAD MÁXIMA: Si el anuncio muestra EXPLÍCITAMENTE una etiqueta "Precio al contado: X€" o "PVP: X€", usa ESE valor SIEMPRE, sin excepción, aunque ese mismo número aparezca tachado en otra parte del texto
- PATRÓN TACHADO (muy común en Flexicar, Coches.net, AutoScout24): el HTML elimina el tachado visual al convertirse a texto plano, por lo que el mismo precio puede aparecer varias veces. La etiqueta "Precio al contado: X€" identifica sin ambigüedad cuál es el precio sin financiar
- Ejemplos Flexicar:
  · "Desde 16.490€ [tachado] → 13.990€ | 218€/mes | Precio al contado: 16.490€" → precio_contado = 16490 (la etiqueta gana; 13.990€ es el precio con financiación de Flexicar, NO el contado)
  · "21.990€ en grande + 343€/mes + Precio al contado: 25.990€" → precio_contado = 25990 (la etiqueta gana)
- En anuncios de segunda mano sin financiación: el precio pedido único es el precio_contado
- En ofertas de concesionario: busca "PVP", "precio al contado", "precio sin financiar", "precio de venta", "precio sin oferta"
- El precio "Desde X€" NUNCA es el precio_contado (es el precio de enganche o precio con condiciones)
- El precio mensual NUNCA es el precio_contado
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

SIMULADORES DE FINANCIACIÓN CON SLIDERS — patrón frecuente en concesionarios online:
Muchos anuncios muestran un calculador interactivo con este formato de texto (después de eliminar el HTML):

  [Nombre campo]
  [Valor actual]
  [Valor mínimo][Valor máximo]

Ejemplo real:
  "Entrada inicial / 0 € / 0 €13.490 €"   → precio_contado = 13.490€, entrada = 0€
  "Periodo / 120 meses / 12 meses120 meses" → meses = 120
  "Fináncialo por / 218 € al mes a 120 meses*" → cuota = 218, meses = 120
  "* Financiación estimada no vinculante"   → TRAMPA OBLIGATORIA (ver abajo)
  "Te ahorras 2.500 €"                      → precio original era mayor, verifica vs PVP real

REGLAS PARA SIMULADORES:
- El valor máximo de la "Entrada inicial" suele ser el precio al contado del vehículo → úsalo como precio_contado
- El valor mostrado en el slider es la configuración actual del usuario (no el mínimo)
- "Financiación estimada no vinculante" / "estimación orientativa" / "sin compromiso" → SIEMPRE incluir en trampa: la cuota real puede ser mayor al solicitar financiación formal
- "Te ahorras X€" junto a un precio tachado → el precio real sin descuento era más alto; verifica si el "ahorro" condiciona la financiación
- Sin TAE ni TIN explícitos en el simulador → ponlo en trampa: no se puede comparar sin estos datos
- Si el simulador no indica la entidad financiera → red flag: pueden ser condiciones más duras que la banca

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
    "vs_contado": {
      "diferencia_eur": [sobrecoste en € respecto al pago al contado (positivo=más caro, negativo=más barato). null si no aplica],
      "descripcion": "[1-2 frases con cifras exactas. Financiación: 'Pagas X€ más que al contado, equivale a Y meses de cuota extra'. Segunda mano: 'Este modelo/año/km vale entre X€ y Y€ en el mercado, te piden Z€']",
      "veredicto": "[exactamente uno de: CARO, NORMAL, BARATO, POSITIVO]"
    },
    "vs_banco": {
      "diferencia_eur": [diferencia en € respecto a préstamo bancario típico al 9% TAE (positivo=esta oferta es más cara, negativo=esta oferta es más barata). null si no aplica],
      "descripcion": "[1-2 frases con cifras. Financiación: 'Con préstamo bancario al 9% TAE pagarías X€ menos en intereses'. Segunda mano: 'Existen alternativas similares en el mercado por X-Y€ menos']",
      "veredicto": "[exactamente uno de: CARO, NORMAL, BARATO, POSITIVO]"
    },
    "recomendacion": "[2-3 frases directas. Qué haría una persona con criterio que NO tiene interés en venderte nada. Menciona si el modelo tiene problemas conocidos. Sin suavizar]"
  },
  "preguntas_clave": [
    {
      "pregunta": "[pregunta directa y específica que el usuario DEBE hacer antes de firmar o comprar]",
      "por_que": "[en 1 frase: por qué esta pregunta protege al comprador en ESTE caso concreto]",
      "respuesta_buena": "[qué respuesta concreta quiere escuchar — con cifras o condiciones reales si aplica]",
      "respuesta_mala": "[señal de alarma: qué respuesta o comportamiento del vendedor indica peligro]"
    }
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
- "preguntas_clave": Exactamente 3 objetos. Cada uno con "pregunta", "por_que", "respuesta_buena" y "respuesta_mala". Usa cifras reales (€, %, plazos) cuando las tengas. "respuesta_mala" debe describir comportamientos concretos del vendedor que son señal de alarma, no frases genéricas
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

// Add VIN decode to response (not cached separately, included in analysis)
if ($vinDecode) $analysis['vin_decode'] = $vinDecode;

$finalJson = json_encode($analysis, JSON_UNESCAPED_UNICODE);

@file_put_contents($cacheFile, $finalJson, LOCK_EX);
_pruneCache($cacheDir, $cacheTtl);

echo $finalJson;
