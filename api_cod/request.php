<?php

if (isset($_REQUEST['test'])) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}
header('Content-Type: application/json');

include_once __DIR__ . '/helps.php';
include_once __DIR__ . '/sql.php';
include_once __DIR__ . '/interwiki.php';
include_once __DIR__ . '/lang_pairs.php';
include_once __DIR__ . '/site_matrix.php';
include_once __DIR__ . '/pages.php';
include_once __DIR__ . '/qids.php';

use function API\Langs\get_lang_names_new;
use function API\Langs\get_lang_names;
use function API\SQL\fetch_query;
use function API\InterWiki\get_inter_wiki;
use function API\SiteMatrix\get_site_matrix;
use function API\Helps\sanitize_input;
use function API\Helps\add_li;
use function API\Helps\add_limit;
use function API\Pages\get_pages_qua;
use function API\Qids\qids_qua;

function leaderboard_table()
{
    // ---
    $pa_rams = [];
    // ---
    $qu_ery = "SELECT p.title,
        p.target, p.cat, p.lang, p.word, YEAR(p.pupdate) AS pup_y, LEFT(p.pupdate, 7) as m,
        p.user,
        (SELECT u.user_group FROM users u WHERE p.user = u.username) AS user_group
        FROM pages p
        WHERE p.target != ''
    ";
    // ---
    $user_group = sanitize_input($_GET['user_group'] ?? '', '/^[a-zA-Z ]+$/');
    // ---
    if ($user_group !== null && $user_group !== 'all') {
        // ---
        $qu_ery = "SELECT p.title,
            p.target, p.cat, p.lang, p.word, YEAR(p.pupdate) AS pup_y, p.user, u.user_group, LEFT(p.pupdate, 7) as m
            FROM pages p, users u
            WHERE p.user = u.username
            AND u.user_group = ?
        ";
        // ---
        $pa_rams[] = $user_group;
    };
    // ---
    $year = sanitize_input($_GET['year'] ?? '', '/^\d+$/');
    // ---
    if ($year !== null) {
        $qu_ery .= " AND YEAR(p.pupdate) = ?";
        $pa_rams[] = $year;
    }
    // ---
    $qu_ery = add_limit($qu_ery);
    // ---
    return ["qua" => $qu_ery, "params" => $pa_rams];
}

function make_status_query()
{
    // https://mdwiki.toolforge.org/api.php?get=status&year=2022&user_group=Wiki&campaign=Main

    $qu_ery = <<<SQL
        SELECT LEFT(p.pupdate, 7) as date, COUNT(*) as count
        FROM pages p
        WHERE p.target != ''
    SQL;

    $pa_rams = [];

    $year       = sanitize_input($_GET['year'] ?? '', '/^\d+$/');
    $user_group = sanitize_input($_GET['user_group'] ?? '', '/^[a-zA-Z ]+$/');
    $campaign   = sanitize_input($_GET['campaign'] ?? '', '/^[a-zA-Z ]+$/');

    if ($year !== null) {
        $added = $year;
        $qu_ery .= " AND YEAR(p.pupdate) = ?";
        $pa_rams[] = $added;
    }

    if ($user_group !== null) {
        $qu_ery .= " AND p.user IN (SELECT username FROM users WHERE user_group = ?)";
        $pa_rams[] = $user_group;
    }

    if ($campaign !== null) {
        $qu_ery .= " AND p.cat IN (SELECT category FROM categories WHERE campaign = ?)";
        $pa_rams[] = $campaign;
    }

    $qu_ery .= <<<SQL
        GROUP BY LEFT(p.pupdate, 7)
        ORDER BY LEFT(p.pupdate, 7) ASC;
    SQL;

    return ["qua" => $qu_ery, "params" => $pa_rams];
}

$DISTINCT = (isset($_GET['distinct'])) ? 'DISTINCT ' : '';
$SELECT   = (isset($_GET['select'])) ? filter_input(INPUT_GET, 'select', FILTER_SANITIZE_SPECIAL_CHARS) : '*';
$get = filter_input(INPUT_GET, 'get', FILTER_SANITIZE_SPECIAL_CHARS); //$_GET['get']

// if (!isset($_GET['limit'])) $_GET['limit'] = '50';

$qua = "";
$query = "";
$params = [];
$results = [];
$execution_time = 0;

switch ($get) {
    case 'users':
        $qua = "SELECT username FROM users";
        if (isset($_GET['userlike'])) {
            $added = filter_input(INPUT_GET, 'userlike', FILTER_SANITIZE_SPECIAL_CHARS);
            $qua .= " WHERE username like '$added%'";
        }
        $qua = add_limit($qua);
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
        $qua = qids_qua($get, $dis);
        $qua = add_limit($qua);
        break;

    case 'qids_others':
        $qua = qids_qua($get, $dis);
        $qua = add_limit($qua);
        break;

    case 'count_pages':
        $target_t = (isset($_GET['target_empty'])) ? " target = '' " : " target != '' ";
        $qua = "SELECT DISTINCT user, count(target) as count from pages where $target_t group by user order by count desc";
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

        $query = add_limit($query);
        break;

    default:
        if (in_array($get, ['categories', 'full_translators', 'projects', 'settings', 'translate_type'])) {
            $qua = "SELECT * FROM $get";
            $qua = add_limit($qua);
            break;
        }
        if (in_array($get, ['pages', 'pages_users'])) {
            $qua = get_pages_qua($get, $DISTINCT, $SELECT);
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
    "query" => $qua,
    "length" => count($results),
    "results" => $results
];

echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
