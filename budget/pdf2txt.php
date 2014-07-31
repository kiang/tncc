<?php

$path = dirname(__DIR__);
$resultFolder = $path . '/budget/txt';
if (!file_exists($resultFolder)) {
    mkdir($resultFolder, 0777, true);
}


foreach (glob(__DIR__ . '/pdf/*.pdf') AS $file) {
    $txtFile = $resultFolder . '/' . substr(basename($file), 0, -4) . '.txt';
    exec("java -cp /usr/share/java/commons-logging.jar:/usr/share/java/fontbox.jar:/usr/share/java/pdfbox.jar org.apache.pdfbox.PDFBox ExtractText {$file} tmp.txt");
    if (file_exists('tmp.txt')) {
        copy('tmp.txt', $txtFile);
        unlink('tmp.txt');
    } else {
        print_r($r);
    }
}

