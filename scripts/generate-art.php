<?php

/**
 * Generates the original CineGo artwork (posters, banners, app icons).
 *
 * Run: php scripts/generate-art.php
 *
 * Every file is written from scratch — no third-party artwork is used.
 */
$root = dirname(__DIR__);
$posterDir = $root.'/public/img/posters';
$bannerDir = $root.'/public/img/banners';
$iconDir = $root.'/public/img/icons';

foreach ([$posterDir, $bannerDir, $iconDir] as $dir) {
    if (! is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

$display = "Impact, 'Arial Black', 'Helvetica Neue', sans-serif";
$body = "'Segoe UI', 'Helvetica Neue', Arial, sans-serif";

/*
 |--------------------------------------------------------------------------
 | Posters (400 × 600)
 |--------------------------------------------------------------------------
 */
$posters = [
    [
        'file' => 'poster-1.svg',
        'title' => 'KATHMANDU NIGHTS',
        'tag' => 'NEP · THRILLER',
        'from' => '#12b8c9',
        'to' => '#6d28d9',
        'motif' => 'skyline',
    ],
    [
        'file' => 'poster-2.svg',
        'title' => 'HIMALAYAN ECHO',
        'tag' => 'NEP · DRAMA',
        'from' => '#f59e0b',
        'to' => '#1d4ed8',
        'motif' => 'mountains',
    ],
    [
        'file' => 'poster-3.svg',
        'title' => 'THE LAST YATRA',
        'tag' => 'NEP · ADVENTURE',
        'from' => '#ea580c',
        'to' => '#7f1d1d',
        'motif' => 'road',
    ],
    [
        'file' => 'poster-4.svg',
        'title' => 'PHIR BHI DIL HAI',
        'tag' => 'HIN · ROMANCE',
        'from' => '#ec4899',
        'to' => '#7e22ce',
        'motif' => 'rain',
    ],
    [
        'file' => 'poster-5.svg',
        'title' => 'AANKHA PHULEY',
        'tag' => 'NEP · COMEDY',
        'from' => '#facc15',
        'to' => '#0ea5e9',
        'motif' => 'sunburst',
    ],
    [
        'file' => 'poster-6.svg',
        'title' => 'BHUT KATHA',
        'tag' => 'NEP · HORROR',
        'from' => '#22c55e',
        'to' => '#052e16',
        'motif' => 'tree',
    ],
    [
        'file' => 'poster-7.svg',
        'title' => 'SHIKHAR',
        'tag' => 'NEP · ACTION',
        'from' => '#ef4444',
        'to' => '#111827',
        'motif' => 'climber',
    ],
    [
        'file' => 'poster-8.svg',
        'title' => 'MAYA DJANGO',
        'tag' => 'HIN · ACTION',
        'from' => '#f43f5e',
        'to' => '#1e293b',
        'motif' => 'star',
    ],
];

foreach ($posters as $poster) {
    file_put_contents($posterDir.'/'.$poster['file'], posterSvg($poster, $display, $body));
}

/*
|--------------------------------------------------------------------------
| Banners (1600 × 600)
|--------------------------------------------------------------------------
*/
$banners = [
    ['file' => 'banner-1.svg', 'title' => 'NOW SHOWING', 'sub' => 'Blockbusters on the big screen', 'from' => '#e50914', 'to' => '#7f1d1d', 'motif' => 'rays'],
    ['file' => 'banner-2.svg', 'title' => 'WEEKEND DEALS', 'sub' => 'Buy 1 Get 1 on morning shows', 'from' => '#f59e0b', 'to' => '#7c2d12', 'motif' => 'dots'],
    ['file' => 'banner-3.svg', 'title' => 'COMING SOON', 'sub' => 'Book before the rush', 'from' => '#2563eb', 'to' => '#312e81', 'motif' => 'grid'],
    ['file' => 'banner-4.svg', 'title' => 'PREMIUM RECLINERS', 'sub' => 'Luxury comfort, cinema prices', 'from' => '#7c3aed', 'to' => '#3b0764', 'motif' => 'arcs'],
    ['file' => 'banner-5.svg', 'title' => 'CITY LIGHTS', 'sub' => 'Kathmandu · Pokhara · Lalitpur', 'from' => '#0d9488', 'to' => '#083344', 'motif' => 'skyline'],
    ['file' => 'banner-6.svg', 'title' => 'MOVIE NIGHTS', 'sub' => 'Every night, every screen', 'from' => '#db2777', 'to' => '#4a044e', 'motif' => 'rays'],
];

foreach ($banners as $banner) {
    file_put_contents($bannerDir.'/'.$banner['file'], bannerSvg($banner, $display, $body));
}

/*
|--------------------------------------------------------------------------
| App icons (512 × 512, used by the PWA manifest and the favicon)
|--------------------------------------------------------------------------
*/
file_put_contents($iconDir.'/icon.svg', iconSvg(false, $display));
file_put_contents($iconDir.'/icon-maskable.svg', iconSvg(true, $display));

echo "Artwork generated:\n";
echo '  '.count($posters)." posters\n";
echo '  '.count($banners)." banners\n";
echo "  2 icons\n";

/**
 * @param  array<string, string>  $data
 */
function posterSvg(array $data, string $display, string $body): string
{
    $motif = match ($data['motif']) {
        'skyline' => skylineMotif(),
        'mountains' => mountainsMotif(),
        'road' => roadMotif(),
        'rain' => rainMotif(),
        'sunburst' => sunburstMotif(),
        'tree' => treeMotif(),
        'climber' => climberMotif(),
        default => starMotif(),
    };

    $titleLines = explode(' ', $data['title']);
    $lines = [];
    $current = '';
    foreach ($titleLines as $word) {
        if (strlen($current.' '.$word) > 12 && $current !== '') {
            $lines[] = $current;
            $current = $word;
        } else {
            $current = $current === '' ? $word : $current.' '.$word;
        }
    }
    $lines[] = $current;

    $titleSvg = '';
    foreach ($lines as $index => $line) {
        $titleSvg .= sprintf(
            '<text x="200" y="%d" text-anchor="middle" font-family="%s" font-size="%d" fill="#ffffff" letter-spacing="2">%s</text>',
            470 + ($index * 52),
            htmlspecialchars($display, ENT_XML1),
            count($lines) > 1 ? 44 : 52,
            htmlspecialchars($line, ENT_XML1)
        );
    }

    return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 600" width="400" height="600" role="img" aria-label="{$data['title']}">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="{$data['from']}"/>
      <stop offset="100%" stop-color="{$data['to']}"/>
    </linearGradient>
    <linearGradient id="shade" x1="0" y1="0" x2="0" y2="1">
      <stop offset="45%" stop-color="#000000" stop-opacity="0"/>
      <stop offset="100%" stop-color="#000000" stop-opacity="0.92"/>
    </linearGradient>
    <radialGradient id="glow" cx="50%" cy="35%" r="65%">
      <stop offset="0%" stop-color="#ffffff" stop-opacity="0.35"/>
      <stop offset="100%" stop-color="#ffffff" stop-opacity="0"/>
    </radialGradient>
  </defs>

  <rect width="400" height="600" fill="#090909"/>
  <rect width="400" height="600" fill="url(#bg)"/>
  <rect width="400" height="600" fill="url(#glow)"/>
  {$motif}
  <rect width="400" height="600" fill="url(#shade)"/>

  <g font-family="{$body}">
    <text x="200" y="42" text-anchor="middle" font-size="13" fill="#ffffff" fill-opacity="0.85" letter-spacing="6">CINEGO</text>
    {$titleSvg}
    <text x="200" y="566" text-anchor="middle" font-size="12" fill="#ffffff" fill-opacity="0.7" letter-spacing="4">{$data['tag']}</text>
  </g>

  <rect x="0" y="0" width="400" height="600" fill="none" stroke="#ffffff" stroke-opacity="0.14" stroke-width="2"/>
</svg>
SVG;
}

function skylineMotif(): string
{
    $buildings = '';
    $x = 0;
    $heights = [220, 300, 250, 340, 210, 290, 260, 330, 230, 310, 270];
    foreach ($heights as $index => $height) {
        $width = 40;
        $buildings .= sprintf(
            '<rect x="%d" y="%d" width="%d" height="%d" fill="#05070d" fill-opacity="0.85"/>',
            $x,
            400 - $height,
            $width,
            $height
        );
        for ($row = 0; $row < intdiv($height, 46); $row++) {
            for ($col = 0; $col < 2; $col++) {
                if (($row + $col + $index) % 3 === 0) {
                    continue;
                }
                $buildings .= sprintf(
                    '<rect x="%d" y="%d" width="8" height="12" fill="#fde68a" fill-opacity="0.75"/>',
                    $x + 8 + ($col * 18),
                    400 - $height + 14 + ($row * 46),
                );
            }
        }
        $x += 40;
    }

    return <<<SVG
<circle cx="300" cy="140" r="58" fill="#ffffff" fill-opacity="0.85"/>
<circle cx="278" cy="126" r="58" fill="#0b1220" fill-opacity="0.55"/>
{$buildings}
SVG;
}

function mountainsMotif(): string
{
    return <<<'SVG'
<polygon points="0,400 120,220 210,330 300,180 400,340 400,430 0,430" fill="#0b1220" fill-opacity="0.9"/>
<polygon points="120,220 160,282 80,282" fill="#ffffff" fill-opacity="0.9"/>
<polygon points="300,180 344,250 256,250" fill="#ffffff" fill-opacity="0.9"/>
<circle cx="96" cy="128" r="46" fill="#ffffff" fill-opacity="0.9"/>
<g stroke="#ffffff" stroke-opacity="0.35" stroke-width="2">
  <line x1="0" y1="452" x2="400" y2="452"/>
  <line x1="0" y1="472" x2="400" y2="472"/>
</g>
SVG;
}

function roadMotif(): string
{
    return <<<'SVG'
<circle cx="200" cy="250" r="120" fill="#ffffff" fill-opacity="0.14"/>
<polygon points="150,600 250,600 232,330 168,330" fill="#0b0b0d" fill-opacity="0.9"/>
<g fill="#fde68a">
  <rect x="194" y="580" width="12" height="34"/>
  <rect x="195" y="520" width="10" height="26"/>
  <rect x="196" y="466" width="8" height="22"/>
  <rect x="197" y="420" width="7" height="18"/>
  <rect x="198" y="382" width="6" height="14"/>
</g>
<polygon points="0,600 140,360 140,600" fill="#111827" fill-opacity="0.75"/>
<polygon points="400,600 260,360 260,600" fill="#111827" fill-opacity="0.75"/>
SVG;
}

function rainMotif(): string
{
    $drops = '';
    for ($i = 0; $i < 46; $i++) {
        $x = ($i * 37) % 400;
        $y = ($i * 71) % 360;
        $drops .= sprintf('<line x1="%d" y1="%d" x2="%d" y2="%d" stroke="#ffffff" stroke-opacity="0.28" stroke-width="2"/>', $x, $y, $x - 10, $y + 26);
    }

    return <<<SVG
{$drops}
<path d="M200 330c-46-32-74-62-74-96a40 40 0 0 1 74-20 40 40 0 0 1 74 20c0 34-28 64-74 96Z" fill="#ffffff" fill-opacity="0.9"/>
SVG;
}

function sunburstMotif(): string
{
    $rays = '';
    for ($i = 0; $i < 18; $i++) {
        $angle = $i * 20;
        $rays .= sprintf('<polygon points="200,240 194,60 206,60" fill="#ffffff" fill-opacity="0.18" transform="rotate(%d 200 240)"/>', $angle);
    }

    return <<<SVG
{$rays}
<circle cx="200" cy="240" r="86" fill="#ffffff" fill-opacity="0.92"/>
<circle cx="172" cy="222" r="10" fill="#0b1220"/>
<circle cx="230" cy="222" r="10" fill="#0b1220"/>
<path d="M164 268q36 34 74 0" stroke="#0b1220" stroke-width="9" fill="none" stroke-linecap="round"/>
SVG;
}

function treeMotif(): string
{
    return <<<'SVG'
<circle cx="200" cy="250" r="130" fill="#ecfccb" fill-opacity="0.16"/>
<path d="M200 470V300m0 0-46-58m46 58 52-64m-52 64-70-34m70 34 74-30" stroke="#052e16" stroke-width="14" fill="none" stroke-linecap="round"/>
<path d="M170 470h60l14 40h-88Z" fill="#052e16" fill-opacity="0.9"/>
<g fill="#ecfccb" fill-opacity="0.5">
  <circle cx="120" cy="150" r="6"/>
  <circle cx="300" cy="120" r="5"/>
  <circle cx="330" cy="230" r="7"/>
  <circle cx="86" cy="250" r="4"/>
</g>
SVG;
}

function climberMotif(): string
{
    return <<<'SVG'
<polygon points="0,430 140,180 240,320 320,140 400,300 400,470 0,470" fill="#0b1220" fill-opacity="0.88"/>
<polygon points="320,140 356,196 284,196" fill="#ffffff" fill-opacity="0.92"/>
<g stroke="#ffffff" stroke-opacity="0.55" stroke-width="6" fill="none" stroke-linecap="round">
  <circle cx="150" cy="300" r="12" fill="#ffffff" stroke="none"/>
  <path d="M150 316v42m0-30-30 16m30-16 30 12m-30 18-24 34m24-34 26 32"/>
</g>
SVG;
}

function starMotif(): string
{
    $points = '';
    for ($i = 0; $i < 10; $i++) {
        $outer = $i % 2 === 0;
        $angle = (-90 + $i * 36) * M_PI / 180;
        $radius = $outer ? 130 : 58;
        $points .= sprintf('%0.1f,%0.1f ', 200 + cos($angle) * $radius, 250 + sin($angle) * $radius);
    }

    return <<<SVG
<polygon points="{$points}" fill="#ffffff" fill-opacity="0.9"/>
<circle cx="200" cy="250" r="42" fill="#111827"/>
<circle cx="200" cy="250" r="18" fill="#ffffff" fill-opacity="0.9"/>
SVG;
}

/**
 * @param  array<string, string>  $data
 */
function bannerSvg(array $data, string $display, string $body): string
{
    $motif = match ($data['motif']) {
        'rays' => bannerRays(),
        'dots' => bannerDots(),
        'grid' => bannerGrid(),
        'arcs' => bannerArcs(),
        default => bannerSkyline(),
    };

    return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1600 600" width="1600" height="600" role="img" aria-label="{$data['title']}">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="{$data['from']}"/>
      <stop offset="100%" stop-color="{$data['to']}"/>
    </linearGradient>
    <linearGradient id="fade" x1="0" y1="0" x2="1" y2="0">
      <stop offset="0%" stop-color="#000000" stop-opacity="0.72"/>
      <stop offset="65%" stop-color="#000000" stop-opacity="0.15"/>
      <stop offset="100%" stop-color="#000000" stop-opacity="0"/>
    </linearGradient>
  </defs>

  <rect width="1600" height="600" fill="#090909"/>
  <rect width="1600" height="600" fill="url(#bg)"/>
  {$motif}
  <rect width="1600" height="600" fill="url(#fade)"/>

  <g font-family="{$body}" fill="#ffffff">
    <text x="90" y="252" font-size="26" letter-spacing="12" fill-opacity="0.85">CINEGO</text>
    <text x="86" y="330" font-family="{$display}" font-size="84" letter-spacing="3">{$data['title']}</text>
    <text x="90" y="384" font-size="30" fill-opacity="0.88">{$data['sub']}</text>
    <rect x="90" y="424" width="150" height="8" rx="4" fill="#ffffff" fill-opacity="0.9"/>
  </g>
</svg>
SVG;
}

function bannerRays(): string
{
    $rays = '';
    for ($i = 0; $i < 12; $i++) {
        $rays .= sprintf('<polygon points="1180,300 1580,%d 1580,%d" fill="#ffffff" fill-opacity="0.08" transform="rotate(%d 1180 300)"/>', 100 + $i * 40, 160 + $i * 40, $i * 30);
    }

    return $rays.'<circle cx="1180" cy="300" r="120" fill="#ffffff" fill-opacity="0.18"/>';
}

function bannerDots(): string
{
    $dots = '';
    for ($x = 0; $x < 16; $x++) {
        for ($y = 0; $y < 6; $y++) {
            $dots .= sprintf('<circle cx="%d" cy="%d" r="%d" fill="#ffffff" fill-opacity="0.12"/>', 940 + $x * 44, 90 + $y * 84, ($x + $y) % 3 === 0 ? 12 : 6);
        }
    }

    return $dots;
}

function bannerGrid(): string
{
    $lines = '';
    for ($x = 0; $x <= 16; $x++) {
        $lines .= sprintf('<line x1="%d" y1="0" x2="%d" y2="600" stroke="#ffffff" stroke-opacity="0.1" stroke-width="2"/>', 900 + $x * 46, 900 + $x * 46);
    }
    for ($y = 0; $y <= 7; $y++) {
        $lines .= sprintf('<line x1="900" y1="%d" x2="1600" y2="%d" stroke="#ffffff" stroke-opacity="0.1" stroke-width="2"/>', $y * 92, $y * 92);
    }

    return $lines.'<circle cx="1290" cy="300" r="110" fill="#ffffff" fill-opacity="0.16"/>';
}

function bannerArcs(): string
{
    $arcs = '';
    for ($i = 1; $i <= 6; $i++) {
        $arcs .= sprintf('<circle cx="1250" cy="320" r="%d" fill="none" stroke="#ffffff" stroke-opacity="%0.2f" stroke-width="14"/>', $i * 78, 0.30 - $i * 0.035);
    }

    return $arcs.'<rect x="1170" y="240" width="160" height="160" rx="24" fill="#ffffff" fill-opacity="0.22"/>';
}

function bannerSkyline(): string
{
    $buildings = '';
    $x = 880;
    $heights = [180, 260, 210, 300, 170, 250, 220, 290, 190];
    foreach ($heights as $height) {
        $buildings .= sprintf('<rect x="%d" y="%d" width="82" height="%d" fill="#05070d" fill-opacity="0.7"/>', $x, 440 - $height, $height);
        $x += 82;
    }

    return $buildings.'<circle cx="1440" cy="130" r="64" fill="#ffffff" fill-opacity="0.85"/>';
}

function iconSvg(bool $maskable, string $display): string
{
    $rx = $maskable ? 0 : 96;
    $innerRx = $maskable ? 0 : 84;
    $scale = $maskable ? 0.82 : 1;

    return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="512" height="512" role="img" aria-label="CineGo">
  <rect width="512" height="512" rx="{$rx}" fill="#090909"/>
  <rect x="16" y="16" width="480" height="480" rx="{$innerRx}" fill="#151515"/>
  <g transform="translate(256 256) scale($scale) translate(-256 -256)">
    <text x="256" y="330" text-anchor="middle" font-family="{$display}" font-size="240" font-weight="bold">
      <tspan fill="#E50914">C</tspan><tspan fill="#FFFFFF">G</tspan>
    </text>
    <rect x="150" y="374" width="212" height="26" rx="13" fill="#E50914"/>
    <rect x="196" y="374" width="16" height="26" fill="#151515"/>
    <rect x="300" y="374" width="16" height="26" fill="#151515"/>
  </g>
</svg>
SVG;
}
