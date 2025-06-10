<?php

namespace API\TitlesInfos;
/*
Usage:
use function API\TitlesInfos\titles_query;
use function API\TitlesInfos\mdwiki_revids;
*/

use function API\Helps\add_li_params;
use function API\Helps\add_array_params;

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
    // list($qua, $params) = add_li_params($qua, [], $endpoint_params, ['titles']);
    // ---
    list($qua, $params) = add_li_params($qua, [], $endpoint_params);
    // ---
    // list($qua, $params) = add_array_params($qua, $params, 'titles', 'title');
    // ---
    return [$qua, $params];
}

function mdwiki_revids($endpoint_params)
{
    // ---
    $qua = <<<SQL
        SELECT *
        FROM mdwiki_revids
    SQL;
    // ---
    // list($qua, $params) = add_li_params($qua, [], $endpoint_params, ['titles']);
    // ---
    list($qua, $params) = add_li_params($qua, [], $endpoint_params);
    // ---
    // list($qua, $params) = add_array_params($qua, $params, 'titles', 'title');
    // ---
    return [$qua, $params];
}
