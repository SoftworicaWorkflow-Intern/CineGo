<?php

/** Lists the keys still missing from lang/np/ui.php. */
$root = dirname(__DIR__);

$en = require $root.'/lang/en/ui.php';
$np = require $root.'/lang/np/ui.php';

$missing = array_diff_key($en, $np);
ksort($missing);

echo count($missing)." keys missing in lang/np/ui.php\n";

foreach ($missing as $key => $value) {
    echo $key.' => '.var_export($value, true)."\n";
}
