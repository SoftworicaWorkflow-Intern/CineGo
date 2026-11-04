<?php

/**
 * Appends missing translation keys to lang/en/ui.php using the English
 * fallback text written next to each ui() call.
 *
 * Run: php scripts/merge-lang.php
 */
$root = dirname(__DIR__);
$enFile = $root.'/lang/en/ui.php';

$targets = [$root.'/resources/views', $root.'/app', $root.'/routes', $root.'/database/seeders'];

/** @var array<string, string> $withFallback */
$withFallback = [];
/** @var list<string> $withoutFallback */
$withoutFallback = [];
/** @var list<string> $concatenated */
$concatenated = [];

$iterator = function (string $dir) use (&$iterator, &$withFallback, &$withoutFallback, &$concatenated) {
    foreach (scandir($dir) as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }

        $path = $dir.'/'.$entry;

        if (is_dir($path)) {
            $iterator($path);

            continue;
        }

        if (! preg_match('/\.php$/', $entry)) {
            continue;
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            continue;
        }

        // ui('key', 'Fallback')
        if (preg_match_all("/ui\\(\\s*'([^']+)'\\s*,\\s*'((?:[^'\\\\]|\\\\.)*)'\\s*(.)/", $contents, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                if ($match[3] === '.') {
                    // the fallback is built by concatenation — needs a human
                    if (! in_array($match[1], $concatenated, true)) {
                        $concatenated[] = $match[1];
                    }

                    continue;
                }

                if ($match[3] !== ')') {
                    continue;
                }

                $withFallback[$match[1]] = stripcslashes($match[2]);
            }
        }

        // ui('key') — no fallback provided
        if (preg_match_all("/ui\\(\\s*'([^']+)'\\s*\\)/", $contents, $matches)) {
            foreach ($matches[1] as $key) {
                $withoutFallback[$key] = $key;
            }
        }
    }
};

foreach ($targets as $target) {
    if (is_dir($target)) {
        $iterator($target);
    }
}

$en = require $enFile;
$missing = array_diff_key($withFallback, $en);
ksort($missing);

if ($missing === []) {
    echo "lang/en/ui.php is already complete.\n";
} else {
    $source = file_get_contents($enFile);
    $lines = [];

    foreach ($missing as $key => $fallback) {
        $lines[] = '    '.var_export($key, true).' => '.var_export($fallback, true).',';
    }

    $block = "\n    // Added automatically from ui() fallbacks\n".implode("\n", $lines)."\n];";

    $trimmed = rtrim($source);
    if (! str_ends_with($trimmed, '];')) {
        fwrite(STDERR, "Unexpected file ending — aborting.\n");
        exit(1);
    }

    $source = substr($trimmed, 0, -2).$block."\n";
    file_put_contents($enFile, $source);

    echo 'Added '.count($missing)." keys to lang/en/ui.php\n";
}

$noFallback = array_diff(array_keys($withoutFallback), array_keys($en), array_keys($missing));

if ($noFallback) {
    echo "\nKeys used without a fallback (add manually):\n";
    echo implode(', ', $noFallback)."\n";
}

if ($concatenated) {
    $stillNeeded = array_diff($concatenated, array_keys($en), array_keys($missing));

    if ($stillNeeded) {
        echo "\nKeys with concatenated fallbacks (add manually):\n";
        echo implode(', ', $stillNeeded)."\n";
    }
}
