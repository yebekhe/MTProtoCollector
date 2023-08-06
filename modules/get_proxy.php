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
        !filtered_or_not("https://" . $query_params["server"])
    ) {
        foreach ($query_params as $key => $value) {
            if (stripos($key, "@") !== false) {
                unset($query_params[$key]);
                break; 
            }
        }
        $ip_data = ip_info($query_params["server"]);
        if (isset($ip_data["country"])) {
            $location = $ip_data["country"];
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
    $exception = ["alephproxy"];
    $key_limit = in_array($source, $exception) ? count(getProxies($source)) - 9 : count(getProxies($source)) - 2;
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

function remove_duplicate($input)
{
    $new_proxy_array = [];
    foreach ($input as $proxy_data) {
        $name = $proxy_data["query"]["name"];
        unset($proxy_data["query"]["name"]);
        $key = serialize($proxy_data["query"]);
        $new_proxy_array[$key][] = $name;
    }
    $output = [];
    $query = [];
    $counter = 0;
    foreach ($new_proxy_array as $query_params => $name_array) {
        $query = unserialize($query_params);
        $query["name"] = $name_array[0];
        $output[$counter]["scheme"] = "https";
        $output[$counter]["host"] = "t.me";
        $output[$counter]["path"] = "/proxy";
        $output[$counter]["query"] = $query;
        $output[$counter]["link"] =
            $output[$counter]["scheme"] .
            "://" .
            $output[$counter]["host"] .
            $output[$counter]["path"] .
            "?server=" .
            $output[$counter]["query"]["server"] .
            "&port=" .
            $output[$counter]["query"]["port"] .
            "&secret=" .
            $output[$counter]["query"]["secret"] .
            "&" .
            $output[$counter]["query"]["name"];
        $counter++;
    }
    return $output;
}

?>
