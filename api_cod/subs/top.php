<?php

namespace API\Top;
/*
Usage:
use function API\Top\top_langs;
use function API\Top\top_users;
use function API\Top\langs_names;



*/

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

function top_users($endpoint_params)
{
    // ---
    $query = <<<SQL
        SELECT
            p.user,
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

        LEFT JOIN users u
            ON p.user = u.username

        LEFT JOIN words w
            ON w.w_title = p.title

        LEFT JOIN views_new_all v
            ON p.target = v.target AND p.lang = v.lang

        WHERE p.target != '' AND p.target IS NOT NULL
        AND p.user != '' AND p.user IS NOT NULL
        SQL;
    // ---
    $tab = add_li_params($query, [], $endpoint_params, [""]);
    // ---
    $params = $tab['params'];
    $query = $tab['qua'];
    // ---
    /*
    $cat   = sanitize_input($_GET['cat'] ?? '', '/^[a-zA-Z ]+$/');
    if ($cat !== null) {
        $query .= " AND p.title IN (SELECT article_id FROM articles_cats WHERE category = ?)";
        $params[] = $cat;
    }
    // ---
    */
    $query .= " GROUP BY p.user ORDER BY 2 DESC";
    // ---
    return ["qua" => $query, "params" => $params];
}

function top_langs($endpoint_params)
{
    // ---
    $query = <<<SQL
        SELECT
            p.lang,
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

        LEFT JOIN users u
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
    $tab = add_li_params($query, [], $endpoint_params, [""]);
    // ---
    $params = $tab['params'];
    $query = $tab['qua'];
    // ---
    /*
    $cat   = sanitize_input($_GET['cat'] ?? '', '/^[a-zA-Z ]+$/');
    if ($cat !== null) {
        $query .= " AND p.title IN (SELECT article_id FROM articles_cats WHERE category = ?)";
        $params[] = $cat;
    }
    // ---
    */
    $query .= " GROUP BY p.lang ORDER BY 2 DESC";
    // ---
    return ["qua" => $query, "params" => $params];
}
