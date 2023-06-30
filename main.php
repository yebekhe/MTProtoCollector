<?php
include "modules/get_proxy.php";
include "modules/config.php";

$final_data = [];
foreach ($sources as $source) {
    $final_data = array_merge($final_data, proxy_array_maker($source));
}

$proxy_subscription = "";
foreach ($final_data as $proxy_data){
  $proxy_subscription .= $proxy_data['scheme'] . "://" . $proxy_data['host'] . $proxy_data['path'] . "?server=" . $proxy_data['query']['server'] . "&port=" . $proxy_data['query']['port'] . "&secret=" . $proxy_data['query']['secret'] . "&" . $proxy_data['query']['name'] . "\n";
}

file_put_contents("proxy/mtproto", $proxy_subscription);
?>
