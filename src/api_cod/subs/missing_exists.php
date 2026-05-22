<?php

namespace API\Missing;
/*

Usage:
use function API\Missing\exists_by_qids_query;

*/

use function API\Helps\sanitize_input;

function exists_by_qids_query($endpoint_params)
{
    // ---
    // exists_by_qids
    // ---
    /*
        [
            { "name": "lang", "column": "t.code", "type": "text", "placeholder": "Language code", "no_mt_options": true },
            { "name": "category", "column": "aa.category", "type": "text", "placeholder": "Category", "no_mt_options": true },
            { "name": "campaign", "column": "campaign", "type": "text", "placeholder": "Campaign" },
            { "name": "order", "column": "order", "type": "text", "placeholder": "Order by", "no_select": true }
        ]
      */
    // ---
    $qua = <<<SQL
        SELECT
            t.qid AS qid,
            q.title AS title,
            aa.category AS category,
            t.code AS code,
            t.target AS target
        FROM qids q
            JOIN all_qids_exists t      ON t.qid = q.qid
            LEFT JOIN all_articles aa   ON aa.article_id = q.title
        WHERE t.code = ?

        AND (t.target != '' AND t.target IS NOT NULL)
    SQL;
    // ---
    $lang_code   = sanitize_input($_GET['lang'] ?? '', '/^[A-Za-z0-9-]+$/');
    // ---
    $params = [$lang_code];
    // ---
    $campaign   = sanitize_input($_GET['campaign'] ?? '', '/^[A-Za-z0-9-]+$/');
    $category   = sanitize_input($_GET['category'] ?? '', '/^[A-Za-z0-9-]+$/');
    // ---
    if ($category === null && $campaign !== null) {
        $qua .= " AND aa.category IN (SELECT category FROM categories WHERE campaign = ?)";
        $params[] = $campaign;
    } elseif ($category !== null) {
        $qua .= " AND aa.category = ?";
        $params[] = $category;
    }
    // ---
    return [$qua, $params];
    // ---
}


function missing_exists_statics($endpoint_params)
{
    // ---
    $category   = sanitize_input($_GET['category'] ?? '', '/^[A-Za-z0-9-]+$/');
    // ---
    if ($category === null) {
        $category = "RTT";
    }
    // ---
    $qua = <<<SQL
        SELECT
            a.code AS language_code,
            la.autonym AS autonym,
            la.name AS language_name,
            COUNT(a.article_id) AS available_title_count,
            (total.total_rtt - COUNT(a.article_id)) AS missing_title_count,
            total.total_rtt as total
        FROM
            all_exists a
        CROSS JOIN (
            SELECT COUNT(DISTINCT article_id) AS total_rtt
            FROM category_members
            WHERE category = ?
        ) total
        JOIN langs la ON la.code = a.code
        WHERE
            a.article_id IN (
                SELECT c.article_id
                FROM category_members c
                WHERE c.category = ?
            )
        AND la.autonym IS NOT NULL
        GROUP BY
            a.code, la.autonym, la.name, total.total_rtt
        ORDER BY 4 DESC;
    SQL;
    // ---
    $params = [$category, $category];
    // ---
    return [$qua, $params];
    // ---
}



function exists_statics_by_category($endpoint_params)
{
    // ---
    // NOTE: not ready yet
    // ---
    $category   = sanitize_input($_GET['category'] ?? '', '/^[A-Za-z0-9-]+$/');
    // ---
    if ($category === null) {
        $category = "RTT";
    }
    // ---
    $qua = <<<SQL
        SELECT
            t.code AS language_code,
            la.autonym AS autonym,
            la.name AS language_name,
            COUNT(DISTINCT c.article_id) AS available_title_count,
            (total.total_rtt - COUNT(c.article_id)) AS missing_title_count,
            total.total_rtt as total
        FROM
            category_members c
        CROSS JOIN (
            SELECT COUNT(DISTINCT article_id) AS total_rtt
            FROM category_members
            WHERE category = ?
        ) total
            LEFT JOIN qids q                ON q.title = c.article_id
            INNER JOIN all_exists t         ON t.article_id = c.article_id
            INNER JOIN all_qids_exists aqe  ON aqe.qid = q.qid AND aqe.code = t.code
            JOIN langs la                   ON la.code = t.code
        WHERE
            c.category = ?
        AND la.autonym IS NOT NULL
        GROUP BY
            t.code, la.autonym, la.name, total.total_rtt
        ORDER BY 4 DESC;
    SQL;
    // ---
    $params = [$category, $category];
    // ---
    return [$qua, $params];
    // ---
}

function missing_by_lang_and_category($endpoint_params)
{
    // ---
    $lang_code  = sanitize_input($_GET['lang'] ?? '', '/^[A-Za-z0-9-]+$/');
    $category   = sanitize_input($_GET['category'] ?? '', '/^[A-Za-z0-9-]+$/');
    // ---
    $error = "";
    // ---
    if ($lang_code === null) {
        $error = "lang is missing";
        return ["", [], $error];
    };
    // ---
    if ($category === null) {
        $category = "RTT";
    }
    // ---
    $qua = <<<SQL
        SELECT
            c.article_id AS title,
            c.category AS category,
            ase.importance,
            rc.r_lead_refs,
            rc.r_all_refs,
            ep.en_views,
            q.qid,
            w.w_lead_words,
            w.w_all_words
        FROM
            category_members c

        JOIN qids q                     ON q.title      = c.article_id
        LEFT JOIN assessments ase       ON ase.title    = c.article_id
        LEFT JOIN enwiki_pageviews ep   ON ep.title     = c.article_id
        LEFT JOIN refs_counts rc        ON rc.r_title   = c.article_id
        LEFT JOIN words w               ON w.w_title    = c.article_id

        WHERE
            c.category = ?
        AND NOT EXISTS (
            SELECT 1
            FROM all_qids_exists aqe
            WHERE
                aqe.code = ?
                AND aqe.qid = q.qid
        )
        /* to work with valid langs */
        AND EXISTS ( SELECT 1 FROM langs la WHERE la.code = ? )
    SQL;
    // ---
    $params = [$category, $lang_code, $lang_code];
    // ---
    return [$qua, $params, $error];
    // ---
}


function exists_by_lang_and_category($endpoint_params)
{
    // ---
    $lang_code  = sanitize_input($_GET['lang'] ?? '', '/^[A-Za-z0-9-]+$/');
    $category   = sanitize_input($_GET['category'] ?? '', '/^[A-Za-z0-9-]+$/');
    // ---
    if ($lang_code === null) {
        $error = "lang is missing";
        return ["", [], $error];
    };
    // ---
    if ($category === null) {
        $category = "RTT";
    }
    // ---
    $qua = <<<SQL
        SELECT
            c.article_id AS title,
            c.category AS category,
            ase.importance,
            rc.r_lead_refs,
            rc.r_all_refs,
            ep.en_views,
            q.qid,
            w.w_lead_words,
            w.w_all_words,
            aq.target
        FROM
            category_members c

        JOIN qids q                ON q.title = c.article_id
        JOIN all_qids_exists aq    ON aq.qid = q.qid

        LEFT JOIN assessments ase       ON ase.title    = c.article_id
        LEFT JOIN enwiki_pageviews ep   ON ep.title     = c.article_id
        LEFT JOIN refs_counts rc        ON rc.r_title   = c.article_id
        LEFT JOIN words w               ON w.w_title    = c.article_id
        WHERE
            c.category = ?
        AND aq.code = ?
    SQL;
    // ---
    $params = [$category, $lang_code];
    // ---
    return [$qua, $params, ""];
    // ---
}
