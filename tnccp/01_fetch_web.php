<?php

$path = dirname(__DIR__);
$cacheFolder = $path . '/cache';

if (!file_exists($cacheFolder)) {
    mkdir($cacheFolder, 0777, true);
}

$data = array();

$listFile = $cacheFolder . '/list';
if (!file_exists($listFile)) {
    file_put_contents($listFile, file_get_contents('http://www.tncc.gov.tw/tnccp/ccp.asp'));
}

$listText = mb_convert_encoding(file_get_contents($listFile), 'utf8', 'big5');

$lMatches = array();

preg_match_all('/<h1 class="style1">[^\'"]*<\\/h1>/i', $listText, $lMatches);

foreach ($lMatches[0] AS $k => $lMatch) {
    $tPosBegin = strpos($listText, $lMatch);
    if ($k === 17) {
        $tPosEnd = strpos($listText, '</table>', $tPosBegin + 10);
    } else {
        $tPosEnd = strpos($listText, '<h1', $tPosBegin + 10);
    }
    preg_match_all('/CName=[^\'"]*/i', substr($listText, $tPosBegin, $tPosEnd - $tPosBegin), $matches);

    foreach ($matches[0] AS $match) {
        $pUrl = 'http://www.tncc.gov.tw/tnccp/ccp_01a.asp?CName=' . urlencode(substr(mb_convert_encoding($match, 'big5', 'utf8'), 6));
        $pFile = $cacheFolder . '/p_' . md5($pUrl);
        if (!file_exists($pFile)) {
            file_put_contents($pFile, file_get_contents($pUrl));
        }
        $pContent = mb_convert_encoding(file_get_contents($pFile), 'utf8', 'big5');
        $pContent = substr($pContent, strpos($pContent, '<div id="main">'));
        $pContent = substr($pContent, 0, strpos($pContent, '<div id="copyright">'));
        $pContent = str_replace('<br>', "\t", $pContent);
        $pLines = explode("\n", strip_tags($pContent));
        foreach ($pLines AS $k => $v) {
            $v = trim($v);
            if (empty($v)) {
                unset($pLines[$k]);
            } else {
                $pLines[$k] = $v;
            }
        }
        $pTitle = preg_split('/[\\(\\)]/i', strip_tags($lMatch));
        $pProfile = array(
            'url' => '',
            'uid' => '',
            'name' => '',
            'birth' => '',
            'former_names' => '',
            'each_terms' => array(
                0 => array(
                    'contact_details' => array(
                        0 => array(
                            'label' => '電子信箱',
                            'type' => 'email',
                            'value' => '',
                        ),
                        1 => array(
                            'label' => '電話',
                            'type' => 'voice',
                            'value' => '',
                        ),
                        2 => array(
                            'label' => '傳真',
                            'type' => 'fax',
                            'value' => '',
                        ),
                        3 => array(
                            'label' => '通訊處',
                            'type' => 'address',
                            'value' => '',
                        ),
                    ),
                    'term_end' => array(
                        'date' => '2014-12-25',
                    ),
                    'links' => array(
                        0 => array(
                            'note' => '議會個人官網',
                            'url' => $pUrl,
                        ),
                    ),
                    'social_media' => array(),
                    'url' => '',
                    'councilor' => '',
                    'election_year' => '2010',
                    'name' => '',
                    'gender' => '',
                    'party' => '',
                    'title' => '',
                    'constituency' => '臺南市' . $pTitle[0],
                    'county' => '',
                    'district' => $pTitle[1],
                    'in_office' => true,
                    'term_start' => '2010-12-25',
                    'education' => '',
                    'experience' => '',
                    'remark' => '',
                    'image' => '',
                    'platform' => '',
                ),
            ),
        );
        $imagePos = strpos($pContent, '/warehouse/');
        if (false !== $imagePos) {
            $imagePosEnd = strpos($pContent, '"', $imagePos);
            $imagePaths = explode('/', substr($pContent, $imagePos, $imagePosEnd - $imagePos));
            foreach ($imagePaths AS $imagePathKey => $imagePath) {
                $imagePaths[$imagePathKey] = urlencode($imagePath);
            }
            $pProfile['each_terms'][0]['image'] = 'http://www.tncc.gov.tw' . implode('/', $imagePaths);
        }
        if (isset($pLines[14]) && false !== strpos($pLines[14], '姓名：')) {
            $pProfile['name'] = $pProfile['each_terms'][0]['name'] = substr($pLines[14], 9);
        } else {
            continue;
        }
        if (isset($pLines[17]) && false !== strpos($pLines[17], '出生：')) {
            $pProfile['birth'] = substr($pLines[17], 9);
        }
        if (isset($pLines[18]) && false !== strpos($pLines[18], '性別：')) {
            $pProfile['each_terms'][0]['gender'] = substr($pLines[18], 9);
        }
        if (isset($pLines[21]) && false !== strpos($pLines[21], '黨籍：')) {
            $pProfile['each_terms'][0]['party'] = substr($pLines[21], 9);
        }
        if (isset($pLines[23]) && false !== strpos($pLines[23], '電話：')) {
            $pProfile['each_terms'][0]['contact_details'][1]['value'] = substr($pLines[23], 9);
        }
        if (isset($pLines[26]) && false !== strpos($pLines[26], '通&nbsp;訊&nbsp;處：')) {
            $pProfile['each_terms'][0]['contact_details'][3]['value'] = substr($pLines[26], 24);
        }
        if (isset($pLines[29]) && false !== strpos($pLines[29], '電子信箱：')) {
            $pProfile['each_terms'][0]['contact_details'][0]['value'] = substr($pLines[29], 15);
        }
        if (isset($pLines[33]) && ((false !== strpos($pLines[33], 'FaceBook：')) || (false !== strpos($pLines[33], '部落格：')))) {
            $pProfile['each_terms'][0]['links'][] = array(
                'note' => '個人網站',
                'url' => explode('：', $pLines[33])[1],
            );
        }
        $tokenKey = false;
        foreach ($pLines AS $pLine) {
            if (false !== $tokenKey) {
                if ($tokenKey !== 'platform') {
                    $pProfile['each_terms'][0][$tokenKey] = explode("\t", $pLine);
                    foreach ($pProfile['each_terms'][0][$tokenKey] AS $uKey => $uVal) {
                        $uVal = trim($uVal);
                        if (!empty($uVal)) {
                            $pProfile['each_terms'][0][$tokenKey][$uKey] = $uVal;
                        } else {
                            unset($pProfile['each_terms'][0][$tokenKey][$uKey]);
                        }
                    }
                } else {
                    $pProfile['each_terms'][0][$tokenKey] = $pLine;
                }
                $tokenKey = false;
            } else {
                switch ($pLine) {
                    case '學　歷':
                        $tokenKey = 'education';
                        break;
                    case '經　歷':
                        $tokenKey = 'experience';
                        break;
                    case '政　見':
                        $tokenKey = 'platform';
                        break;
                }
            }
        }
        $data[] = $pProfile;
    }
}

file_put_contents($path . '/tnccp/tnccp.json', json_encode($data));
