<?php

namespace API\Langs;
/*
Usage:
use function API\Langs\get_url_result_curl;

use function API\Langs\get_lang_names;
*/

$print_t = false;

if (isset($_REQUEST['test'])) {
    $print_t = true;
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

define('print_te', $print_t);

function test_print($s)
{
    if (print_te && gettype($s) == 'string') {
        echo "\n<br>\n$s";
    } elseif (print_te) {
        echo "\n<br>\n";
        print_r($s);
    }
}

function get_url_result_curl(string $url): string
{
    $usr_agent = "WikiProjectMed Translation Dashboard/1.0 (https://mdwiki.toolforge.org/; tools.mdwiki@toolforge.org)";

    $ch = curl_init($url);
    // ---
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT => $usr_agent,
        CURLOPT_CONNECTTIMEOUT => 7,
        CURLOPT_TIMEOUT => 13,
        // CURLOPT_COOKIEJAR => "cookie.txt",
        // CURLOPT_COOKIEFILE => "cookie.txt",
    ]);
    // ---
    $output = curl_exec($ch);
    // ---
    $url = "<a target='_blank' href='$url'>$url</a>";
    //---
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    //---
    if ($http_code !== 200) {
        test_print('post_url_mdwiki: Error: API request failed with status code ' . $http_code);
    }
    //---
    test_print("post_url_mdwiki: (http_code: $http_code) $url");
    // ---
    if ($output === FALSE) {
        test_print("post_url_mdwiki: cURL Error: " . curl_error($ch));
    }

    if (curl_errno($ch)) {
        test_print('post_url_mdwiki: Error:' . curl_error($ch));
    }

    curl_close($ch);
    return $output;
}

function get_names()
{
    $params  = [
        "action" => "query",
        "format" => "json",
        "meta" => "wbcontentlanguages",
        "formatversion" => "2",
        "wbclcontext" => "monolingualtext",
        "wbclprop" => "code|autonym|name"
    ];

    $url = "https://www.wikidata.org/w/api.php?" . http_build_query($params, '', '&', PHP_QUERY_RFC3986);

    $result = get_url_result_curl($url);

    $result = json_decode($result, true);

    return $result['query']['wbcontentlanguages'] ?? [];
}

function get_langs_list()
{
    $url = "https://cxserver.wikimedia.org/v2/list/languagepairs";

    $pairs = get_url_result_curl($url);

    $results = json_decode($pairs, true) ?: [];
    $source = $results['source'] ?? [];
    $target = $results['target'] ?? [];

    $results = array_merge($source, $target);
    $results = array_unique($results);

    sort($results);

    // del "simple" and "en"
    $results = array_diff($results, ['simple', 'en']);

    return $results;
};

function get_lang_names()
{
    $lang_tables = [];
    // load langs_table.json
    if (file_exists(__DIR__ . '/langs_table.json')) {
        $lang_tables = json_decode(file_get_contents(__DIR__ . '/langs_table.json'), true) ?: [];
        ksort($lang_tables);
    }
    return $lang_tables;
};

function get_lang_names_new()
{
    $pairs = [
        "aa",
        "ab",
        "ace",
        "ady",
        "af",
        "ak",
        "als",
        "alt",
        "am",
        "ami",
        "an",
        "ang",
        "anp",
        "ar",
        "arc",
        "ary",
        "arz",
        "as",
        "ast",
        "atj",
        "av",
        "avk",
        "awa",
        "ay",
        "az",
        "azb",
        "ba",
        "ban",
        "bar",
        "bat-smg",
        "bbc",
        "bcl",
        "bdr",
        "be",
        "be-tarask",
        "bew",
        "bg",
        "bh",
        "bho",
        "bi",
        "bjn",
        "blk",
        "bm",
        "bn",
        "bo",
        "bpy",
        "br",
        "bs",
        "btm",
        "bug",
        "bxr",
        "ca",
        "cbk-zam",
        "cdo",
        "ce",
        "ceb",
        "ch",
        "cho",
        "chr",
        "chy",
        "ckb",
        "co",
        "cr",
        "crh",
        "cs",
        "csb",
        "cu",
        "cv",
        "cy",
        "da",
        "dag",
        "de",
        "dga",
        "din",
        "diq",
        "dsb",
        "dtp",
        "dty",
        "dv",
        "dz",
        "ee",
        "el",
        "eml",
        "en",
        "eo",
        "es",
        "et",
        "eu",
        "ext",
        "fa",
        "fat",
        "ff",
        "fi",
        "fiu-vro",
        "fj",
        "fo",
        "fon",
        "fr",
        "frp",
        "frr",
        "fur",
        "fy",
        "ga",
        "gag",
        "gan",
        "gcr",
        "gd",
        "gl",
        "glk",
        "gn",
        "gom",
        "gor",
        "got",
        "gpe",
        "gsw",
        "gu",
        "guc",
        "gur",
        "guw",
        "gv",
        "ha",
        "hak",
        "haw",
        "he",
        "hi",
        "hif",
        "ho",
        "hr",
        "hsb",
        "ht",
        "hu",
        "hy",
        "hyw",
        "hz",
        "ia",
        "iba",
        "id",
        "ie",
        "ig",
        "igl",
        "ii",
        "ik",
        "ilo",
        "inh",
        "io",
        "is",
        "it",
        "iu",
        "ja",
        "jam",
        "jbo",
        "jv",
        "ka",
        "kaa",
        "kab",
        "kbd",
        "kbp",
        "kcg",
        "kg",
        "kge",
        "ki",
        "kj",
        "kk",
        "kl",
        "km",
        "kn",
        "ko",
        "koi",
        "kr",
        "krc",
        "ks",
        "ksh",
        "ku",
        "kus",
        "kv",
        "kw",
        "ky",
        "la",
        "lad",
        "lb",
        "lbe",
        "lez",
        "lfn",
        "lg",
        "li",
        "lij",
        "lld",
        "lmo",
        "ln",
        "lo",
        "lrc",
        "lt",
        "ltg",
        "lv",
        "lzh",
        "mad",
        "mai",
        "map-bms",
        "mdf",
        "mg",
        "mh",
        "mhr",
        "mi",
        "min",
        "mk",
        "ml",
        "mn",
        "mni",
        "mnw",
        "mos",
        "mr",
        "mrj",
        "ms",
        "mt",
        "mus",
        "mwl",
        "my",
        "myv",
        "mzn",
        "na",
        "nah",
        "nan",
        "nap",
        "nb",
        "nds",
        "nds-nl",
        "ne",
        "new",
        "ng",
        "nia",
        "nl",
        "nn",
        "no",
        "nov",
        "nqo",
        "nrm",
        "nso",
        "nv",
        "ny",
        "oc",
        "olo",
        "om",
        "or",
        "os",
        "pa",
        "pag",
        "pam",
        "pap",
        "pcd",
        "pcm",
        "pdc",
        "pfl",
        "pi",
        "pih",
        "pl",
        "pms",
        "pnb",
        "pnt",
        "ps",
        "pt",
        "pwn",
        "qu",
        "rm",
        "rmy",
        "rn",
        "ro",
        "roa-rup",
        "roa-tara",
        "rsk",
        "ru",
        "rue",
        "rup",
        "rw",
        "sa",
        "sah",
        "sat",
        "sc",
        "scn",
        "sco",
        "sd",
        "se",
        "sg",
        "sgs",
        "sh",
        "shi",
        "shn",
        "si",
        "simple",
        "sk",
        "skr",
        "sl",
        "sm",
        "smn",
        "sn",
        "so",
        "sq",
        "sr",
        "srn",
        "ss",
        "st",
        "stq",
        "su",
        "sv",
        "sw",
        "szl",
        "szy",
        "ta",
        "tay",
        "tcy",
        "tdd",
        "te",
        "tet",
        "tg",
        "th",
        "ti",
        "tk",
        "tl",
        "tly",
        "tn",
        "to",
        "tpi",
        "tr",
        "trv",
        "ts",
        "tt",
        "tum",
        "tw",
        "ty",
        "tyv",
        "udm",
        "ug",
        "uk",
        "ur",
        "uz",
        "ve",
        "vec",
        "vep",
        "vi",
        "vls",
        "vo",
        "vro",
        "wa",
        "war",
        "wo",
        "wuu",
        "xal",
        "xh",
        "xmf",
        "yi",
        "yo",
        "yue",
        "za",
        "zea",
        "zgh",
        "zh",
        "zh-classical",
        "zh-min-nan",
        "zh-yue",
        "zu"
    ];

    $lang_names2 = get_langs_list();

    $pairs = array_unique(array_merge($pairs, $lang_names2));

    $names = get_names();

    $results = array();

    foreach ($pairs as $pair) {
        $data = ["code" => $pair, "autonym" => "", "name" => ""];

        $results[$pair] = $names[$pair] ?? $data;
    };

    ksort($results);

    return $results;
};
