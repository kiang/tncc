<?php

$path = dirname(__DIR__);
$cacheFolder = $path . '/cache/population';
$targetFolder = __DIR__ . '/clean';

if (!file_exists($cacheFolder)) {
    mkdir($cacheFolder, 0777, true);
}

if (!file_exists($targetFolder)) {
    mkdir($targetFolder, 0777, true);
}

foreach (glob(__DIR__ . '/dirty/*.csv') AS $csvFile) {
    $content = file_get_contents($csvFile);
    $pages = explode("\f", $content);
    echo count($pages) . "\n";
}