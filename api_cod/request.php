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
use function API\SQL\fetch_query_new;
use function API\InterWiki\get_inter_wiki;
use function API\SiteMatrix\get_site_matrix;
use function API\Helps\sanitize_input;
use function API\Helps\add_group;
use function API\Helps\add_li_params;
use function API\Helps\add_order;
use function API\Helps\add_limit;
use function API\Helps\add_offset;
use function API\Qids\qids_qua;
use function API\Leaderboard\leaderboard_table_format;
use function API\Status\make_status_query;
use function API\TitlesInfos\titles_query;
use function API\TitlesInfos\mdwiki_revids;
use function API\Missing\missing_query;
use function API\Missing\exists_by_qids_query;
use function API\Missing\missing_by_qids_query;
use function API\SelectHelps\get_select;
use function API\Top\top_langs;
use function API\Top\top_lang_of_users;
use function API\Top\top_users;
use function API\Top\top_langs_format;

$other_tables = [
    'in_process',
    'assessments',
    'refs_counts',
    'enwiki_pageviews',
    'categories',
    'full_translators',
    'users_no_inprocess',
    'projects',
    'settings',
    'translate_type',
    // 'pages',
    // 'pages_users',
];

$DISTINCT = (isset($_GET['distinct']) && $_GET['distinct'] != 'false' && $_GET['distinct'] != '0') ? 'DISTINCT ' : '';
$get = filter_input(INPUT_GET, 'get', FILTER_SANITIZE_SPECIAL_CHARS); //$_GET['get']

// if (!isset($_GET['limit'])) $_GET['limit'] = '50';

$qua = "";
$query = "";
$params = [];
$results = [];
$execution_time = 0;

// load endpoint_params.json
$endpoint_params_tab = json_decode(file_get_contents(__DIR__ . '/../endpoint_params.json'), true);
$endpoint_params = $endpoint_params_tab[$get]['params'] ?? [];
// ---
if (isset($endpoint_params_tab[$get]['redirect'])) {
    $redirect = $endpoint_params_tab[$get]['redirect'];
    $endpoint_params = $endpoint_params_tab[$redirect]['params'] ?? [];
};
// ---
$SELECT = get_select($endpoint_params);
// ---
switch ($get) {

    case 'missing':
        list($query, $params) = missing_query($endpoint_params);
        break;

    case 'missing_by_qids':
        list($query, $params) = missing_by_qids_query($endpoint_params);
        break;

    case 'exists_by_qids':
        list($query, $params) = exists_by_qids_query($endpoint_params);
        break;

    case 'users':
        $query = "SELECT username FROM users";
        if (isset($_GET['userlike']) && $_GET['userlike'] != 'false' && $_GET['userlike'] != '0') {
            $added = filter_input(INPUT_GET, 'userlike', FILTER_SANITIZE_SPECIAL_CHARS);
            if ($added !== null) {
                $query .= " WHERE username like ?";
                $params[] = "$added%";
            }
        }
        break;

    case 'revids':
        list($query, $params) = mdwiki_revids($endpoint_params);
        break;

    case 'titles':
        list($query, $params) = titles_query($endpoint_params);
        break;

    case 'pages_users_to_main':
        $query = "SELECT pum.id, pum.new_target, pum.new_user, pum.new_qid FROM pages_users_to_main pum, pages_users pu where pum.id = pu.id";
        $params = [];
        if (isset($_GET['lang']) && $_GET['lang'] != 'false' && $_GET['lang'] != '0') {
            $added = filter_input(INPUT_GET, 'lang', FILTER_SANITIZE_SPECIAL_CHARS);
            if ($added !== null) {
                $query .= " AND pu.lang = ?";
                $params[] = $added;
            }
        }
        break;

    case 'coordinator':
        $qua = "SELECT $SELECT FROM coordinator";
        $qua = add_limit($qua);
        break;

    case 'leaderboard_table':
    case 'leaderboard_table_formated':
        // ---
        $query = "SELECT p.title,
            p.target, p.cat, p.lang, p.word, YEAR(p.pupdate) AS pup_y, p.user, u.user_group, LEFT(p.pupdate, 7) as m, v.views
            FROM pages p
            LEFT JOIN users u
                ON p.user = u.username
            LEFT JOIN views_new_all v
                ON p.target = v.target
                AND p.lang = v.lang
            WHERE p.target != ''
        ";
        // ---
        list($query, $params) = add_li_params($query, [], $endpoint_params);
        // ---
        // $query .= " \n group by v.target, v.lang";
        $query .= " ORDER BY 1 DESC";
        //---
        break;

    case 'status':
        list($query, $params) = make_status_query($endpoint_params);
        break;

    case 'views':
    case 'views_new':
        $query = <<<SQL
            SELECT p.title, v.target, v.lang, v.views
            FROM views_new_all v
            LEFT JOIN pages p
                ON p.target = v.target
                AND p.lang = v.lang
        SQL;
        list($query, $params) = add_li_params($query, [], $endpoint_params);
        // $query .= " group by v.target, v.lang"; // used with views_new and sum(v.views)
        $query .= " ORDER BY 1 DESC";
        break;

    case 'user_access':
        $query = "SELECT id, user_name, created_at FROM access_keys";
        list($query, $params) = add_li_params($query, [], $endpoint_params);
        break;

    case 'qids':
        $qua = qids_qua($get);
        break;

    case 'qids_others':
        $qua = qids_qua($get);
        break;

    case 'count_pages':
        $query = "SELECT DISTINCT user, count(target) as count from pages";
        list($query, $params) = add_li_params($query, [], $endpoint_params);
        $query .= " group by user order by count desc";
        break;

    case 'top_lang_of_users':
        // ---
        list($query, $params) = top_lang_of_users($endpoint_params);
        // ---
        break;

    case 'top_langs':
        // ---
        list($query, $params) = top_langs($endpoint_params);
        // ---
        break;

    case 'top_users':
        // ---
        list($query, $params) = top_users($endpoint_params);
        // ---
        break;

    case 'users_by_last_pupdate':
        $qua = <<<SQL
            WITH RankedPages AS (
                SELECT
                    p1.target,
                    p1.user,
                    p1.pupdate,
                    p1.lang,
                    p1.title,
                    ROW_NUMBER() OVER (PARTITION BY p1.user ORDER BY p1.pupdate DESC) AS rn
                FROM pages p1
                WHERE p1.target != ''
            )
            SELECT target, user, pupdate, lang, title
            FROM RankedPages
            WHERE rn = 1
            ORDER BY pupdate DESC;
        SQL;
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
    case 'user_views2':
        if (isset($_GET['user']) && $_GET['user'] != 'false' && $_GET['user'] != '0') {
            $query = <<<SQL
                SELECT p.title, v.target, v.lang, v.views
                FROM views_new_all v
                JOIN pages p
                    ON p.target = v.target
                    AND p.lang = v.lang
            SQL;
            // ---
            list($query, $params) = add_li_params($query, [], $endpoint_params);
            // ---
            // $query .= " GROUP BY v.target, v.lang";
            // ---
        };
        break;

    case 'language_settings':
        $query = <<<SQL
            SELECT DISTINCT *
            FROM language_settings
        SQL;
        // ---
        list($query, $params) = add_li_params($query, [], $endpoint_params);
        // ---
        break;

    case 'publish_reports_stats':
        $query = <<<SQL
            SELECT DISTINCT YEAR(date) as year, MONTH(date) as month, lang, user, result
            FROM publish_reports
            GROUP BY year, month, lang, user, result
        SQL;
        // ---
        list($query, $params) = add_li_params($query, [], $endpoint_params);
        // ---
        break;

    case 'publish_reports':
        $query = <<<SQL
            SELECT $DISTINCT $SELECT
            FROM publish_reports
            SQL;
        // ---
        list($query, $params) = add_li_params($query, [], $endpoint_params);
        // ---
        break;

    case 'lang_views':
    case 'lang_views2':
        if (isset($_GET['lang']) && $_GET['lang'] != 'false' && $_GET['lang'] != '0') {
            $query = <<<SQL
                SELECT v.target, v.lang, v.views
                FROM views_new_all v
                LEFT JOIN pages p
                    ON p.target = v.target
                    AND p.lang = v.lang
            SQL;
            // ---
            list($query, $params) = add_li_params($query, [], $endpoint_params);
            // ---
            // $query .= " GROUP BY v.target, v.lang";
            // ---
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
        break;

    case 'words':
        $params = [];
        $query = "SELECT w_id, w_title, w_lead_words, w_all_words FROM words ";
        // ---
        list($query, $params) = add_li_params($query, [], $endpoint_params);
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
        break;

    case 'pages_by_user_or_lang':
        // ---
        $qua = <<<SQL
            SELECT DISTINCT p.title, p.word, p.translate_type, p.cat, p.lang, p.user, p.target, p.date,
            p.pupdate, p.add_date, p.deleted, v.views
            FROM pages p
            LEFT JOIN views_new_all v
                ON p.target = v.target
                AND p.lang = v.lang
        SQL;
        // ---
        list($query, $params) = add_li_params($qua, [], $endpoint_params);
        // ---
        $query = add_group($query);
        $query = add_order($query);
        // ---
        break;

    case 'pages':
    case 'pages_users':
        // ---
        $qua = "SELECT $DISTINCT $SELECT FROM $get p";
        // ---
        list($query, $params) = add_li_params($qua, [], $endpoint_params, ['campaign', 'title_not_in_pages', 'cat']);
        // ---
        $title_not_in_pages = (isset($_GET['title_not_in_pages']) && $_GET['title_not_in_pages'] != 'false' && $_GET['title_not_in_pages'] != '0') ? true : false;
        // ---
        if ($title_not_in_pages) {
            $query .= " and p.title not in (select p2.title from pages p2 WHERE p2.lang = p.lang and p2.target != '') ";
        }
        // ---
        $campaign   = sanitize_input($_GET['campaign'] ?? '', '/^[a-zA-Z ]+$/');
        $category   = sanitize_input($_GET['cat'] ?? '', '/^[a-zA-Z ]+$/');
        // ---
        if ($category !== null) {
            $query .= " AND p.cat = ?";
            $params[] = $category;
        } elseif ($campaign !== null) {
            $query .= " AND p.cat IN (SELECT category FROM categories WHERE campaign = ?)";
            $params[] = $campaign;
        }
        // ---
        $query = add_group($query);
        $query = add_order($query);
        // ---
        break;

    case 'user_lang_status':
    case 'user_status':
        // ---
        $SELECT = ($SELECT == "*" || $SELECT == "year") ? "YEAR(p.pupdate) as year" : $SELECT;
        // ---
        $qua = "SELECT DISTINCT $SELECT
            FROM pages p
            LEFT JOIN categories ca
            ON p.cat = ca.category
            ";
        // ---
        list($query, $params) = add_li_params($qua, [], $endpoint_params);
        // ---
        // $params = [sanitize_input($_GET['user'] ?? '', '/^[a-zA-Z ]+$/')];
        // ---
        break;

    case 'pages_with_views':
        // ---
        $qua = <<<SQL
            from pages p
            WHERE p.target != ''
        SQL;
        // ---
        list($query, $params) = add_li_params($qua, [], $endpoint_params);
        // ---
        $query_start = <<<SQL
            select distinct
                p.id, p.title, p.word, p.translate_type, p.cat,
                p.lang, p.user, p.target, p.date, p.pupdate,
                p.add_date, p.deleted, p.mdwiki_revid,
                (select v.views from views_new_all v WHERE p.target = v.target AND p.lang = v.lang) as views
        SQL;
        // ---
        $query = $query_start . $query;
        // ---
        // ---
        $query = add_group($query);
        $query = add_order($query);
        // ---
        break;

    case 'in_process':
        // ---
        $qua = <<<SQL
            SELECT $DISTINCT $SELECT from in_process
        SQL;
        // ---
        list($query, $params) = add_li_params($qua, [], $endpoint_params);
        // ---
        $query = add_group($query);
        $query = add_order($query);
        // ---
        break;

    default:
        if (in_array($get, $other_tables) || isset($endpoint_params_tab[$get])) {
            $query = "SELECT $DISTINCT $SELECT FROM $get";
            list($query, $params) = add_li_params($query, [], $endpoint_params);
            break;
        }
        $results = ["error" => "invalid get request"];
        break;
}
$source = "db";

if ($results === [] && ($qua !== "" || $query !== "")) {
    // ---
    $start_time = microtime(true);
    // ---
    if ($query !== "") {
        $query = add_limit($query);
        $query = add_offset($query);
        // apply $params to $qua
        $qua = sprintf(str_replace('?', "'%s'", $query), ...$params);
        // ---
        list($results, $source) = fetch_query_new($query, $params, $get);
    } else {
        $qua = add_limit($qua);
        $qua = add_offset($qua);
        // ---
        list($results, $source) = fetch_query_new($qua, [], $get);
    }
    // ---
    $end_time = microtime(true);
    // ---
    $execution_time = $end_time - $start_time;
    $execution_time = number_format($execution_time, 2);
}

$qua = str_replace(["\n", "\r"], " ", $qua);
$qua = preg_replace("/ +/", " ", $qua);

// ---
switch ($get) {
    case 'leaderboard_table_formated':
        $results = leaderboard_table_format($results);
        break;
    case 'top_langs':
        $results = top_langs_format($results);
        break;
}

$out = [
    "time" => $execution_time,
    "query" => $qua,
    "source" => $source,
    "length" => count($results),
    "results" => $results
];

// if server is localhost then add query to out
if ($_SERVER['SERVER_NAME'] !== 'localhost') {
    // remove query from $out
    unset($out["query"]);
};

$out["supported_params"] = array_column($endpoint_params, "name");

foreach ($endpoint_params as $param) {
    // ---
    if ($param["name"] == "select") {
        $out["supported_select"] = $param["options"] ?? [];
    }
}
// ---
echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
