<?php

namespace API\TitlesInfos;
/*
Usage:
use function API\TitlesInfos\titles_query;
*/

use function API\Helps\add_li_params;

function titles_query($endpoint_params)
{
    // ---
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
    // ---
    $qua = <<<SQL
        SELECT *
        FROM titles_infos
    SQL;
    // ---
    $tab = add_li_params($qua, [], $endpoint_params);
    // ---
    $qua = $tab['qua'];
    $params = $tab['params'];
    // ---
    return ["qua" => $qua, "params" => $params];
}
