<?php

namespace API\Top;
/*
Usage:
use function API\Top\top_langs;
use function API\Top\top_users;
use function API\Top\langs_names;



*/

use function API\Helps\add_array_params;
use function API\Helps\add_li_params;

function langs_names()
{
    $lang_tables = [];
    // load langs_table.json
    $file_path = __DIR__ . '/../langs/langs_table.json';
    if (file_exists($file_path)) {
        $lang_tables = json_decode(file_get_contents($file_path), true);
        ksort($lang_tables);
    }
    return $lang_tables;
}

function top_langs_format($results)
{
    $results2 = [];
    // ---
    $langs_ = langs_names();
    // ---
    foreach ($results as $key => $result) {
        $lang_code = $result['lang'];
        $result['lang_name'] = $langs_[$lang_code]['name'] ?? '';
        // ---
        $results2[] = $result;
    }
    // ---
    return $results2;
}

function top_query($select)
{
    // ---
    $select_field = ($select === 'user') ? 'p.user' : 'p.lang';
    // ---
    $query = <<<SQL
        SELECT
            $select_field,
            COUNT(p.target) AS targets,
            SUM(CASE
                WHEN p.word IS NOT NULL AND p.word != 0 AND p.word != '' THEN p.word
                WHEN translate_type = 'all' THEN w.w_all_words
                ELSE w.w_lead_words
            END) AS words,
            SUM(
                CASE
                    WHEN v.views IS NULL OR v.views = '' THEN 0
                    ELSE CAST(v.views AS UNSIGNED)
                END
                ) AS views

        FROM pages p

        LEFT JOIN users_list u
            ON p.user = u.username

        LEFT JOIN words w
            ON w.w_title = p.title

        LEFT JOIN views_new_all v
            ON p.target = v.target AND p.lang = v.lang

        WHERE p.target != '' AND p.target IS NOT NULL
        AND p.user != '' AND p.user IS NOT NULL
        AND p.lang != '' AND p.lang IS NOT NULL
        SQL;
    // ---
    return $query;
}

function top_users($endpoint_params)
{
    // ---
    $query = top_query('user');
    // ---
    list($query, $params) = add_li_params($query, [], $endpoint_params, []);
    // ---
    $query .= " GROUP BY p.user ORDER BY 2 DESC";
    // ---
    return [$query, $params];
}

function top_langs($endpoint_params)
{
    // ---
    $query = top_query('lang');
    // ---
    list($query, $params) = add_li_params($query, [], $endpoint_params, [""]);
    // ---
    $query .= " GROUP BY p.lang ORDER BY 2 DESC";
    // ---
    return [$query, $params];
}

function top_lang_of_users($endpoint_params)
{
    // ---
    $params = [];
    $query_line = "";
    // ---
    list($query_line, $params) = add_array_params($query_line, $params, 'users', 'p.user', "AND");
    // ---
    $query = <<<SQL
        SELECT user, lang, cnt
        FROM (
            SELECT p.user, p.lang, COUNT(p.target) AS cnt,
                ROW_NUMBER() OVER (PARTITION BY p.user ORDER BY COUNT(p.target) DESC) AS rn
            FROM pages p
            WHERE p.target != ''
            AND p.target IS NOT NULL

            $query_line

            GROUP BY p.user, p.lang
        ) AS ranked
        WHERE rn = 1
        ORDER BY cnt DESC;
    SQL;
    // ---
    return [$query, $params];
}
