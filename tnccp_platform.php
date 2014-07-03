<?php

$path = __DIR__;

$data = json_decode(file_get_contents('tnccp.json'), true);

$platform = array();

foreach ($data AS $dataKey => $p) {
    switch ($p['name']) {
        case '李退之':
            $items = explode('(', $p['platform']);
            break;
        case '曾王雅雲':
            $items = array();
            break;
        default:
            $items = explode('。', $p['platform']);
    }
    foreach ($items AS $k => $v) {
        $v = trim($v);
        if (empty($v)) {
            unset($items[$k]);
        } else {
            switch ($p['name']) {
                case '賴美惠':
                    if ($k === 6) {
                        $v .= '，' . $items[7];
                    }
                    if ($k === 7) {
                        unset($items[$k]);
                        continue;
                    }
                    $items[$k] = substr($v, strpos($v, '、') + 3);
                    break;
                case '蔡蘇秋金':
                    if ($k !== 10) {
                        $items[$k] = substr($v, strpos($v, '.') + 1);
                    }
                    break;
                case '蔡秋蘭':
                    switch ($k) {
                        case 3:
                            $v .= '，' . $items[4];
                            break;
                        case 6:
                            $v .= '，' . $items[7];
                            break;
                        case 8:
                            $v .= '，' . $items[9];
                            break;
                        case 4:
                        case 7:
                        case 9:
                            unset($items[$k]);
                            continue(2);
                    }
                    $items[$k] = substr($v, strpos($v, '.') + 1);
                    break;
                case '李文俊':
                    switch ($k) {
                        case 2:
                            $v .= '，' . $items[3];
                            $v .= '，' . $items[4];
                            break;
                        case 3:
                        case 4:
                            unset($items[$k]);
                            continue(2);
                    }
                    $items[$k] = substr($v, strpos($v, '、') + 3);
                    break;
                case '郭國文':
                    switch ($k) {
                        case 3:
                        case 4:
                        case 5:
                        case 6:
                        case 8:
                        case 9:
                        case 10:
                        case 12:
                        case 13:
                        case 14:
                        case 16:
                        case 17:
                        case 19:
                        case 20:
                        case 21:
                            $items[$k] = substr($v, strpos($v, '）') + 3);
                            break;
                        default:
                            $items[$k] = substr($v, strpos($v, '、') + 3);
                    }

                    break;
                case '林宜瑾':
                    switch ($k) {
                        case 0:
                        case 1:
                        case 2:
                        case 3:
                        case 4:
                            $items[$k] = substr($v, strpos($v, '.') + 1);
                            break;
                        case 5:
                            $items[$k] = substr($v, strpos($v, '：') + 3);
                            break;
                        default:
                            $items[$k] = substr($v, strpos($v, ')') + 1);
                    }
                    break;
                case '黃麗招':
                    if ($k === 2) {
                        $v .= '，' . $items[3];
                    }
                    if ($k === 3) {
                        unset($items[$k]);
                        continue;
                    }
                    $items[$k] = substr($v, strpos($v, '、') + 3);
                    break;
                case '謝龍介':
                    switch ($k) {
                        case 0:
                        case 1:
                        case 2:
                        case 3:
                        case 4:
                            unset($items[$k]);
                            break;
                        default:
                            $items[$k] = substr($v, strpos($v, ')') + 1);
                    }
                    break;
                case '唐碧娥':
                    switch ($k) {
                        case 0:
                            $v .= '，' . $items[1];
                            break;
                        case 2:
                            $v .= '，' . $items[3];
                            break;
                        case 4:
                            $v .= '，' . $items[5];
                            break;
                        case 6:
                            $v .= '，' . $items[7];
                            break;
                        case 8:
                            $v .= '，' . $items[9];
                            $v .= '，' . $items[10];
                            break;
                        case 1:
                        case 3:
                        case 5:
                        case 7:
                        case 9:
                        case 10:
                            continue(2);
                            break;
                    }
                    $items[$k] = substr($v, strpos($v, '、') + 3);
                    break;
                case '邱莉莉':
                    $items[$k] = substr($v, strpos($v, '、') + 3);
                    if ($k === 4)
                        $items[$k] = substr($items[$k], strpos($items [$k], '、') + 3);
                    break;
                case '洪玉鳳':
                    switch ($k) {
                        case 0:
                            break;
                        case 1:
                            $v = substr($v, strpos($v, '—') + 3);
                            $items = explode('！', $v);
                            break;
                    }
                    break;
                case '陳文科':
                    switch ($k) {
                        case 1:
                        case 2:
                        case 3:
                        case 4:
                            $items[$k] = substr($v, strpos($v, '.') + 1);
                            break;
                        default:
                            $items[$k] = substr($v, strpos($v, '、') + 3);
                    }
                    break;
                case '蔡旺詮':
                    switch ($k) {
                        case 6:
                        case 8:
                        case 9:
                            continue;
                            break;
                        default:
                            $items[$k] = substr($v, strpos($v, '、') + 3);
                    }
                    break;
                case '曾培雅':
                    switch ($k) {
                        case 0:
                        case 11:
                            unset($items[$k]);
                            break;
                        case 10:
                            $items[$k] = substr($v, strpos($v, '、') + 3);
                            $items [$k] .= '，' . $items[11];
                            break;
                        default:
                            $items[$k] = substr($v, strpos($v, '、') + 3);
                    }
                    break;
                case '陳進益':
                    unset($items[$k]);
                    break;
                case '杜素吟':
                    switch ($k) {
                        case 1:
                            $v .= '，' . $items[2];
                            break;
                        case 2:
                            unset($items[$k]);
                            break;
                    }
                    $items[$k] = substr($v, strpos($v, '、') + 3);
                    break;
                case '張世賢':
                case '趙昆原':
                case '謝財旺':
                case '郭秀珠':
                case '吳通龍':
                case '陳文賢':
                case '陳朝來':
                case '林全忠':
                case '王峻潭':
                case '李坤煌':
                case '陳秋萍':
                case '陳怡珍':
                case '邱莉莉':
                case '盧崑福':
                case '曾順良':
                case '陸美祈':
                case '林美燕':
                case '蔡淑惠':
                case '張伯祿':
                case '陳特清':
                case '蔡玉枝':
                    $items[$k] = substr($v, strpos($v, '、') + 3);
                    break;
                case '賴惠員':
                case '蔡育輝':
                case '楊麗玉':
                case '林燕祝':
                case '施重男':
                case '林慶鎮':
                case '郭信良':
                case '林炳利':
                case '王錦德':
                case '李文正':
                    $items[$k] = substr($v, strpos($v, '.') + 1);
                    break;
                case '李退之':
                case '林志聰':
                    $items[$k] = substr($v, strpos($v, ') ') + 2);
                    break;
                case '許至椿':
                case '谷暮．哈就':
                    $items[$k] = substr($v, strpos($v, ')') + 1);
                    break;
                case '侯澄財':
                case '郭清華':
                    $items[$k] = substr($v, strpos($v, '）') + 3);
                    break;
                case '梁順發':
                    $items[$k] = substr($v, strpos($v, '〉') + 3);
                    break;
                default:
                    $items[$k] = $v;
            }
        }
    }

    if (!empty($items)) {
        $data[$dataKey]['platform'] = $items;
    }
}

file_put_contents('tnccp.json', json_encode($data));