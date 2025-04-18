<?php
require_once 'init.php';
$ad_key = $_GET["key"] ?? null;
if(empty($ad_key)) {
    exit(0);
}

$get_ad = $dsql->dsql()->table('ads')->where('ad_key',$ad_key)->field('ad_link')->field('ad_clicks')->limit(1)->getRow();
if($get_ad) {
    $ad = $get_ad;
    $ad_link = $ad["ad_link"];
    $ad_clicks = $ad["ad_clicks"];
    $ad_clicks += 1;
    $update_clicks = $dsql->dsql()->table('ads')->set(["ad_clicks" => $ad_clicks])->where('ad_key', $ad_key)->update();
    if($update_clicks) {
        header("location:".$ad_link."");
    }
}else{
    exit(0);
}


