<?php

$path = __DIR__;
$cacheFolder = $path . '/cache';
$listFolder = $cacheFolder . '/motions_list';
$itemFolder = $cacheFolder . '/motions_item';
if (!file_exists($listFolder)) {
    mkdir($listFolder, 0777, true);
}
if (!file_exists($itemFolder)) {
    mkdir($itemFolder, 0777, true);
}

for ($i = 1; $i <= 142; $i++) {
    $url = 'http://www.tncc.gov.tw/motions/default1.asp?status=^&menu1=A00000&topage=' . $i;
    $cacheFile = $listFolder . '/' . md5($url);
    if (!file_exists($cacheFile)) {
        file_put_contents($cacheFile, file_get_contents($url));
    }
    $listContent = file_get_contents($cacheFile);
    $listContent = substr($listContent, strpos($listContent, 'id="printa"') + 12);
    $listContent = substr($listContent, 0, strpos($listContent, '</table>'));
    $listLines = explode('</tr>', $listContent);
    foreach ($listLines AS $listLine) {
        $cols = explode('</td>', $listLine);
        if (false === strpos($cols[0], 'mainid=')) {
            continue;
        }
        unset($cols[9]);
        foreach ($cols AS $k => $col) {
            switch($k) {
                case 0:
                    //example: 1A1F7251-3CD8-4AE6-BB97-6C3A021D9351
                    $cols[$k] = substr(explode('mainid={', $col)[1], 0, 36);
                    break;
                default:
                    $cols[$k] = trim(strip_tags($col));
            }
        }
        $itemCacheFile = $itemFolder . '/' . str_replace('-', '/', $cols[0]);
        if(!file_exists(dirname($itemCacheFile))) {
            mkdir(dirname($itemCacheFile), 0777, true);
        }
        if(!file_exists($itemCacheFile)) {
            file_put_contents($itemCacheFile, file_get_contents('http://www.tncc.gov.tw/motions/page.asp?mainid=' . $cols[0]));
        }
    }
}