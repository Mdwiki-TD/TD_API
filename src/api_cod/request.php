<?php

if (isset($_REQUEST['test'])) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}
header('Content-Type: application/json');

use function API\SQL\fetch_query_new;
use function API\Helps\sanitize_input;
use function API\Helps\add_group;
use function API\Helps\add_li_params;
use function API\Helps\add_order;
use function API\Helps\add_limit;
use function API\Helps\add_offset;
use function API\Qids\qids_qua;
use function API\Leaderboard\leaderboard_table_format;
use function API\Leaderboard\langs_format;
use function API\Status\make_status_query;
use function API\TitlesInfos\titles_query;
use function API\TitlesInfos\mdwiki_revids;
use function API\Missing\exists_by_qids_query;
use function API\Missing\exists_statics_by_category;
use function API\Missing\missing_by_lang_and_category;
use function API\Missing\exists_by_lang_and_category;
use function API\SelectHelps\get_select;
use function API\Top\top_langs;
use function API\Top\top_lang_of_users;
use function API\Top\top_users;
use function API\TitlesInfos\pages_query;

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
$get = filter_input(INPUT_GET, 'get', FILTER_SANITIZE_FULL_SPECIAL_CHARS); //$_GET['get']

// if (!isset($_GET['limit'])) $_GET['limit'] = '50';

$qua = "";
$query = "";
$params = [];

$error_results = [];
$execution_time = 0;

// load endpoint_params.json
$endpoint_params_tab = json_decode(file_get_contents(__DIR__ . '/../endpoint_params.json'), true);
// ---
$endpoint_data = $endpoint_params_tab[$get] ?? [];
// ---
if (isset($endpoint_data['redirect'])) {
    $endpoint_data = $endpoint_params_tab[$endpoint_data['redirect']] ?? [];
};
// ---
$endpoint_params = $endpoint_data['params'] ?? [];
$endpoint_columns = $endpoint_data['columns'] ?? [];
// ---
$SELECT = get_select($endpoint_params, $endpoint_columns);
// ---
$get_group_value = filter_input(INPUT_GET, 'group', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
// ---
$error = "";
// ---
switch ($get) {

    case 'missing':
    case 'missing_by_lang_and_category':
        [$query, $params, $error] = missing_by_lang_and_category($endpoint_params);
        break;

    case 'exists_by_qids':
        [$query, $params, $error] = exists_by_qids_query($endpoint_params);
        break;

    case 'exists_statics_by_category':
        [$query, $params, $error] = exists_statics_by_category($endpoint_params);
        break;

    case 'exists_by_lang_and_category':
        [$query, $params, $error] = exists_by_lang_and_category($endpoint_params);

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
        [$query, $params, $error] = mdwiki_revids($endpoint_params);
        break;

    case 'titles':
        [$query, $params, $error] = titles_query($endpoint_params);
        break;

    case 'pages_users_to_main':
        $query = "SELECT pum.id, pum.new_target, pum.new_user, pum.new_qid FROM pages_users_to_main pum, pages_users pu where pum.id = pu.id";
        $params = [];
        if (isset($_GET['lang']) && $_GET['lang'] != 'false' && $_GET['lang'] != '0') {
            $added = filter_input(INPUT_GET, 'lang', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            if ($added !== null) {
                $query .= " AND pu.lang = ?";
                $params[] = $added;
            }
        }
        break;

    case 'coordinators':
        $qua = "SELECT id, username, is_active FROM coordinators order by id";
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
        [$query, $params] = add_li_params($query, [], $endpoint_params);
        // ---
        // $query .= " \n group by v.target, v.lang";
        $query .= " ORDER BY 1 DESC";
        //---
        break;

    case 'status':
        [$query, $params, $error] = make_status_query($endpoint_params);
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
        [$query, $params] = add_li_params($query, [], $endpoint_params);
        // $query .= " group by v.target, v.lang"; // used with views_new and sum(v.views)
        $query .= " ORDER BY 1 DESC";
        break;

    case 'user_access':
        $query = "SELECT id, user_name, created_at FROM access_keys";
        [$query, $params] = add_li_params($query, [], $endpoint_params);
        break;

    case 'qids':
        $qua = qids_qua($get);
        break;

    case 'qids_others':
        $qua = qids_qua($get);
        break;

    case 'count_pages':
        $query = "SELECT DISTINCT user, count(target) as count from pages";
        [$query, $params] = add_li_params($query, [], $endpoint_params);
        $query .= " group by user order by count desc";
        break;

    case 'top_lang_of_users':
        // ---
        [$query, $params, $error] = top_lang_of_users($endpoint_params);
        // ---
        break;

    case 'top_langs':
        // ---
        [$query, $params, $error] = top_langs($endpoint_params);
        // ---
        break;

    case 'top_users':
        // ---
        [$query, $params, $error] = top_users($endpoint_params);
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

    case 'langs':
        $qua = <<<SQL
            SELECT code, autonym, name, redirects
            FROM langs
        SQL;
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
            [$query, $params] = add_li_params($query, [], $endpoint_params);
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
        [$query, $params] = add_li_params($query, [], $endpoint_params);
        // ---
        break;

    case 'publish_reports_stats':
        $query = <<<SQL
            SELECT DISTINCT YEAR(date) as year, MONTH(date) as month, lang, user, result
            FROM publish_reports
            GROUP BY year, month, lang, user, result
        SQL;
        // ---
        [$query, $params] = add_li_params($query, [], $endpoint_params);
        // ---
        break;

    case 'publish_reports':
        $query = <<<SQL
            SELECT $DISTINCT $SELECT
            FROM publish_reports
            SQL;
        // ---
        [$query, $params] = add_li_params($query, [], $endpoint_params);
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
            [$query, $params] = add_li_params($query, [], $endpoint_params);
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
        [$query, $params] = add_li_params($query, [], $endpoint_params);
        // ---
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
        [$query, $params] = add_li_params($qua, [], $endpoint_params, ['year']);
        // ---
        if (isset($_GET['year'])) {
            $added = filter_input(INPUT_GET, 'year', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $added = (int) $added;
            if ($added && $added > 0) {
                // ---
                // $query .= " AND (YEAR(p.date) = ? OR YEAR(p.pupdate) = ? OR YEAR(p.add_date) = ?)";
                // // ---
                // $params[] = $added;
                // $params[] = $added;
                // $params[] = $added;
                // ---
                $query .= " AND ? IN (YEAR(p.date), YEAR(p.pupdate), YEAR(p.add_date))";
                $params[] = $added;
            }
        }
        $query = add_group($query, $endpoint_data, $get_group_value);
        // ---
        break;

    case 'pages':
    case 'pages_users':
        [$query, $params, $error] = pages_query($endpoint_params, $SELECT, $DISTINCT, $get);
        break;

    case 'pages_langs':
    case 'pages_users_langs':
        $table_name = ($get == 'pages_langs') ? 'pages' : 'pages_users';
        $query = <<<SQL
            SELECT lang, autonym
            FROM $table_name p
            LEFT JOIN langs la ON lang = la.code
            GROUP BY lang
        SQL;
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
        [$query, $params] = add_li_params($qua, [], $endpoint_params);
        // ---
        break;

    case 'pages_with_views':
        // ---
        $_qua = <<<SQL
            from pages p
            WHERE p.target != ''
        SQL;
        // ---
        [$query, $params] = add_li_params($_qua, [], $endpoint_params);
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
        $query = add_group($query, $endpoint_data, $get_group_value);
        // ---
        break;

    case 'in_process':
        // ---
        $qua = <<<SQL
            SELECT title, user, lang, cat, translate_type, word, add_date, ca.campaign, la.autonym
            from in_process
            LEFT JOIN categories ca ON cat = ca.category
            LEFT JOIN langs la ON lang = la.code
        SQL;
        // ---
        [$query, $params] = add_li_params($qua, [], $endpoint_params);
        // ---
        $query = add_group($query, $endpoint_data, $get_group_value);
        // ---
        break;

    default:
        if (in_array($get, $other_tables) || !empty($endpoint_data)) {
            $query = "SELECT $DISTINCT $SELECT FROM $get";
            [$query, $params] = add_li_params($query, [], $endpoint_params);
            break;
        }
        $error_results = ["error" => "invalid get request"];
        break;
}

$source = "db";

$results = [];

if ($qua !== "" || $query !== "") {
    // ---
    $start_time = microtime(true);
    // ---
    if ($query !== "") {
        // ---
        $order_value = filter_input(INPUT_GET, 'order', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $query = add_order($query, $endpoint_data, $order_value);
        // ---
        $query = add_limit($query);
        $query = add_offset($query);
        // ---
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

    case 'langs':
        $results = langs_format($results);
        break;
}

$out = [
    "time" => $execution_time,
    "query" => $qua,
    "source" => $source,
    "length" => count($results),
    "results" => $results,
    // "endpoint_params" => $endpoint_params,
    "supported_params" => [],
    "supported_values" => [],
];

if ($error) $error_results = ["error" => $error];

if ($error_results) {
    $out["error"] = $error_results;
}
// if server is localhost then add query to out
if ($_SERVER['SERVER_NAME'] !== 'localhost') {
    // remove query from $out
    unset($out["query"]);
};

$out["supported_params"] = array_column($endpoint_params, "name");

$out["supported_values"] = array_column($endpoint_params, "options", 'name');
$out["columns"] = $endpoint_columns;
// ---
echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
