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
    $lang_raw     = $_GET['lang'] ?? null;
    $campaign_raw = $_GET['campaign'] ?? null;
    $category_raw = $_GET['category'] ?? $_GET['cat'] ?? null;
    // ---
    $lang_code   = sanitize_input($lang_raw ?? '', '/^[A-Za-z0-9-]+$/');
    // ---
    if ($lang_code === null) {
        $error = "lang is missing";
        return ["", [], $error];
    };
    // ---
    $params = [$lang_code];
    // ---
    $campaign   = sanitize_input($campaign_raw ?? '', '/^[A-Za-z0-9-]+$/');
    $category   = sanitize_input($category_raw ?? '', '/^[A-Za-z0-9-]+$/');
    // ---
    if ($category === null && $campaign !== null) {
        $qua .= " AND aa.category IN (SELECT category FROM categories WHERE campaign = ?)";
        $params[] = $campaign;
    } elseif ($category !== null) {
        $qua .= " AND aa.category = ?";
        $params[] = $category;
    }
    // ---
    return [$qua, $params, ""];
    // ---
}

function exists_statics_by_category($endpoint_params)
{
    // ---
    $category_raw = $_GET['category'] ?? $_GET['cat'] ?? null;
    // ---
    $category   = sanitize_input($category_raw ?? '', '/^[A-Za-z0-9-]+$/');
    // ---
    if ($category === null) {
        $category = "RTT";
    }
    // ---
    $qua = <<<SQL
        SELECT
            la.code AS language_code,
            la.autonym AS autonym,
            la.name AS language_name,
            count(*) AS total,
            SUM(CASE WHEN aq.target IS NULL THEN 1 ELSE 0 END) AS missing_title_count,
            SUM(CASE WHEN aq.target IS NOT NULL THEN 1 ELSE 0 END) AS available_title_count
        FROM
            category_members c

        JOIN langs la
        LEFT JOIN qids q                ON q.title = c.article_id
        LEFT JOIN all_qids_exists aq    ON aq.qid = q.qid AND la.code = aq.code

        WHERE
            c.category = ?
        GROUP BY 1, 2, 3
        ORDER BY 4 ASC;
    SQL;
    // ---
    $params = [$category];
    // ---
    return [$qua, $params, ""];
    // ---
}

function missing_by_lang_and_category($endpoint_params)
{
    // ---
    $lang_raw     = $_GET['lang'] ?? null;
    $category_raw = $_GET['category'] ?? $_GET['cat'] ?? null;
    // ---
    $lang_code  = sanitize_input($lang_raw ?? '', '/^[A-Za-z0-9-]+$/');
    $category   = sanitize_input($category_raw ?? '', '/^[A-Za-z0-9-]+$/');
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
        LEFT JOIN all_qids_exists aq    ON aq.qid       = q.qid AND aq.code = ?

        LEFT JOIN assessments ase       ON ase.title    = c.article_id
        LEFT JOIN enwiki_pageviews ep   ON ep.title     = c.article_id
        LEFT JOIN refs_counts rc        ON rc.r_title   = c.article_id
        LEFT JOIN words w               ON w.w_title    = c.article_id
        WHERE
            c.category = ?
        AND aq.target IS NULL

        /* to work with valid langs */
        AND EXISTS ( SELECT 1 FROM langs la WHERE la.code = ? )
    SQL;
    // ---
    $params = [$lang_code, $category, $lang_code];
    // ---
    return [$qua, $params, $error];
    // ---
}


function exists_by_lang_and_category($endpoint_params)
{
    // ---
    $lang_raw     = $_GET['lang'] ?? null;
    $category_raw = $_GET['category'] ?? $_GET['cat'] ?? null;
    // ---
    $lang_code  = sanitize_input($lang_raw ?? '', '/^[A-Za-z0-9-]+$/');
    $category   = sanitize_input($category_raw ?? '', '/^[A-Za-z0-9-]+$/');
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
        LEFT JOIN all_qids_exists aq    ON aq.qid = q.qid AND aq.code = ?

        LEFT JOIN assessments ase       ON ase.title    = c.article_id
        LEFT JOIN enwiki_pageviews ep   ON ep.title     = c.article_id
        LEFT JOIN refs_counts rc        ON rc.r_title   = c.article_id
        LEFT JOIN words w               ON w.w_title    = c.article_id
        WHERE
            c.category = ?
        AND aq.target IS NOT NULL

        /* to work with valid langs */
        AND EXISTS ( SELECT 1 FROM langs la WHERE la.code = ? )
    SQL;
    // ---
    $params = [$lang_code, $category, $lang_code];
    // ---
    return [$qua, $params, ""];
    // ---
}
