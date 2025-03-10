<?php

namespace API\Missing;
/*
Usage:
use function API\Missing\missing_query;



*/

// use function API\Helps\add_li_params;

function missing_query($endpoint_params)
{
    // ---
    $query = <<<SQL
        SELECT a.article_id
            FROM all_articles a
            WHERE NOT EXISTS (
                SELECT 1
                FROM all_exists t
                WHERE t.article_id = a.article_id

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
    return ["qua" => $query, "params" => $params];
}

function missing_qids_query($endpoint_params)
{
    // ---
    $query = <<<SQL
        SELECT a.qid
            FROM all_qids a
            WHERE NOT EXISTS (
                SELECT 1
                FROM all_qidsexists t
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
    return ["qua" => $query, "params" => $params];
}
