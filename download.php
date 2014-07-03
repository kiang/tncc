<?php

$path = __DIR__;
$cacheFolder = $path . '/cache/download';
$resultFolder = $path . '/download';
if (!file_exists($cacheFolder)) {
    mkdir($cacheFolder, 0777, true);
}
if (!file_exists($resultFolder)) {
    mkdir($resultFolder, 0777, true);
}

$page1Url = 'http://www.tncc.gov.tw/download.asp?nsub=H00000';
if (!file_exists($cacheFolder . '/p1')) {
    file_put_contents($cacheFolder . '/p1', file_get_contents($page1Url));
}
$page1Text = file_get_contents($cacheFolder . '/p1');

$page1Parts = explode('topage=', $page1Text);
unset($page1Parts[0]);
$totalPages = 1;
foreach ($page1Parts AS $page1Part) {
    $cPage = intval(substr($page1Part, 0, strpos($page1Part, '"')));
    if ($cPage > $totalPages) {
        $totalPages = $cPage;
    }
}

$fileList = array();

for ($i = 1; $i <= $totalPages; $i++) {
    $pageUrl = 'http://www.tncc.gov.tw/download.asp?nsub=H00000&topage=' . $i;
    if (!file_exists($cacheFolder . '/p' . $i)) {
        file_put_contents($cacheFolder . '/p' . $i, file_get_contents($pageUrl));
    }
    $pageText = file_get_contents($cacheFolder . '/p' . $i);
    $pos = strpos($pageText, '<table width="99%" border="0" cellpadding="3" cellspacing="1" id="table2"');
    $posEnd = strpos($pageText, '</table>', $pos);
    $pageText = substr($pageText, $pos, $posEnd - $pos);
    $lines = explode('</tr>', $pageText);
    foreach ($lines AS $line) {
        $cols = explode('</td>', $line);
        if (count($cols) === 4) {
            foreach ($cols AS $k => $v) {
                switch ($k) {
                    case 0:
                    case 1:
                        $cols[$k] = trim(strip_tags($v));
                        break;
                    case 2:
                        $links = explode('</a>', $v);
                        foreach ($links AS $lk => $lv) {
                            $lPos = strpos($lv, 'warehouse');
                            if (false !== $lPos) {
                                $links[$lk] = substr($lv, $lPos, strpos($lv, '"', $lPos) - $lPos);
                            } else {
                                unset($links[$lk]);
                            }
                        }
                        $cols[$k] = $links;
                        break;
                    case 3:
                        unset($cols[$k]);
                        break;
                }
            }
            $fileList[] = $cols;
        }
    }
}

file_put_contents($resultFolder . '/list.json', json_encode($fileList));