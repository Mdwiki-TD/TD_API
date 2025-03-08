<?php

namespace API\Missing;
/*
Usage:
use function API\Missing\missing_query;



*/

use function API\Helps\add_li_params;


function missing_query($endpoint_params)
{
    // ---
    $query = <<<SQL
        SELECT a.article_id
        FROM all_articles a
        LEFT JOIN all_exists t
            ON a.article_id = t.article_id
    SQL;
    // ---
    // $tab = add_li_params($query, [], $endpoint_params);
    // // ---
    // $query = $tab['qua'];
    // $params = $tab['params'];
    // // ---
    $params = [];
    if (isset($_GET['lang'])) {
        $added = filter_input(INPUT_GET, 'lang', FILTER_SANITIZE_SPECIAL_CHARS);
        if ($added !== null) {
            $query .= " AND t.code = ?";
            $params[] = $added;
        }
    }
    // ---
    $query .= " \n WHERE t.title IS NULL \n ";
    // ---
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


function missing_query_not($endpoint_params)
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

function missing_query1($endpoint_params)
{
    $qua = <<<SQL
        SELECT
            ase.title,
            ase.importance,
            rc.r_lead_refs,
            rc.r_all_refs,
            ep.en_views,
            w.w_lead_words,
            w.w_all_words,
            q.qid
        FROM assessments ase
        JOIN enwiki_pageviews ep ON ase.title = ep.title
        JOIN qids q ON q.title = ase.title
        JOIN refs_counts rc ON rc.r_title = ase.title
        JOIN words w ON w.w_title = ase.title
    SQL;
    // ---
    $tab = add_li_params($qua, [], $endpoint_params);
    // ---
    $qua = $tab['qua'];
    $params = $tab['params'];
    // ---
    return ["qua" => $qua, "params" => $params];
}
