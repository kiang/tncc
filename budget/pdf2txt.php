<?php

$path = dirname(__DIR__);
$resultFolder = $path . '/budget/txt';
if (!file_exists($resultFolder)) {
    mkdir($resultFolder, 0777, true);
}
$data = array();
foreach (glob(__DIR__ . '/pdf/*.pdf') AS $file) {
    if (!file_exists("{$file}.txt")) {
        exec("/usr/bin/pdftotext -layout {$file} {$file}.txt");
    }
    $baseName = basename($file);
    $baseKey = substr($baseName, 0, strpos($baseName, '_'));
    $txtContent = Normalizer::normalize(file_get_contents("{$file}.txt"), Normalizer::FORM_C);
    switch ($baseName) {
        case 'Y100_summary.pdf':
        case 'Y101_summary.pdf':
        case 'Y103_summary.pdf':
            $sheets = explode('臺南市地方總預算', $txtContent);
            foreach ($sheets AS $key => $sheet) {
                switch ($key) {
                    case 1: //歲入歲出簡明比較分析表
                        $lines = explode("\n", $sheet);
                        foreach ($lines AS $line) {
                            if (false !== strpos($line, '.')) {
                                $cols = preg_split('/[ ]+/i', $line);
                                if (false !== strpos($cols[0], '.')) {
                                    $cols[0] = trim(str_replace(array('　'), array(''), $cols[0]));
                                    if (!isset($data[$cols[0]])) {
                                        $data[$cols[0]] = array();
                                    }
                                    $cols[1] = isset($cols[1]) ? trim($cols[1]) : '0';
                                    $cols[1] = intval(str_replace(array('.', ','), array('', ''), $cols[1]));
                                    $data[$cols[0]][$baseKey] = $cols[1];
                                }
                            }
                        }
                        break;
                    case 2: //歲入歲出性質及餘絀簡明比較分析表
                        break;
                    case 3: //收支簡明比較分析表
                        break;
                }
            }
            break;
        case 'Y100a1_summary.pdf':
        case 'Y101a1_summary.pdf':
        case 'Y102a1_summary.pdf':
            $sheets = explode('臺南市地方總預算', $txtContent);
            foreach ($sheets AS $key => $sheet) {
                switch ($key) {
                    case 1: //歲入歲出簡明比較分析表
                        $lines = explode("\n", $sheet);
                        foreach ($lines AS $line) {
                            if (false !== strpos($line, '.')) {
                                $cols = preg_split('/[ ]+/i', $line);
                                if (false !== strpos($cols[0], '.')) {
                                    $cols[0] = trim(str_replace(array('　'), array(''), $cols[0]));
                                    if (!isset($data[$cols[0]])) {
                                        $data[$cols[0]] = array();
                                    }
                                    if (count($cols) === 7) {
                                        $colKey = 5;
                                    } else {
                                        $colKey = 3;
                                    }
                                    $cols[$colKey] = isset($cols[$colKey]) ? trim($cols[$colKey]) : '0';
                                    $cols[$colKey] = intval(str_replace(array('.', ','), array('', ''), $cols[$colKey]));
                                    $data[$cols[0]][$baseKey] = $cols[$colKey];
                                }
                            }
                        }
                        break;
                    case 2: //歲入歲出性質及餘絀簡明比較分析表
                        break;
                    case 3: //收支簡明比較分析表
                        break;
                }
            }
            break;
        case 'Y102_summary.pdf':
            $sheets = explode('臺南市地方總預算', $txtContent);
            foreach ($sheets AS $key => $sheet) {
                switch ($key) {
                    case 0:
                        $lines = explode("\n", $sheet);
                        foreach ($lines AS $lineKey => $line) {
                            if ($lineKey < 51) {
                                if (false !== strpos($line, '.')) {
                                    $cols = preg_split('/[ ]+/i', $line);
                                    if (false !== strpos($cols[0], '.')) {
                                        $cols[0] = trim(str_replace(array('　'), array(''), $cols[0]));
                                        $cols[1] = isset($cols[1]) ? trim($cols[1]) : '0';
                                        $cols[1] = intval(str_replace(array('.', ','), array('', ''), $cols[1]));
                                        if ($lineKey < 28) { //收入
                                            foreach ($data AS $dataKey => $dataVal) {
                                                if (false !== strpos($dataKey, '收入') && false !== strpos($dataKey, $cols[0])) {
                                                    $data[$dataKey][$baseKey] = $cols[1];
                                                }
                                            }
                                        } else { //支出
                                            foreach ($data AS $dataKey => $dataVal) {
                                                if (false !== strpos($dataKey, '支出') && false !== strpos($dataKey, $cols[0])) {
                                                    $data[$dataKey][$baseKey] = $cols[1];
                                                }
                                            }
                                        }
                                    }
                                }
                            } elseif ($lineKey < 93) {
                                
                            } else {
                                
                            }
                        }
                        break;
                    case 2: //歲入歲出性質及餘絀簡明比較分析表
                        break;
                    case 3: //收支簡明比較分析表
                        break;
                }
            }
            break;
    }
}

$fh = fopen(__DIR__ . '/txt/budget_summary.csv', 'w');
$labelPushed = false;
foreach ($data AS $key => $cols) {
    if (count($cols) === 7) {
        if (false === $labelPushed) {
            fputcsv($fh, array_merge(array('項目'), array_keys($cols)));
            $labelPushed = true;
        }
        fputcsv($fh, array_merge(array($key), $cols));
    }
}