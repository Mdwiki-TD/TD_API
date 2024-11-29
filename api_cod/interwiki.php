<?php

namespace API\InterWiki;
/*
Usage:
use function API\InterWiki\get_inter_wiki;
*/

use function API\Langs\get_url_result_curl;
use function API\Langs\get_lang_names;

function inter_wiki()
{
    // ---
    $params = [
        "action" => "query",
        "format" => "json",
        "meta" => "siteinfo",
        "formatversion" => "2",
        "siprop" => "interwikimap",
        "sifilteriw" => "local"
    ];
    $end_point = "https://en.wikipedia.org/w/api.php";

    $url = $end_point . "?" . http_build_query($params);

    $result = get_url_result_curl($url);

    $result = json_decode($result, true);

    $data =  $result['query']['interwikimap'];

    return $data;
}

function by_code($data)
{
    $new = [];

    foreach ($data as $item) {
        $url = $item['url'];

        if (strpos($url, '.wikipedia.org') === false) {
            continue;
        }
        $url = str_replace('https://', '', $url);
        // $url = preg_replace('#^https?://#', '', $url);
        // split before .wikipedia.org

        $code = substr($url, 0, strpos($url, '.wikipedia.org'));

        if (isset($new[$code])) {
            continue;
        }
        $item2 = [
            "code" => $code,
            "autonym" => $item['language'] ?? "",
            "bcp47" => $item['bcp47'] ?? "",
        ];
        // ---
        if ($item['prefix'] !== $code && $item['prefix'] !== $item2['bcp47']) {
            $item2['prefix'] = $item['prefix'];
        }
        // ---
        // delete bcp47
        if ($item2['bcp47'] === $code) {
            unset($item2['bcp47']);
        }

        // ---
        $new[$code] = $item2;
    }

    return $new;
}

function filter_data($data)
{
    $skip_t = [
        "tenwiki",
        "wg",
        "wikipedia",
        "wikipediawikipedia",
        // "w",
    ];

    // filter prefix in $skip_t
    $data = array_filter($data, function ($item) use ($skip_t) {
        return !in_array($item['prefix'], $skip_t);
    });

    // filter only if "url" contains "wikipedia.org"
    $data = array_filter($data, function ($item) {
        return strpos($item['url'], 'wikipedia.org') !== false;
    });

    return $data;
}

function filter_last($data)
{
    $u = get_lang_names();
    // return only if not in $u
    $data = array_filter($data, function ($item) use ($u) {
        return !in_array($item['code'], array_keys($u));
    });

    return $data;
}
function filter_codes($data)
{
    $skip_t = [
        "wg-en",
        "ten",
        "test",
        "test2",
    ];

    // filter code in $skip_t
    $data = array_filter($data, function ($item) use ($skip_t) {
        return !in_array($item['code'], $skip_t);
    });

    return $data;
}

function get_inter_wiki($ty)
{
    $data = inter_wiki();
    $data = filter_data($data);

    $data = by_code($data);

    $data = filter_codes($data);

    if ($ty == "only") {
        $data = filter_last($data);
    };

    ksort($data);

    return $data;
};
