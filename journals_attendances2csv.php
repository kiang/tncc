<?php

$path = __DIR__ . '/download/';

$f = json_decode(file_get_contents($path . 'journals_attendances.json'), true);

$h = false;

$fh = fopen($path . 'journals_attendances2csv.csv', 'w');

foreach ($f AS $p => $o) {
    if (false === $h) {
        $fields = array('議員');

        foreach ($o AS $k => $v) {
            $fields[] = $k;
            for ($i = 0; $i < 2; $i ++) {
                $fields[] = '';
            }
        }
        fputcsv($fh, $fields);

        $fields = array('');

        foreach ($o AS $k => $v) {
            $fields[] = '出席';
            $fields[] = '請假';
            $fields[] = '曠職';
        }
        fputcsv($fh, $fields);
        $h = true;
    }
    $fields = array($p);
    foreach ($o AS $v) {
        foreach ($v AS $c) {
            $fields[] = $c;
        }
    }
    fputcsv($fh, $fields);
}
