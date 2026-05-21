<?php

namespace API\Missing;
/*

Usage:
use function API\Missing\missing_query;
use function API\Missing\exists_by_qids_query;

*/

use function API\Helps\add_li_params;
use function API\Helps\sanitize_input;

function missing_query($endpoint_params)
{
    // ---
    $query = <<<SQL
        SELECT a.qid, a.title, a.category
            FROM all_articles_titles a
            WHERE NOT EXISTS (
                SELECT 1
                FROM all_exists t
                WHERE t.article_id = a.title

    SQL;
    $params = [];
    if (isset($_GET['lang'])) {
        $added = filter_input(INPUT_GET, 'lang', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if ($added !== null) {
            $query .= " AND t.code = ?";
            $params[] = $added;
        }
    }
    $query .= ")";
    if (isset($_GET['category'])) {
        $added = filter_input(INPUT_GET, 'category', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if ($added !== null) {
            $query .= " AND a.category = ?";
            $params[] = $added;
        }
    }
    // ---
    return [$query, $params];
}

function missing_by_qids_query($endpoint_params)
{
    // ---
    $query = <<<SQL
        SELECT a.qid, a.title, a.category
            FROM all_qids_titles a
            WHERE NOT EXISTS (
                SELECT 1
                FROM all_qids_exists t
                WHERE t.qid = a.qid

    SQL;
    $params = [];
    if (isset($_GET['lang'])) {
        $added = filter_input(INPUT_GET, 'lang', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if ($added !== null) {
            $query .= " AND t.code = ?";
            $params[] = $added;
        }
    }
    $query .= ")";
    if (isset($_GET['category'])) {
        $added = filter_input(INPUT_GET, 'category', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if ($added !== null) {
            $query .= " AND a.category = ?";
            $params[] = $added;
        }
    }
    // ---
    return [$query, $params];
}


function exists_by_qids_query($endpoint_params)
{
    // ---
    // exists_by_qids
    // ---
    $qua = <<<SQL
        SELECT a.qid, a.title, a.category, t.code, t.target
            FROM all_qids_titles a
            JOIN all_qids_exists t
            ON t.qid = a.qid
    SQL;
    // ---
    list($qua, $params) = add_li_params($qua, [], $endpoint_params);
    // ---
    $campaign   = sanitize_input($_GET['campaign'] ?? '', '/^[a-zA-Z ]+$/');
    $category   = sanitize_input($_GET['category'] ?? '', '/^[a-zA-Z ]+$/');
    // ---
    if ($category === null && $campaign !== null) {
        $qua .= " AND a.category IN (SELECT category FROM categories WHERE campaign = ?)";
        $params[] = $campaign;
    }
    // ---
    return [$qua, $params];
    // ---
}


function missing_exists_statics($endpoint_params)
{
    // ---
    $category   = sanitize_input($_GET['category'] ?? '', '/^[a-zA-Z ]+$/');
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
        LEFT JOIN langs la ON la.code = a.code
        WHERE
            a.article_id IN (
                SELECT c.article_id
                FROM category_members c
                WHERE c.category = ?
            )
        GROUP BY
            a.code, la.autonym, la.name, total.total_rtt
        ORDER BY
            available_title_count DESC;
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
    $lang_code  = sanitize_input($_GET['lang'] ?? '', '/^[a-zA-Z ]+$/');
    $category   = sanitize_input($_GET['category'] ?? '', '/^[a-zA-Z ]+$/');
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
            c.article_id
        FROM
            category_members c
        WHERE
            c.category = ?
            AND NOT EXISTS (
                SELECT
                    1
                FROM
                    all_exists t
                WHERE
                    t.article_id = c.article_id
                AND
                t.code = ?
            )
    SQL;
    // ---
    $params = [$category, $lang_code];
    // ---
    return [$qua, $params, $error];
    // ---
}
