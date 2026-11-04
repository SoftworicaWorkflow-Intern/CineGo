<?php

/**
 * Exports missing ui() keys together with their English fallback text,
 * so the language files can be extended in one pass.
 *
 * Run: php scripts/export-missing-lang.php
 */
$root = dirname(__DIR__);

$targets = [$root.'/resources/views', $root.'/app', $root.'/routes', $root.'/database/seeders'];

$used = [];

$iterator = function (string $dir) use (&$iterator, &$used) {
    foreach (scandir($dir) as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }

        $path = $dir.'/'.$entry;

        if (is_dir($path)) {
            $iterator($path);

            continue;
        }

        if (! preg_match('/\.(php)$/', $entry)) {
            continue;
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            continue;
        }

        // ui('key', 'Fallback text')
        if (preg_match_all("/ui\\(\\s*'([^']+)'\\s*,\\s*'((?:[^'\\\\]|\\\\.)*)'/", $contents, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $used[$match[1]] = stripcslashes($match[2]);
            }
        }

        // ui("key", "Fallback text")
        if (preg_match_all('/ui\\(\\s*"([^"]+)"\\s*,\\s*"((?:[^"\\\\]|\\\\.)*)"/', $contents, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $used[$match[1]] = stripcslashes($match[2]);
            }
        }
    }
};

foreach ($targets as $target) {
    if (is_dir($target)) {
        $iterator($target);
    }
}

$en = require $root.'/lang/en/ui.php';
$np = require $root.'/lang/np/ui.php';

$missingEn = array_diff_key($used, $en);
$missingNp = array_diff_key($used, $np);

ksort($missingEn);
ksort($missingNp);

echo '--- EN additions ('.count($missingEn).") ---\n";
foreach ($missingEn as $key => $fallback) {
    echo var_export($key, true).' => '.var_export($fallback, true).",\n";
}

echo "\n--- NP additions (".count($missingNp).") ---\n";
foreach ($missingNp as $key => $fallback) {
    echo var_export($key, true).' => '.var_export($fallback, true).",\n";
}
