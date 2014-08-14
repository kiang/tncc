<?php

$path = dirname(__DIR__);
$cacheFolder = $path . '/cache/grants';
$targetFolder = __DIR__ . '/dirty';

if (!file_exists($cacheFolder)) {
    mkdir($cacheFolder, 0777, true);
}

if (!file_exists($targetFolder)) {
    mkdir($targetFolder, 0777, true);
}

$data = array();

foreach (glob($targetFolder . '/*') AS $txtFile) {
    $txtContent = file_get_contents($txtFile);
    $year = substr($txtFile, 0, strpos($txtFile, '.'));
    $data[$year] = array();
    if (substr($txtFile, -3) === 'txt') {
        continue;
        $lines = explode("\n", $txtContent);
        foreach ($lines AS $line) {
            $cols = explode(' ', $line);
            if (count($cols) === 9) {
                $data[$year][] = array(
                    'name' => $cols[0],
                    'area' => $cols[1],
                    'budget_submitted' => $cols[2],
                    'budget_approved' => $cols[3],
                    'account' => $cols[4],
                    'department' => $cols[6],
                    'type' => $cols[7],
                    'vendor' => $cols[8],
                );
            }
        }
    } else {
        $pos = strpos($txtContent, '<table');
        while (false !== $pos) {
            $pos = strpos($txtContent, '>', $pos + 1) + 1;
            $posEnd = strpos($txtContent, '</table', $pos);
            $subContent = substr($txtContent, $pos, $posEnd - $pos);
            $lines = explode('</tr>', $subContent);
            foreach ($lines AS $line) {
                $cols = explode('</td>', $line);
                foreach ($cols AS $k => $col) {
                    $cols[$k] = trim(strip_tags(str_replace(array('翊', ' '), '', $col)));
                }
                if (isset($cols[1]) && (false !== strpos($cols[1], '區'))) {
                    $data[$year][] = array(
                        'name' => $cols[0],
                        'area' => $cols[1],
                        'budget_submitted' => $cols[2],
                        'budget_approved' => $cols[3],
                        'account' => $cols[4],
                        'department' => $cols[5],
                        'type' => $cols[6],
                        'vendor' => isset($cols[7]) ? $cols[7] : '',
                    );
                }
            }
            $pos = strpos($txtContent, '<table', $posEnd);
        }
    }
}

file_put_contents(__DIR__ . '/grants.json', json_encode($data));

exit();

/*
 * The code below is a slow method
 */

$listFile = $cacheFolder . '/list';
if (!file_exists($listFile)) {
    file_put_contents($listFile, file_get_contents('http://www.tainan.gov.tw/tainan/Grants.asp?nsub=A6C400'));
}
$list = file_get_contents($listFile);
$pos = strpos($list, 'warehouse/A60000');
while (false !== $pos) {
    $posEnd = strpos($list, '"', $pos + 1);
    $fileUrl = 'http://www.tainan.gov.tw/tainan/' . substr($list, $pos, $posEnd - $pos);
    $fileToken = md5($fileUrl);
    $fileCache = "{$cacheFolder}/file_" . $fileToken;
    echo "{$fileUrl}\n{$fileCache}\n\n";
    $pos = strpos($list, 'warehouse/A60000', $pos + 1);
    continue;

    if (!file_exists($fileCache) || filesize($fileCache) === 0) {
        $sPos = strrpos($fileUrl, '/');
        file_put_contents($fileCache, file_get_contents(substr($fileUrl, 0, $sPos + 1) . urlencode(substr($fileUrl, $sPos + 1))));
    }
    if (file_exists($fileCache) && !file_exists($fileCache . '.txt')) {
        exec("java -cp /usr/share/java/commons-logging.jar:/usr/share/java/fontbox.jar:/usr/share/java/pdfbox.jar org.apache.pdfbox.PDFBox ExtractText {$fileCache} tmp.txt");
    }
    if (file_exists('tmp.txt')) {
        copy('tmp.txt', $fileCache . '.txt');
        unlink('tmp.txt');
    }
    if (file_exists($fileCache . '.txt')) {
        $txtContent = file_get_contents($fileCache . '.txt');
        if (false === strpos($txtContent, '建議事項')) {
            exec("gs -dNOPAUSE -dNumRenderingThreads=4 -sDEVICE=jpeg -sOutputFile={$fileToken}-%04d.jpg -dJPEGQ=90 -r150x150 -q {$fileCache} -c quit");
            foreach (glob($fileToken . '*') AS $jpgFile) {
                exec("/usr/bin/tesseract {$jpgFile} {$jpgFile} -l chi_tra");
            }
        }
    }
    $pos = strpos($list, 'warehouse/A60000', $pos + 1);
}