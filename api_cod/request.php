<?php

if (isset($_REQUEST['test'])) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}
header('Content-Type: application/json');

include_once __DIR__ . '/include.php';

use function API\Langs\get_lang_names_new;
use function API\Langs\get_lang_names;
use function API\SQL\fetch_query;
use function API\InterWiki\get_inter_wiki;
use function API\SiteMatrix\get_site_matrix;
use function API\Helps\sanitize_input;
use function API\Helps\add_li;
use function API\Helps\add_li_params;
use function API\Helps\add_limit;
use function API\Pages\get_pages_qua;
use function API\Qids\qids_qua;
use function API\Leaderboard\leaderboard_table;
use function API\Leaderboard\leaderboard_table_new;
use function API\Status\make_status_query;
use function API\TitlesInfos\titles_query;

$other_tables = [
    'assessments',
    'refs_counts',
    'enwiki_pageviews',
    'categories',
    'full_translators',
    'projects',
    'settings',
    'translate_type'
];

$DISTINCT = (isset($_GET['distinct'])) ? 'DISTINCT ' : '';
$get = filter_input(INPUT_GET, 'get', FILTER_SANITIZE_SPECIAL_CHARS); //$_GET['get']

// if (!isset($_GET['limit'])) $_GET['limit'] = '50';

$qua = "";
$query = "";
$params = [];
$results = [];
$execution_time = 0;

$select_valids = [
    'count(title) as count',
    'YEAR(date) AS year',
    'YEAR(pupdate) AS year',
    'lang',
    'user',
];

$SELECT = (isset($_GET['select'])) ? filter_input(INPUT_GET, 'select', FILTER_SANITIZE_SPECIAL_CHARS) : '*';

if (!in_array($SELECT, $select_valids)) {
    $SELECT = '*';
};

// load endpoint_params.json
$endpoint_params = json_decode(file_get_contents(__DIR__ . '/../endpoint_params.json'), true);
$endpoint_params = $endpoint_params[$get]['params'] ?? [];
// ---
switch ($get) {
    case 'users':
        $qua = "SELECT username FROM users";
        if (isset($_GET['userlike'])) {
            $added = filter_input(INPUT_GET, 'userlike', FILTER_SANITIZE_SPECIAL_CHARS);
            if ($added !== null) {
                $qua .= " WHERE username like '$added%'";
            }
        }
        $qua = add_limit($qua);
        break;

    case 'titles':
        $tab = titles_query($endpoint_params);
        $query = $tab['qua'];
        $params = $tab['params'];
        // echo json_encode($tab);
        $query = add_limit($query);
        break;

    case 'coordinator':
        $qua = "SELECT $SELECT FROM coordinator";
        $qua = add_limit($qua);
        break;

    case 'leaderboard_table':
        $de = leaderboard_table();
        $query = $de["qua"];
        $params = $de["params"];

        break;

    case 'leaderboard_table_new':
        $de = leaderboard_table_new();
        $query = $de["qua"];
        $params = $de["params"];

        break;

    case 'status':
        $status = make_status_query();
        $query = $status['qua'];
        $params = $status['params'];
        break;

    case 'views':
        $qua = "SELECT * FROM views ";
        $qua = add_li($qua, ['lang']);
        $qua = add_limit($qua);
        break;

    case 'user_access':
        $qua = "SELECT id, user_name, created_at FROM access_keys";
        $qua = add_li($qua, ['user_name']);
        $qua = add_limit($qua);
        break;

    case 'qids':
        $qua = qids_qua($get);
        $qua = add_limit($qua);
        break;

    case 'qids_others':
        $qua = qids_qua($get);
        $qua = add_limit($qua);
        break;

    case 'count_pages':
        // $target_t = (isset($_GET['target_empty'])) ? " target = '' " : " target != '' ";
        // $qua = "SELECT DISTINCT user, count(target) as count from pages where $target_t group by user order by count desc";
        $qua = "SELECT DISTINCT user, count(target) as count from pages";
        $qua = add_li($qua, [], $endpoint_params);
        $qua .= " group by user order by count desc";
        $qua = add_limit($qua);
        break;

    case 'users_by_last_pupdate_old':
        $qua = <<<SQL
            select DISTINCT p1.target, p1.title, p1.cat, p1.user, p1.pupdate, p1.lang
            from pages p1
            where target != ''
            and p1.pupdate = (select p2.pupdate from pages p2 where p2.user = p1.user ORDER BY p2.pupdate DESC limit 1)
            group by p1.user
            ORDER BY p1.pupdate DESC
        SQL;
        $qua = add_limit($qua);
        break;

    case 'users_by_last_pupdate':
        $qua = <<<SQL
            WITH RankedPages AS (
                SELECT
                    p1.target,
                    p1.user,
                    p1.pupdate,
                    p1.lang,
                    ROW_NUMBER() OVER (PARTITION BY p1.user ORDER BY p1.pupdate DESC) AS rn
                FROM pages p1
                WHERE p1.target != ''
            )
            SELECT target, user, pupdate, lang
            FROM RankedPages
            WHERE rn = 1
            ORDER BY pupdate DESC;
        SQL;
        $qua = add_limit($qua);
        break;

    case 'lang_names':
        $results = get_lang_names();
        break;

    case 'lang_names_new':
        $results = get_lang_names_new();
        break;

    case 'inter_wiki':
        $ty = sanitize_input($_GET['type'] ?? 'all', '/^[a-zA-Z ]+$/');
        $results = get_inter_wiki($ty);
        break;

    case 'site_matrix':
        $ty = sanitize_input($_GET['type'] ?? 'all', '/^[a-zA-Z ]+$/');
        $results = get_site_matrix($ty);
        break;

    case 'user_views':
        if (isset($_GET['user'])) {
            $user_name = filter_input(INPUT_GET, 'user', FILTER_SANITIZE_SPECIAL_CHARS);
            $qua = <<<SQL
                    select p.target, v.countall
                from pages p, views v
                where p.user = '{$user_name}'
                and p.lang = v.lang
                and p.target = v.target
            SQL;
            $qua = add_limit($qua);
        };
        break;

    case 'graph_data':
        $qua = <<<SQL
            SELECT LEFT(pupdate, 7) as m, COUNT(*) as c
            FROM pages
            WHERE target != ''
            GROUP BY LEFT(pupdate, 7)
            ORDER BY LEFT(pupdate, 7) ASC
        SQL;
        $qua = add_limit($qua);
        break;

    case 'lang_views':
        if (isset($_GET['lang'])) {
            $lang = filter_input(INPUT_GET, 'lang', FILTER_SANITIZE_SPECIAL_CHARS);
            $qua = <<<SQL
                    select p.target, v.countall
                from pages p, views v
                where p.lang = '{$lang}'
                and p.lang = v.lang
                and p.target = v.target
            SQL;
            $qua = add_limit($qua);
        };
        break;

    case 'words':
        $params = [];
        $query = "SELECT * FROM words WHERE 1=1";
        // ---
        $tab = add_li_params($query, [], $endpoint_params);
        // ---
        $query = $tab['qua'];
        $params = $tab['params'];
        // ---
        /*
        // التحقق من عنوان الكلمات
        $title = sanitize_input($_GET['title'] ?? '', '/^[a-zA-Z0-9\s_-]+$/');
        if ($title !== null) {
            $query .= " AND w_title = ?";
            $params[] = $title;
        }

        // التحقق من عدد كلمات المقدمة
        $lead_words = sanitize_input($_GET['lead_words'] ?? '', '/^\d+$/');
        if ($lead_words !== null) {
            $query .= " AND w_lead_words = ?";
            $params[] = $lead_words;
        }

        // التحقق من عدد كل الكلمات
        $all_words = sanitize_input($_GET['all_words'] ?? '', '/^\d+$/');
        if ($all_words !== null) {
            $query .= " AND w_all_words = ?";
            $params[] = $all_words;
        }
        */
        $query = add_limit($query);
        break;

    case 'pages':
    case 'pages_users':
        $qua = get_pages_qua($get, $DISTINCT, $SELECT);
        $qua = add_limit($qua);
        break;

    default:
        if (in_array($get, $other_tables)) {
            $qua = "SELECT * FROM $get";
            $qua = add_li($qua, [], $endpoint_params);
            $qua = add_limit($qua);
            break;
        }
        $results = ["error" => "invalid get request"];
        break;
}

if ($results === [] && ($qua !== "" || $query !== "")) {
    $start_time = microtime(true);
    if ($query !== "") {
        // apply $params to $qua
        $qua = sprintf(str_replace('?', "'%s'", $query), ...$params);
        $results = fetch_query($query, $params);
    } else {
        $results = fetch_query($qua);
    }
    $end_time = microtime(true);
    $execution_time = $end_time - $start_time;
    $execution_time = number_format($execution_time, 2);
}

$qua = str_replace(["\n", "\r"], " ", $qua);
$qua = preg_replace("/ +/", " ", $qua);

$out = [
    "time" => $execution_time,
    // "query" => $qua,
    "length" => count($results),
    "results" => $results
];

// if server is localhost then add query to out
if ($_SERVER['SERVER_NAME'] === 'localhost') {
    $out = [
        "query" => $qua,
        "time" => $execution_time,
        "length" => count($results),
        "results" => $results
    ];
};
$out["supported_params"] = [];
foreach ($endpoint_params as $param) {
    $out["supported_params"][] = $param["name"];
};

echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
