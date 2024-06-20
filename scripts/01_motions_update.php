<?php
$bastPath = dirname(__DIR__);
//https://bill.tncc.gov.tw/NoPaperMeeting_TNCC/api/WEB014_GetProposalList.ashx?topage=1&pagerow=100&grdno=1

$grdno = 4;
$grdPath = $bastPath . '/motions/' . $grdno;
if (!file_exists($grdPath)) {
    mkdir($grdPath, 0777, true);
}
$page = 1;
$pageAll = 1;
$pageAllDone = false;
function cmp($a, $b)
{
    if (!isset($a['FileName']) || $a['FileName'] == $b['FileName']) {
        return 0;
    }
    return ($a['FileName'] < $b['FileName']) ? -1 : 1;
}
while ($page <= $pageAll) {
    $pageFile = $grdPath . '/page_' . $page . '.json';
    $url = "https://bill.tncc.gov.tw/NoPaperMeeting_TNCC/api/WEB014_GetProposalList.ashx?topage={$page}&pagerow=100&grdno={$grdno}";
    $c = json_decode(file_get_contents($url));
    file_put_contents($pageFile, json_encode($c, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    $json = json_decode(file_get_contents($pageFile), true);
    foreach ($json as $item) {
        if (false === $pageAllDone) {
            $pageAllDone = true;
            $pageAll = $item['PagesAll'];
        }
        $motionFile = $grdPath . '/case_' . $item['ProNo'] . '.json';
        $c = json_decode(file_get_contents("https://bill.tncc.gov.tw/NoPaperMeeting_TNCC/Api/WEB016_GetOneProposal.ashx?prono={$item['ProNo']}"), true);
        if (!empty($c['GradeTime'])) {
            foreach ($c as $k => $v) {
                if (is_array($c[$k])) {
                    usort($c[$k], "cmp");
                }
            }

            file_put_contents($motionFile, json_encode($c, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        }
    }
    $page++;
}
