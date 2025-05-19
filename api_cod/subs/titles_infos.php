<?php

namespace API\TitlesInfos;
/*
Usage:
use function API\TitlesInfos\titles_query;
*/

use function API\Helps\add_li_params;

$qua_old = <<<SQL
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
    LEFT JOIN enwiki_pageviews ep ON ase.title = ep.title
    LEFT JOIN qids q ON q.title = ase.title
    LEFT JOIN refs_counts rc ON rc.r_title = ase.title
    LEFT JOIN words w ON w.w_title = ase.title
SQL;

function titles_query($endpoint_params)
{
    // ---
    $qua = <<<SQL
        SELECT *
        FROM titles_infos
    SQL;
    // ---
    // remove titles from $endpoint_params { "name": "titles", "column": "titles", "type": "array" }
    $endpoint_params = array_filter($endpoint_params, function ($param) {
        return $param['name'] !== 'titles';
    });
    // ---
    $tab = add_li_params($qua, [], $endpoint_params);
    // ---
    $qua = $tab['qua'];
    $params = $tab['params'];
    // ---
    $where_or_and = (strpos(strtolower($qua), 'where') !== false) ? ' AND ' : ' where ';
    // ---
    // var_export($_GET);
    // ---
    $titles = $_GET['titles'] ?? [];
    // ---
    if (!empty($titles) && is_array($titles)) {
        $placeholders = rtrim(str_repeat('?,', count($titles)), ',');
        $qua .= " $where_or_and title IN ($placeholders)";
        $params = array_merge($params, $titles);
    }
    // ---
    return ["qua" => $qua, "params" => $params];
}
