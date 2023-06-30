<?php
include "flag.php";
include "ipinfo.php";
include "ping.php";

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

function parse_proxy($proxy, $name)
{
    $proxy_array = [];
    $url = html_entity_decode($proxy);
    $parts = parse_url($proxy);
    $query_string = str_replace("amp;", "", $parts["query"]);
    parse_str($query_string, $query_params);
    if (
        ping($query_params["server"], $query_params["port"]) !== "unavailable"
    ) {
        foreach ($query_params as $key => $value) {
            if (stripos($key, "@") !== false) {
                unset($query_params[$key]); // remove the old parameter
                break; // exit the loop after processing the first parameter with @ symbol
            }
        }
        $ip_data = ip_info($query_params["server"]);
        if (isset($ip_data["country"])) {
            $location = $ip_info["country"];
            $flag = getFlags($location);
        } else {
            $flag = "🚩";
        }
        $query_params["name"] = "@" . $name . "|" . $flag;
        $proxy_array = $parts;
        unset($proxy_array["query"]);
        $proxy_array["query"] = $query_params;
    }
    return $proxy_array;
}

function proxy_array_maker($source)
{
    $key_limit = count(getProxies($source)) - 3;
    $output = [];
    foreach (getProxies($source) as $key => $proxy) {
        if ($key >= $key_limit) {
            $proxy = "https://t.me/proxy" . $proxy;
            $data = parse_proxy($proxy, $source);
            if ($data === []) {
                null;
            } else {
                $output[$key - $key_limit] = $data;
            }
        }
    }
    return $output;
}

?>
