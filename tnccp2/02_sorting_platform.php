<?php

$path = __DIR__;

$data = json_decode(file_get_contents(__DIR__ . '/tnccp2.json'), true);

$platform = array();

foreach ($data AS $dataKey => $p) {
    switch ($p['name']) {
        default:
            $items = explode('。', $p['each_terms'][0]['platform']);
    }
    foreach ($items AS $k => $v) {
        $v = str_replace("\t", '', trim($v));
        if (empty($v)) {
            unset($items[$k]);
        } else {
            switch ($p['name']) {
                case '張世賢':
                case '賴美惠':
                case '趙昆原':
                case '蔡育輝':
                case '謝財旺':
                case '郭秀珠':
                case '陳朝來':
                case '林志展':
                case '林全忠':
                case '陳秋萍':
                case '李坤煌':
                case '李中岑':
                case '許至椿':
                case '邱莉莉':
                case '盧崑福':
                case '蔡旺詮':
                case '蔡淑惠':
                case '林美燕':
                case '莊玉珠':
                case '陳特清':
                case '張伯祿':
                case '蔡玉枝':
                    $items[$k] = trim(substr($v, strpos($v, '、') + 3));
                    break;
                case '賴惠員':
                    if (false !== strpos($v, '.')) {
                        $items[$k] = trim(substr($v, strpos($v, '.') + 1));
                    } else {
                        $items[$k] = trim(substr($v, strpos($v, '：') + 3));
                    }
                    break;
                case '李退之':
                    if (false !== strpos($v, '‧')) {
                        $items[$k] = trim(substr($v, strpos($v, '‧') + 3));
                    } else {
                        $items[$k] = $v;
                    }
                    break;
                case '侯澄財':
                case '陳文賢':
                case '蔡秋蘭':
                case '梁順發':
                case '林志聰':
                case '林燕祝':
                case '楊中成':
                case '郭信良':
                case '王錦德':
                case '林炳利':
                case '陳怡珍':
                case '洪玉鳳':
                    if (false !== strpos($v, '.')) {
                        $items[$k] = trim(substr($v, strpos($v, '.') + 1));
                    } else {
                        $items[$k] = $v;
                    }
                    break;
                case '吳通龍':
                    switch ($k) {
                        case 0:
                            $v = explode('二、 ', trim(substr($v, strpos($v, '、') + 3)));
                            $items[$k] = trim($v[0]);
                            $items[] = trim($v[1]);
                            break;
                        case 1:
                            $items[$k] = trim(substr($v, strpos($v, '、') + 3));
                            break;
                        case 2:
                            $v = explode('五、 ', trim(substr($v, strpos($v, '、') + 3)));
                            $items[$k] = trim($v[0]);
                            $items[] = trim($v[1]);
                            break;
                    }
                    break;
                case '蔡蘇秋金':
                    $items[$k] = trim(substr($v, strpos($v, '.') + 1));
                    break;
                case '李全教':
                    if (false !== strpos($v, '、')) {
                        $items[$k] = trim(substr($v, strpos($v, '、') + 3));
                    } else {
                        $items[$k] = $v;
                    }
                    break;
                case '林宜瑾':
                    $items[$k] = trim(substr($v, strpos($v, '＊') + 3));
                    break;
                case '陳金鐘':
                    $items[$k] = trim(substr($v, strpos($v, '●') + 3));
                    break;
                case '郭清華':
                    $items[$k] = trim(substr($v, strpos($v, '◎') + 3));
                    break;
                case '李文正':
                    $items[$k] = trim(substr($v, strpos($v, '˙') + 2));
                    break;
                default:
                    $items[$k] = $v;
            }
        }
    }
    if (!empty($items)) {
        $data[$dataKey]['each_terms'][0]['platform'] = array_values($items);
    }
}

file_put_contents(__DIR__ . '/tnccp2.json', json_encode($data));
