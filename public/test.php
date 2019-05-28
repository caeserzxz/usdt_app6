<?php
$str = file_get_contents('http://match.sports.sina.com.cn/football/csl/opta_rank.php?item=shoot&year=2014&lid=8&type=1&dpc=1');
preg_match_all('/\<td\>(.*?)\<\/td\>/i', $str, $arr);
$i = 0;
$narr = [];
$keys = ['排名','球员','球队','射门数','左脚','右脚','头球','其它部位'];
$ik = 0;
foreach ($arr[1] as $key=>$val){
    $narr[$i][$keys[$ik]] = strip_tags($val);
    $ik++;
    if (($key+1) % 8 == 0){
        $i++;
        $ik =0;
    }
}

echo json_encode($narr,JSON_UNESCAPED_UNICODE);