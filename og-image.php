<?php
/**
 * og-image.php — Genera la imagen Open Graph para SinFiltros (1200×630)
 * Se sirve como PNG via GD. Enlázalo en las meta og:image como /og-image.php
 * o genera el PNG estático una vez con: curl http://localhost:8888/proyecto-1/og-image.php > og-image.png
 */

header('Content-Type: image/png');
header('Cache-Control: public, max-age=604800'); // 7 días

$w = 1200;
$h = 630;

$im = imagecreatetruecolor($w, $h);

// ── Colores ──
$navy   = imagecolorallocate($im, 8, 12, 20);
$purple = imagecolorallocate($im, 139, 92, 246);
$orange = imagecolorallocate($im, 249, 115, 22);
$white  = imagecolorallocate($im, 226, 232, 240);
$muted  = imagecolorallocate($im, 100, 116, 139);
$red    = imagecolorallocate($im, 239, 68, 68);
$glow1  = imagecolorallocate($im, 30, 20, 50);
$glow2  = imagecolorallocate($im, 25, 15, 5);

// ── Fondo degradado (navy) ──
imagefill($im, 0, 0, $navy);

// Orb superior derecha (púrpura)
for ($r = 300; $r > 0; $r -= 2) {
    $alpha = (int)(110 - ($r / 300) * 110);
    $c = imagecolorallocatealpha($im, 139, 92, 246, $alpha);
    imagefilledellipse($im, 1050, 80, $r * 2, $r * 2, $c);
}

// Orb inferior izquierda (naranja)
for ($r = 220; $r > 0; $r -= 2) {
    $alpha = (int)(115 - ($r / 220) * 115);
    $c = imagecolorallocatealpha($im, 249, 115, 22, $alpha);
    imagefilledellipse($im, 80, 550, $r * 2, $r * 2, $c);
}

// ── Logo badge ──
$badge_x = 80;
$badge_y = 80;
$badge_w = 72;
$badge_h = 72;
// Fondo del badge con gradiente manual
for ($i = 0; $i < $badge_w; $i++) {
    $ratio = $i / $badge_w;
    $r = (int)(139 + (249 - 139) * $ratio);
    $g = (int)(92 + (115 - 92) * $ratio);
    $b = (int)(246 + (22 - 246) * $ratio);
    $c = imagecolorallocate($im, $r, $g, $b);
    imageline($im, $badge_x + $i, $badge_y, $badge_x + $i, $badge_y + $badge_h, $c);
}
// Borde redondeado aproximado (rectángulo)
imagesetthickness($im, 1);

// Texto "SF" en el badge
$font = 5; // fuente GD built-in
imagestring($im, $font, $badge_x + 22, $badge_y + 26, 'SF', $white);

// ── Nombre de la marca ──
// Texto "Sin" en blanco + "Filtros" en gradiente (aproximado con naranja)
imagestring($im, 5, $badge_x + $badge_w + 16, $badge_y + 20, 'Sin', $white);
imagestring($im, 5, $badge_x + $badge_w + 52, $badge_y + 20, 'Filtros', $orange);

// ── Tagline pequeña ──
imagestring($im, 2, $badge_x + $badge_w + 16, $badge_y + 44, 'La IA que te lo dice sin filtros', $muted);

// ── Separador horizontal ──
imageline($im, 80, 200, $w - 80, 200, imagecolorallocate($im, 30, 35, 50));

// ── Titular principal ──
// GD built-in fonts son limitados; usamos strings simples
$title_y = 230;
// Línea 1
imagestring($im, 5, 80, $title_y,      'Las empresas te ocultan', $white);
// Línea 2 en naranja/accent
imagestring($im, 5, 80, $title_y + 28, 'miles de euros', $orange);
imagestring($im, 5, 80, $title_y + 56, 'en la letra pequena', $white);

// ── Subtítulo ──
imagestring($im, 3, 80, $title_y + 100, 'Analizamos coches, hipotecas, luz, movil, seguros e inversiones.', $muted);
imagestring($im, 3, 80, $title_y + 120, 'Gratis. Sin registro. Resultado en menos de 15 segundos.', $muted);

// ── Chips de herramientas ──
$chips = ['Coches', 'Hipotecas', 'Luz & Gas', 'Telefonia', 'Seguros', 'Inversiones'];
$chip_x = 80;
$chip_y = 430;
$chip_h = 36;
$chip_pad = 14;
$font_w = 9; // aprox ancho por caracter font-5

foreach ($chips as $chip) {
    $chip_w = strlen($chip) * $font_w + $chip_pad * 2;
    // Fondo del chip
    $chip_bg = imagecolorallocate($im, 20, 25, 40);
    imagefilledrectangle($im, $chip_x, $chip_y, $chip_x + $chip_w, $chip_y + $chip_h, $chip_bg);
    // Borde
    imagerectangle($im, $chip_x, $chip_y, $chip_x + $chip_w, $chip_y + $chip_h,
        imagecolorallocate($im, 50, 60, 90));
    // Texto
    imagestring($im, 3, $chip_x + $chip_pad, $chip_y + 11, $chip, $white);
    $chip_x += $chip_w + 12;
    if ($chip_x > $w - 200) { $chip_x = 80; $chip_y += $chip_h + 10; }
}

// ── CTA badge derecha ──
$cta_x = $w - 280;
$cta_y = 240;
$cta_w = 200;
$cta_h = 110;
$cta_bg = imagecolorallocate($im, 15, 20, 35);
imagefilledrectangle($im, $cta_x, $cta_y, $cta_x + $cta_w, $cta_y + $cta_h, $cta_bg);
imagerectangle($im, $cta_x, $cta_y, $cta_x + $cta_w, $cta_y + $cta_h,
    imagecolorallocate($im, 139, 92, 246));
imagestring($im, 3, $cta_x + 20, $cta_y + 18, '100% GRATIS', $purple);
imagestring($im, 3, $cta_x + 20, $cta_y + 40, 'SIN REGISTRO', $muted);
imagestring($im, 4, $cta_x + 20, $cta_y + 65, '<15 segundos', $orange);
imagestring($im, 2, $cta_x + 20, $cta_y + 90, 'Powered by Claude AI', $muted);

// ── URL ──
imagestring($im, 2, 80, $h - 40, 'sinfiltros.es', $muted);

imagepng($im);
imagedestroy($im);
