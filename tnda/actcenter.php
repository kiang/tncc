<?php

$path = dirname(__DIR__);
$cacheFolder = $path . '/cache/tnda';

if (!file_exists($cacheFolder)) {
    mkdir($cacheFolder, 0777, true);
}

$data = array();

$totalPages = 2;
for ($i = 1; $i <= $totalPages; $i++) {
    $listUrl = 'http://tnda.tainan.gov.tw/acthouse/actcenter.asp?topage=' . $i;
    $listFile = $cacheFolder . '/' . md5($listUrl);
    if (!file_exists($listFile)) {
        file_put_contents($listFile, file_get_contents($listUrl));
    }
    $listContent = file_get_contents($listFile);
    if ($totalPages === 2) {
        $pageParts = explode('topage=', $listContent);
        foreach ($pageParts AS $pagePart) {
            $pagePart = substr($pagePart, 0, 100);
            $pagePartPos = strpos($pagePart, '&');
            if (false !== $pagePartPos) {
                $pageNumber = intval(substr($pagePart, 0, $pagePartPos));
                if ($pageNumber > $totalPages) {
                    $totalPages = $pageNumber;
                }
            }
        }
    }

    $tPos = strpos($listContent, '<table');
    $tPos = strpos($listContent, '<table', $tPos + 1);
    $tPosEnd = strpos($listContent, '</table>', $tPos);
    $listContent = substr($listContent, $tPos, $tPosEnd - $tPos);
    $lines = explode('</tr>', $listContent);
    foreach ($lines AS $line) {
        $cols = explode('</td>', $line);
        if (count($cols) !== 7)
            continue;
        $pos1 = strpos($cols[0], 'warehouse');
        if (false !== $pos1) {
            $pos2 = strpos($cols[0], '"', $pos1);
            $cols[0] = 'http://tnda.tainan.gov.tw/' . substr($cols[0], $pos1, $pos2 - $pos1);
        } else {
            $cols[0] = '';
        }
        $cols[1] = trim(strip_tags($cols[1]));
        $cols[2] = explode('}">', $cols[2]);
        $cols[2][0] = 'http://tnda.tainan.gov.tw/acthouse/actpage.asp?mainid=' . substr($cols[2][0], strpos($cols[2][0], '={') + 2);
        $cols[2][1] = trim(strip_tags($cols[2][1]));
        $cols[3] = trim(strip_tags($cols[3]));
        $cols[4] = trim(strip_tags($cols[4]));
        $pos1 = strpos($cols[5], '655,400,');
        if (false !== $pos1) {
            $pos2 = strpos($cols[5], ')', $pos1);
            $cols[5] = explode(',', substr($cols[5], $pos1 + 8, $pos2 - $pos1 - 8));
        } else {
            $cols[5] = array();
        }
        $data[] = array(
            'name' => $cols[2][1],
            'url' => $cols[2][0],
            'image' => $cols[0],
            'area' => $cols[1],
            'address' => $cols[3],
            'contact' => $cols[4],
            'latitude' => $cols[5][0],
            'longitude' => $cols[5][1],
        );
    }
}

file_put_contents(__DIR__ . '/actcenter.json', json_encode($data));
