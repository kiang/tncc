<?php

$fh = fopen('address-20140701.csv', 'r');
fgetcsv($fh, 2048);
fgetcsv($fh, 2048);
$address = array();
while ($line = fgetcsv($fh, 2048)) {
    $address[$line[2]] = $line;
}
fclose($fh);

$data = json_decode(file_get_contents('tnccp.json'), true);

foreach ($data AS $k => $p) {
    if (isset($address[$p['name']])) {
        $data[$k]['each_terms'][0]['contact_details'][3]['value'] = $address[$p['name']][4] . $address[$p['name']][5];
        $data[$k]['each_terms'][0]['contact_details'][1]['value'] = $address[$p['name']][6];
        $data[$k]['each_terms'][0]['contact_details'][2]['value'] = $address[$p['name']][7];
    }
}

file_put_contents(__DIR__ . '/tnccp.json', json_encode($data));
