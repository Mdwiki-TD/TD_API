<?php

namespace API\Langs;
/*
Usage:
use function API\Langs\get_url_result_curl;

use function API\Langs\get_lang_names_all;
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

$lang_tables = [];
// load langs_table.json
if (file_exists(__DIR__ . '/langs_table.json')) {
    $lang_tables = json_decode(file_get_contents(__DIR__ . '/langs_table.json'), true);
    ksort($lang_tables);
}

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
    global $usr_agent;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // curl_setopt($ch, CURLOPT_COOKIEJAR, "cookie.txt");
    // curl_setopt($ch, CURLOPT_COOKIEFILE, "cookie.txt");

    curl_setopt($ch, CURLOPT_USERAGENT, $usr_agent);

    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);

    $output = curl_exec($ch);
    if ($output === FALSE) {
        echo ("<br>cURL Error: " . curl_error($ch) . "<br>$url");
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

    $url = "https://www.wikidata.org/w/api.php?" . http_build_query($params);

    $result = get_url_result_curl($url);

    $result = json_decode($result, true);

    return $result['query']['wbcontentlanguages'];
}
function get_langs_list()
{
    $url = "https://cxserver.wikimedia.org/v2/list/languagepairs";

    $pairs = get_url_result_curl($url);

    $results = json_decode($pairs, true);
    $source = $results['source'];
    $target = $results['target'];

    $results = array_merge($source, $target);
    $results = array_unique($results);

    sort($results);

    // del "simple" and "en"
    $results = array_diff($results, ['simple', 'en']);

    return $results;
};

function get_lang_names()
{
    global $lang_tables;

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

    ksort($pairs);

    foreach ($pairs as $pair) {
        $data = ["code" => $pair, "autonym" => "", "name" => ""];

        $results[$pair] = $names[$pair] ?? $data;
    };
    return $results;
};
