<?php
// src/Legacy/LegacyController.php

namespace Legacy;

/**
 * DEPRECATED
 */
use Legacy\AddParams;
use Legacy\Helps;
use Legacy\Leaderboard;
use Legacy\Qids;
use Legacy\SelectHelps;
use Legacy\Sql;

use function API\Missing\exists_by_lang_and_category;
use function API\Missing\exists_statics_by_category;
use function API\Missing\missing_by_lang_and_category;
use function API\Missing\statics_by_category;
use function API\TitlesInfos\mdwiki_revids;
use function API\TitlesInfos\pages_query;
use function API\TitlesInfos\titles_query;
use function API\Top\top_langs;
use function API\Top\top_lang_of_users;
use function API\Top\top_users;
use App\Endpoints\Definition\EndpointDefinitions;

class LegacyController
{

    function enabled(string $key): bool
    {
        return isset($_GET[$key]) && $_GET[$key] !== 'false' && $_GET[$key] !== '0';
    }

    /**
     * Main entry point
     */
    public function handleRequest(): void
    {
        header('Content-Type: application/json');

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
        ];

        $DISTINCT = $this->enabled('distinct') ? 'DISTINCT ' : '';
        $get = filter_input(INPUT_GET, 'get', FILTER_SANITIZE_FULL_SPECIAL_CHARS); //$_GET['get']

        $qua = "";
        $query = "";
        $params = [];

        $error_results = [];
        $execution_time = 0;

        // load endpoint_params.json
        $endpoint_params_tab = EndpointDefinitions::alltoArray();
        $endpoint_data = $endpoint_params_tab[$get] ?? [];

        $endpoint_params = $endpoint_data['params'] ?? [];
        $endpoint_columns = $endpoint_data['columns'] ?? [];

        $SELECT = SelectHelps::get_select($endpoint_params, $endpoint_columns);

        $get_group_value = filter_input(INPUT_GET, 'group', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        $error = "";

        /**
         * Already in EndpointRegistry.php
         */
        switch ($get) {
            case 'missing':
            case 'missing_by_lang_and_category':
                [$query, $params, $error] = missing_by_lang_and_category($endpoint_params);
                break;

            case 'exists_statics_by_category':
                [$query, $params, $error] = exists_statics_by_category($endpoint_params);

            case 'exists_by_lang_and_category':
                [$query, $params, $error] = exists_by_lang_and_category($endpoint_params);
                break;

            case 'statics_by_category':
                [$query, $params, $error] = statics_by_category($endpoint_params);
                break;

            case 'revids':
                [$query, $params, $error] = mdwiki_revids($endpoint_params);
                break;

            case 'titles':
                [$query, $params, $error] = titles_query($endpoint_params);
                break;

            case 'top_lang_of_users':
                [$query, $params, $error] = top_lang_of_users($endpoint_params);
                break;

            case 'top_langs':
                [$query, $params, $error] = top_langs($endpoint_params);
                break;

            case 'top_users':
                [$query, $params, $error] = top_users($endpoint_params);
                break;

            case 'users': // now at UsersHandler.php
                $query = "SELECT username FROM users";
                if ($this->enabled('userlike')) {
                    $added = filter_input(INPUT_GET, 'userlike', FILTER_SANITIZE_SPECIAL_CHARS);
                    if ($added !== null) {
                        $query .= " WHERE username like ?";
                        $params[] = "$added%";
                    }
                }
                break;

            case 'category_members': // now at CategoryMembersHandler.php
                $cat = "RTT";
                $query = "SELECT article_id FROM category_members";
                if (isset($_GET['cat'])) {
                    $input_cat = filter_input(INPUT_GET, 'cat', FILTER_SANITIZE_SPECIAL_CHARS);
                    if ($input_cat !== null) {
                        $cat = $input_cat;
                    }
                }
                $query .= " WHERE category = ?";
                $params[] = $cat;
                break;

            case 'coordinators':
                $qua = "SELECT id, username, is_active FROM coordinators order by id";
                $qua = Helps::add_limit($qua);
                break;

            case 'langs':
                $qua = 'SELECT code, autonym, name, redirects FROM langs';
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

            case 'user_access':
                $query = "SELECT id, user_name, created_at FROM access_keys";
                [$query, $params] = AddParams::add_li_params($query, [], $endpoint_params);
                break;

            case 'views': // now at ViewsHandler.php
            case 'views_new':
                $query = <<<SQL
                        SELECT p.title, v.target, v.lang, v.views
                        FROM views_new_all v
                        LEFT JOIN pages p
                            ON p.target = v.target
                            AND p.lang = v.lang
                SQL;
                [$query, $params] = AddParams::add_li_params($query, [], $endpoint_params);
                $query .= " ORDER BY 1 DESC";
                break;

            case 'lang_views': // now at ViewsHandler.php
            case 'lang_views2':
                if ($this->enabled('lang')) {
                    $query = <<<SQL
                        SELECT p.title, v.target, v.lang, v.views
                        FROM views_new_all v
                        LEFT JOIN pages p
                            ON p.target = v.target
                            AND p.lang = v.lang
                    SQL;
                    [$query, $params] = AddParams::add_li_params($query, [], $endpoint_params);
                }
                break;

            case 'user_views': // now at ViewsHandler.php
            case 'user_views2':
                if ($this->enabled('user')) {
                    $query = <<<SQL
                        SELECT p.title, v.target, v.lang, v.views
                        FROM views_new_all v
                        LEFT JOIN pages p
                            ON p.target = v.target
                            AND p.lang = v.lang
                    SQL;
                    [$query, $params] = AddParams::add_li_params($query, [], $endpoint_params);
                }
                break;

            case 'leaderboard_table':          // now at LeaderboardHandler.php
            case 'leaderboard_table_formated': // now at LeaderboardHandler.php

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

                [$query, $params] = AddParams::add_li_params($query, [], $endpoint_params);
                $query .= " ORDER BY 1 DESC";
                break;

            case 'qids':
            case 'qids_others':
                $qua = Qids::qids_qua($get);
                break;

            case 'pages_users_to_main': // now at PagesUsersToMainHandler.php
                $query = "SELECT pum.id, pum.new_target, pum.new_user, pum.new_qid
                    FROM pages_users_to_main pum, pages_users pu
                    where pum.id = pu.id
                ";
                $params = [];
                if ($this->enabled('lang')) {
                    $added = filter_input(INPUT_GET, 'lang', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                    if ($added !== null) {
                        $query .= " AND pu.lang = ?";
                        $params[] = $added;
                    }
                }
                break;

            case 'language_settings':
                $query = "SELECT DISTINCT * FROM language_settings";
                [$query, $params] = AddParams::add_li_params($query, [], $endpoint_params);
                break;

            case 'words':
                $params = [];
                $query = "SELECT w_id, w_title, w_lead_words, w_all_words FROM words ";
                [$query, $params] = AddParams::add_li_params($query, [], $endpoint_params);
                break;

            case 'count_pages':
                $query = "SELECT DISTINCT user, count(target) as count from pages";
                [$query, $params] = AddParams::add_li_params($query, [], $endpoint_params);
                $query .= " group by user order by count desc";
                break;

            case 'publish_reports_stats':
                $query = <<<SQL
                    SELECT DISTINCT YEAR(date) as year, MONTH(date) as month, lang, user, result
                    FROM publish_reports
                    GROUP BY year, month, lang, user, result
                SQL;
                [$query, $params] = AddParams::add_li_params($query, [], $endpoint_params);
                break;

            case 'in_process':

                $qua = <<<SQL
                    SELECT title, user, lang, cat, translate_type, word, add_date, ca.campaign, la.autonym
                    from in_process
                    LEFT JOIN categories ca ON cat = ca.category
                    LEFT JOIN langs la ON lang = la.code
                SQL;
                [$query, $params] = AddParams::add_li_params($qua, [], $endpoint_params);
                $query = Helps::add_group($query, $endpoint_data, $get_group_value);
                break;

            case 'pages_with_views': // now at PagesWithViewsHandler.php
                $_qua = <<<SQL
                    from pages p
                    WHERE p.target != ''
                SQL;

                [$query, $params] = AddParams::add_li_params($_qua, [], $endpoint_params);
                $query_start = <<<SQL
                    select distinct
                        p.id, p.title, p.word, p.translate_type, p.cat,
                        p.lang, p.user, p.target, p.date, p.pupdate,
                        p.add_date, p.deleted, p.mdwiki_revid,
                        (select v.views from views_new_all v WHERE p.target = v.target AND p.lang = v.lang) as views
                SQL;

                $query = $query_start . $query;
                $query = Helps::add_group($query, $endpoint_data, $get_group_value);
                break;

            case 'pages_by_user_or_lang': // now at PagesByUserOrLangHandler.php

                $qua = <<<SQL
                    SELECT DISTINCT p.title, p.word, p.translate_type, p.cat, p.lang, p.user, p.target, p.date,
                    p.pupdate, p.add_date, p.deleted, v.views
                    FROM pages p
                    LEFT JOIN views_new_all v
                        ON p.target = v.target
                        AND p.lang = v.lang
                SQL;

                [$query, $params] = AddParams::add_li_params($qua, [], $endpoint_params, ['year']);

                if (isset($_GET['year'])) {
                    $added = filter_input(INPUT_GET, 'year', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                    $added = (int) $added;
                    if ($added && $added > 0) {
                        // $query .= " AND (YEAR(p.date) = ? OR YEAR(p.pupdate) = ? OR YEAR(p.add_date) = ?)";
                        // $params[] = $added;
                        // $params[] = $added;
                        // $params[] = $added;
                        $query .= " AND ? IN (YEAR(p.date), YEAR(p.pupdate), YEAR(p.add_date))";
                        $params[] = $added;
                    }
                }
                $query = Helps::add_group($query, $endpoint_data, $get_group_value);
                break;

            case 'get_lang_years':
                $qua = "SELECT DISTINCT YEAR(p.pupdate) as year
                    FROM pages p
                    LEFT JOIN categories ca ON p.cat = ca.category
                ";
                [$query, $params] = AddParams::add_li_params($qua, [], $endpoint_params);
                break;

            case 'user_status': // now at UserStatusHandler.php

                $SELECT = ($SELECT == "*" || $SELECT == "year") ? "YEAR(p.pupdate) as year" : $SELECT;
                $qua = "SELECT DISTINCT $SELECT
                    FROM pages p
                    LEFT JOIN categories ca ON p.cat = ca.category
                ";

                [$query, $params] = AddParams::add_li_params($qua, [], $endpoint_params);
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
                    ORDER BY pupdate DESC
                SQL;
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

            case 'pages':
            case 'pages_users':
                [$query, $params, $error] = pages_query($endpoint_params, $SELECT, $DISTINCT, $get);
                break;

            /**
             * Above Already in EndpointRegistry.php
             */

            case 'publish_reports':
                $query = <<<SQL
                    SELECT $DISTINCT $SELECT
                    FROM publish_reports
                    SQL;
                [$query, $params] = AddParams::add_li_params($query, [], $endpoint_params);

                break;

            default:
                if (in_array($get, $other_tables) || !empty($endpoint_data)) {
                    $query = "SELECT $DISTINCT $SELECT FROM $get";
                    [$query, $params] = AddParams::add_li_params($query, [], $endpoint_params);
                    break;
                }
                $error_results = ["error" => "invalid get request"];
                break;
        }

        $source = "db";

        $results = [];

        if ($qua !== "" || $query !== "") {

            $start_time = microtime(true);

            if ($query !== "") {

                $order_value = filter_input(INPUT_GET, 'order', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $query = Helps::add_order($query, $endpoint_data, $order_value);

                $query = Helps::add_limit($query);
                $query = Helps::add_offset($query);

                // apply $params to $qua
                $qua = sprintf(str_replace('?', "'%s'", $query), ...$params);

                list($results, $source) = Sql::fetch_query_new($query, $params, $get);
            } else {
                $qua = Helps::add_limit($qua);
                $qua = Helps::add_offset($qua);

                list($results, $source) = Sql::fetch_query_new($qua, [], $get);
            }

            $end_time = microtime(true);

            $execution_time = $end_time - $start_time;
            $execution_time = number_format($execution_time, 2);
        }

        $qua = str_replace(["\n", "\r"], " ", $qua);
        $qua = preg_replace("/ +/", " ", $qua);

        switch ($get) {
            case 'leaderboard_table_formated':
                $results = Leaderboard::leaderboard_table_format($results);
                break;

            case 'langs':
                $results = Leaderboard::langs_format($results);
                break;
        }

        $out = [
            "time"             => $execution_time,
            "query"            => $qua,
            "source"           => $source,
            "length"           => count($results),
            "results"          => $results,
            // "endpoint_params" => $endpoint_params,
            "supported_params" => [],
            "supported_values" => [],
        ];

        if ($error) {
            $error_results = ["error" => $error];
        }

        if ($error_results) {
            $out["error"] = $error_results;
        }
        // if server is localhost then add query to out
        if ($_SERVER['SERVER_NAME'] !== 'localhost') {
            // remove query from $out
            unset($out["query"]);
        }

        $out["supported_params"] = array_column($endpoint_params, "name");

        $out["supported_values"] = array_column($endpoint_params, "options", 'name');
        $out["columns"] = $endpoint_columns;

        echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    }
}
