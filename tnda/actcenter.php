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
        $actcenterId = substr($cols[2][0], strpos($cols[2][0], '={') + 2);
        $cols[2][0] = 'http://tnda.tainan.gov.tw/acthouse/actpage.asp?mainid=' . $actcenterId;
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

        $cachedFile = $cacheFolder . '/' . $actcenterId;
        if (!file_exists($cachedFile)) {
            file_put_contents($cachedFile, file_get_contents($cols[2][0]));
        }
        $actcenter = file_get_contents($cachedFile);
        $actcenter = substr($actcenter, strpos($actcenter, 'summary="場地借用"'));
        $atpos = strpos($actcenter, '>');
        $actcenter = substr($actcenter, $atpos + 1, strpos($actcenter, '</table>') - $atpos - 1);
        $actLines = explode('</tr>', $actcenter);
        $places = array();
        foreach ($actLines AS $actLine) {
            if (false !== strpos($actLine, '<td>')) {
                $actCols = explode('</td>', $actLine);
                $placeIdPos = strpos($actCols[5], '={') + 2;
                $actCols[5] = substr($actCols[5], $placeIdPos, strpos($actCols[5], '}') - $placeIdPos);
                foreach ($actCols AS $k => $v) {
                    $actCols[$k] = trim(strip_tags($v));
                }
                $places[] = array(
                    'name' => $actCols[0],
                    'url' => 'http://tnda.tainan.gov.tw/acthouse/online.asp?mainid=' . $actCols[5],
                    'air_conditioner' => ($actCols[1] === '有') ? 'yes' : 'no',
                    'ktv' => ($actCols[2] === '有') ? 'yes' : 'no',
                    'projector' => ($actCols[3] === '有') ? 'yes' : 'no',
                    'capacity' => intval($actCols[4]),
                );
            }
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
            'places' => $places,
        );
    }
}

file_put_contents(__DIR__ . '/actcenter.json', json_encode($data));
