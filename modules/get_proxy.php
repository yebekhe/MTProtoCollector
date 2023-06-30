<?php
include "flag.php";
include "ipinfo.php";
include "ping.php";

<?php
error_reporting(0);
header("Content-type: application/json;");
function getProxies($channel)
{
    $get = file_get_contents("https://t.me/s/" . $channel);
    preg_match_all(
        '#href="(.*?)/proxy?(.*?)" target="_blank" rel="noopener"#',
        $get,
        $prxs
    );
    preg_match_all(
        '#class="tgme_widget_message_inline_button url_button" href="(.*?)/proxy?(.*?)"#',
        $get,
        $in_prxs
    );
    
    return $prxs[2] ?: $in_prxs[2];
}

function parse_proxy($proxy, $name){
    $url = html_entity_decode($proxy);
    $parts = parse_url($proxy);
    $query_string = str_replace("amp;", "", $parts['query']);
    parse_str($query_string, $query_params);
    foreach ($query_params as $key => $value) {
        if (stripos($key, '@') !== false) { 
            unset($query_params[$key]); // remove the old parameter
            break; // exit the loop after processing the first parameter with @ symbol
        }
    }
    $query_params['name'] = $name;
    $proxy_array = $parts;
    unset($proxy_array['query']);
    $proxy_array['query'] = $query_params;
    
    return $proxy_array;
}

function proxy_array_maker($source){
    $key_limit = count(getProxies($source)) - 3;
    $output = [];
    foreach (getProxies($source) as $key => $proxy){
        if ($key >= $key_limit) {
            $proxy = "https://t.me/proxy" . $proxy;
            $output[$key - $key_limit] = parse_proxy($proxy, $source);
       }
   }
   return $output;
}
?>
