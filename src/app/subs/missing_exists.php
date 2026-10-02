<?php

namespace API\Missing;
/*

Usage:
use function API\Missing\exists_statics_by_category;
use function API\Missing\missing_by_lang_and_category;
use function API\Missing\exists_by_lang_and_category;
use function API\Missing\statics_by_category;

*/

use function API\Helps\sanitize_input;

function exists_statics_by_category($endpoint_params)
{
    // ---
    $category_raw = $_GET['category'] ?? $_GET['cat'] ?? null;
    // ---
    $category   = sanitize_input($category_raw ?? '', '/^[A-Za-z0-9- ]+$/');
    // ---
    if ($category === null) {
        $category = "RTT";
    }
    // ---
    $qua = <<<SQL
        SELECT
            la.code AS language_code,
            la.autonym,
            la.name AS language_name,

            COUNT(*) AS total,

            COUNT(aq.qid) AS available_title_count,

            COUNT(*) - COUNT(aq.qid) AS missing_title_count

        FROM langs la

        CROSS JOIN (
            SELECT DISTINCT article_id
            FROM category_members
            WHERE category = ?
        ) c

        LEFT JOIN qids q
            ON q.title = c.article_id

        LEFT JOIN all_qids_exists aq
            ON aq.qid = q.qid
        AND aq.code = la.code

        GROUP BY
            la.code,
            la.autonym,
            la.name

        ORDER BY available_title_count ASC;
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
    $lang_code  = sanitize_input($lang_raw ?? '', '/^[A-Za-z0-9- ]+$/');
    $category   = sanitize_input($category_raw ?? '', '/^[A-Za-z0-9- ]+$/');
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
    $lang_code  = sanitize_input($lang_raw ?? '', '/^[A-Za-z0-9- ]+$/');
    $category   = sanitize_input($category_raw ?? '', '/^[A-Za-z0-9- ]+$/');
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

function statics_by_category($endpoint_params)
{
    // ---
    $category_raw = $_GET['category'] ?? $_GET['cat'] ?? null;
    // ---
    $category   = sanitize_input($category_raw ?? '', '/^[A-Za-z0-9- ]+$/');
    // ---
    if ($category === null) {
        $category = "RTT";
    }
    // ---
    $qua = <<<SQL
        SELECT
            aq.code AS language_code,
            COUNT(*) AS available_title_count
        FROM
            category_members c
            JOIN qids q ON q.title = c.article_id
            JOIN all_qids_exists aq ON aq.qid = q.qid
        WHERE
            c.category = ?
        GROUP BY
            aq.code
        ORDER BY
            available_title_count ASC;
    SQL;
    // ---
    $params = [$category];
    // ---
    return [$qua, $params, ""];
    // ---
}
