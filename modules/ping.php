<?php
function ping($ip, $port)
{
    $it = microtime(true);
    $check = @fsockopen($ip, $port, $errno, $errstr, 1);
    $ft = microtime(true);
    $militime = round(($ft - $it) * 1e3, 2);
    if ($check) {
        fclose($check);
        return $militime;
    } else {
        return "unavailable";
    }
}

function filtered_or_not($input){
    $check_host_url = "https://3smdj6-8080.csb.app/?host=";
    $check_host_data = json_decode(file_get_contents($check_host_url . $input), true);
    $average_ping = 0;
    $ping_count = 0;
    $precent = [100, 66, 33, 0];
    if (!is_null($check_host_data)){
        $ping_count = count($check_host_data);
        $output = $precent[$ping_count] >= 33 ? true : false ;
    }
    return $output;
}
?>
