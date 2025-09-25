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
    // FROM all_articles a
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
        $added = filter_input(INPUT_GET, 'lang', FILTER_SANITIZE_SPECIAL_CHARS);
        if ($added !== null) {
            $query .= " AND t.code = ?";
            $params[] = $added;
        }
    }
    $query .= ")";
    if (isset($_GET['category'])) {
        $added = filter_input(INPUT_GET, 'category', FILTER_SANITIZE_SPECIAL_CHARS);
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
        SELECT a.qid, q.title, q.category
            FROM all_qids_titles a
            WHERE NOT EXISTS (
                SELECT 1
                FROM all_qids_exists t
                WHERE t.qid = a.qid

    SQL;
    $params = [];
    if (isset($_GET['lang'])) {
        $added = filter_input(INPUT_GET, 'lang', FILTER_SANITIZE_SPECIAL_CHARS);
        if ($added !== null) {
            $query .= " AND t.code = ?";
            $params[] = $added;
        }
    }
    $query .= ")";
    if (isset($_GET['category'])) {
        $added = filter_input(INPUT_GET, 'category', FILTER_SANITIZE_SPECIAL_CHARS);
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
    $qua = <<<SQL
        SELECT a.qid, q.title, q.category
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
