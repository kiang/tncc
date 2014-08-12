<?php

$path = dirname(__DIR__);
$cacheFolder = $path . '/cache/population';
$targetFolder = __DIR__ . '/dirty';

if (!file_exists($cacheFolder)) {
    mkdir($cacheFolder, 0777, true);
}

if (!file_exists($targetFolder)) {
    mkdir($targetFolder, 0777, true);
}

$listFile = $cacheFolder . '/list';
if (!file_exists($listFile)) {
    file_put_contents($listFile, file_get_contents('http://210.69.40.18/population/population.asp'));
}
$list = mb_convert_encoding(file_get_contents($listFile), 'utf-8', 'big5');
$cols = explode('</td>', $list);

foreach ($cols AS $col) {
    if (false !== strpos($col, '#ccffcc') && false !== strpos($col, 'href')) {
        $col = explode('href="', $col);
        $col = explode('.xls', $col[1]);
        $col[1] = trim(str_replace('&nbsp;', '', strip_tags(substr($col[1], strpos($col[1], '>') + 1))));
        if (false === strpos($col[0], 'http')) {
            $col[0] = 'http://210.69.40.18/population/' . $col[0] . '.xls';
        } else {
            $col[0] .= '.xls';
        }
        $cacheFile = $cacheFolder . '/' . md5($col[0]) . '.xls';
        if (!file_exists($cacheFile)) {
            file_put_contents($cacheFile, file_get_contents($col[0]));
        }
        if (!file_exists($targetFolder . '/' . $col[1] . '.csv')) {
            exec("xls2csv -scp950 -dutf8 {$cacheFile} > {$targetFolder}/{$col[1]}.csv");
        }
    }
}