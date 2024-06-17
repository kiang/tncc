<?php

$path = __DIR__;
$cacheFolder = $path . '/cache/journal';
$resultFolder = $path . '/download';
if (!file_exists($cacheFolder)) {
    mkdir($cacheFolder, 0777, true);
}
if (!file_exists($resultFolder)) {
    mkdir($resultFolder, 0777, true);
}

$page1Url = 'http://www.tncc.gov.tw/download.asp?nsub=R00000';
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
    $pageUrl = 'http://www.tncc.gov.tw/download.asp?nsub=R00000&topage=' . $i;
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

$tnccp = json_decode(file_get_contents($path . '/tnccp2/tnccp2.json'), true);
$counters = array();

foreach ($fileList AS $r) {
    foreach ($r[2] AS $fileUri) {
        $cachedFile = $cacheFolder . '/' . md5($fileUri);
        $txtFile = $resultFolder . '/' . date('Ymd', strtotime($r[0])) . '_' . $r[1] . '.txt';
        if (!file_exists($cachedFile)) {
            $fileUri = str_replace(array(' '), array('%20'), $fileUri);
            file_put_contents($cachedFile, file_get_contents('http://www.tncc.gov.tw/' . $fileUri));
        }
        if (filesize($cachedFile) == 0) {
            unlink($cachedFile);
        } elseif (!file_exists($txtFile)) {
            exec("java -cp /usr/share/java/commons-logging.jar:/usr/share/java/fontbox.jar:/usr/share/java/pdfbox.jar org.apache.pdfbox.PDFBox ExtractText {$cachedFile} tmp.txt");
            if (file_exists('tmp.txt')) {
                copy('tmp.txt', $txtFile);
                unlink('tmp.txt');
            } else {
                print_r($r);
            }
        }
        $eKey = substr($r[1], 0, strrpos($r[1], '會') + 3);

        $txtContent = file_get_contents($txtFile);
        foreach ($tnccp AS $p) {
            if (false !== strpos($p['name'], '谷暮')) {
                $nameKey = mb_substr($p['name'], 0, 2, 'utf-8');
            } else {
                $nameKey = $p['name'];
            }

            if(!isset($counters[$p['name']])) {
              $counters[$p['name']] = array();
            }
            if(!isset($counters[$p['name']][$eKey])) {
              $counters[$p['name']][$eKey] = array(1 => 0, 2 => 0, 3 => 0);
            }

            $pos = strpos($txtContent, $nameKey);
            while (false !== $pos) {
                $posEnd = strpos($txtContent, "\n", $pos);
                $line = substr($txtContent, $pos, $posEnd - $pos);
                $wCounter = array(
                    1 => substr_count($line, '○'),
                    2 => substr_count($line, '△'),
                    3 => substr_count($line, '×'),
                );
                if ($wCounter[1] + $wCounter[2] + $wCounter[3] > 0) {
                    //echo "{$line}\n" . implode(',', $wCounter) . "\n";
                    $counters[$p['name']][$eKey][1] += $wCounter[1];
                    $counters[$p['name']][$eKey][2] += $wCounter[2];
                    $counters[$p['name']][$eKey][3] += $wCounter[3];
                    //echo implode(',', $counters[$p['name']]) . "\n";
                }

                $pos = strpos($txtContent, $nameKey, $pos + 1);
            }
        }
    }
}

file_put_contents($resultFolder . '/list_journals2.json', json_encode($fileList, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
file_put_contents($resultFolder . '/journals_attendances2.json', json_encode($counters, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
