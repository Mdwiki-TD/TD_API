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
        select
            ase.title AS title,
            ase.importance AS importance,
            rc.r_lead_refs AS r_lead_refs,
            rc.r_all_refs AS r_all_refs,
            ep.en_views AS en_views,
            w.w_lead_words AS w_lead_words,
            w.w_all_words AS w_all_words,
            q.qid AS qid
        from
            assessments ase
            left join enwiki_pageviews ep on ase.title = ep.title
            left join qids q on q.title = ase.title
            left join refs_counts rc on rc.r_title = ase.title
            left join words w on w.w_title = ase.title
    SQL;
    // ---
    list($qua, $params) = add_li_params($qua, [], $endpoint_params);
    // ---
    return [$qua, $params];
}

function mdwiki_revids($endpoint_params)
{
    // ---
    $qua = <<<SQL
        SELECT title, revid
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
