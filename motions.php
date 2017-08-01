<?php

$path = __DIR__;
$cacheFolder = $path . '/cache';
$listFolder = $cacheFolder . '/motions_list';
$itemFolder = $cacheFolder . '/motions_item';
$publicFolder = $path . '/motions/A10000';
if (!file_exists($listFolder)) {
    mkdir($listFolder, 0777, true);
}
if (!file_exists($itemFolder)) {
    mkdir($itemFolder, 0777, true);
}

$finalPage = 66;
$finalPageUpdated = false;

for ($i = 1; $i <= $finalPage; $i++) {
    $url = 'http://www.tncc.gov.tw/motions/default1.asp?status=^&menu1=A10000&topage=' . $i;
    $cacheFile = $listFolder . '/' . md5($url);
    if (!file_exists($cacheFile)) {
        file_put_contents($cacheFile, file_get_contents($url));
    }
    $listContent = file_get_contents($cacheFile);
    if(false === $finalPageUpdated) {
        $pageParts = explode('topage=', $listContent);
        foreach($pageParts AS $pagePart) {
            $pagePart = substr($pagePart, 0, 50);
            if(false !== strpos($pagePart, 'menu1')) {
                $getPage = intval(substr($pagePart, 0, strpos($pagePart, '&')));
                if($getPage > $finalPage) {
                    $finalPage = $getPage;
                }
            }
        }
        $finalPageUpdated = true;
    }
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
            switch ($k) {
                case 0:
                    //example: 1A1F7251-3CD8-4AE6-BB97-6C3A021D9351
                    $cols[$k] = substr(explode('mainid={', $col)[1], 0, 36);
                    break;
                default:
                    $cols[$k] = trim(strip_tags($col));
            }
        }
        $itemCacheFile = $itemFolder . '/' . str_replace('-', '/', $cols[0]);
        if (!file_exists(dirname($itemCacheFile))) {
            mkdir(dirname($itemCacheFile), 0777, true);
        }
        if (!file_exists($itemCacheFile)) {
            file_put_contents($itemCacheFile, file_get_contents('http://www.tncc.gov.tw/motions/page.asp?mainid=' . $cols[0]));
        }
        $itemContent = file_get_contents($itemCacheFile);
        $itemContent = substr($itemContent, strpos($itemContent, '"table2">') + 10);
        $itemContent = substr($itemContent, 0, strpos($itemContent, '</table>'));
        $lines = explode('</tr>', $itemContent);
        $isMisc = false;
        foreach ($lines AS $lineKey => $lineVal) {
            $lineVal = str_replace('</th>', '</td>', $lineVal);
            $lineCols = explode('</td>', $lineVal);
            switch ($lineKey) {
                case 8:
                    $lineCols[1] = str_replace('<br>', "\n", $lineCols[1]);
                    break;
            }
            foreach ($lineCols AS $k => $lineCol) {
                $lineCols[$k] = trim(strip_tags($lineCol));
            }
            switch ($lineKey) {
                case 5:
                    $lineCols[1] = explode(' ', $lineCols[1]);
                    break;
                case 6:
                    $lineCols[1] = str_replace(' ', '', $lineCols[1]);
                    $lineCols[1] = explode(',', $lineCols[1]);
                    break;
                case 14:
                    $dateVals = explode('/', $lineCols[1]);
                    if (count($dateVals) !== 3) {
                        $dateVals = explode('/', $lines[13][1]);
                        if (count($dateVals) !== 3) {
                            $dateVals = explode('/', $lines[3][1]);
                            if (count($dateVals) !== 3) {
                                $targetPath = "{$publicFolder}/misc";
                                $isMisc = true;
                            }
                        }
                    }
                    if (!$isMisc) {
                        $targetPath = "{$publicFolder}/{$dateVals[0]}/{$dateVals[1]}";
                    }

                    if (!file_exists($targetPath)) {
                        mkdir($targetPath, 0777, true);
                    }
                    break;
            }
            $lines[$lineKey] = $lineCols;
        }
        if($isMisc) {
            $itemResultFile = "{$targetPath}/{$cols[0]}.json";
        } else {
            $itemResultFile = "{$targetPath}/{$dateVals[2]}-{$cols[0]}.json";
        }

        if (!file_exists($itemResultFile)) {
            $result = array();
            foreach ($lines AS $lineKey => $lineVals) {
                switch ($lineKey) {
                    case 0:
                    case 1:
                        // 4 cols
                        $result[$lineVals[0]] = $lineVals[1];
                        $result[$lineVals[2]] = $lineVals[3];
                        break;
                    case 17:
                        //skip
                        break;
                    default:
                        // 2 cols
                        $result[$lineVals[0]] = $lineVals[1];
                }
            }
            file_put_contents($itemResultFile, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }
}
