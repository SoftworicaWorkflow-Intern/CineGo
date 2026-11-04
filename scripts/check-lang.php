<?php

/**
 * Reports ui() keys used in code that are missing from the language files.
 *
 * Run: php scripts/check-lang.php
 */
$root = dirname(__DIR__);

$used = [];

$iterator = function (string $dir) use (&$iterator, &$used, $root) {
    foreach (scandir($dir) as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }

        $path = $dir.'/'.$entry;

        if (is_dir($path)) {
            if ($entry === 'vendor' || $entry === 'node_modules') {
                continue;
            }
            $iterator($path);

            continue;
        }

        if (! preg_match('/\.(php|blade\.php)$/', $entry)) {
            continue;
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            continue;
        }

        if (! preg_match_all("/ui\\(\\s*'([^']+)'/", $contents, $matches)) {
            continue;
        }

        foreach ($matches[1] as $key) {
            $used[$key][] = str_replace($root, '', $path);
        }
    }
};

$targets = [
    $root.'/resources/views',
    $root.'/app',
    $root.'/routes',
    $root.'/database/seeders',
];

foreach ($targets as $target) {
    if (is_dir($target)) {
        $iterator($target);
    }
}

$en = require $root.'/lang/en/ui.php';
$np = require $root.'/lang/np/ui.php';

$missingEn = array_diff(array_keys($used), array_keys($en));
$missingNp = array_diff(array_keys($used), array_keys($np));
$unusedEn = array_diff(array_keys($en), array_keys($used));

echo 'Keys used: '.count($used)."\n";
echo 'EN file: '.count($en).' | NP file: '.count($np)."\n\n";

echo "\n--- Missing in lang/en/ui.php (".count($missingEn).") ---\n";
foreach ($missingEn as $key) {
    echo $key.'  ('.implode(', ', array_unique(array_slice($used[$key], 0, 2))).")\n";
}

echo "\n--- Missing in lang/np/ui.php (".count($missingNp).") ---\n";
foreach ($missingNp as $key) {
    echo $key."\n";
}

echo "\n--- Defined but unused in EN (".count($unusedEn).") ---\n";
echo implode(', ', $unusedEn)."\n";
