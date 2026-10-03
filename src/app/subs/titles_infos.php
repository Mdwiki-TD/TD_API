<?php

namespace API\TitlesInfos;

use function API\Helps\add_li_params;
use function API\Helps\sanitize_input;

function titles_query($endpoint_params)
{

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
            left join enwiki_pageviews ep   on ep.title   = ase.title
            left join qids q                on q.title    = ase.title
            left join refs_counts rc        on rc.r_title = ase.title
            left join words w               on w.w_title  = ase.title
    SQL;

    list($qua, $params) = add_li_params($qua, [], $endpoint_params);

    return [$qua, $params, ""];
}

function mdwiki_revids($endpoint_params)
{

    $qua = <<<SQL
        SELECT title, revid
        FROM mdwiki_revids
    SQL;

    list($qua, $params) = add_li_params($qua, [], $endpoint_params);

    return [$qua, $params, ""];
}

function pages_query($endpoint_params, $SELECT, $DISTINCT, $get)
{

    $select = ($SELECT == "*") ? "title, word, translate_type, cat, lang, user, target, date, pupdate, add_date, deleted, mdwiki_revid, campaign" : $SELECT;

    $qua = <<<SQL
        SELECT $DISTINCT $select
        FROM $get p
        LEFT JOIN categories ca ON p.cat = ca.category
    SQL;

    [$query, $params] = add_li_params($qua, [], $endpoint_params, ['campaign', 'cat', 'category']);

    $campaign_raw = $_GET['campaign'] ?? null;
    $category_raw = $_GET['category'] ?? $_GET['cat'] ?? null;

    $campaign   = sanitize_input($campaign_raw ?? '', '/^[A-Za-z0-9- ]+$/');
    $category   = sanitize_input($category_raw ?? '', '/^[A-Za-z0-9- ]+$/');

    if ($category !== null) {
        $query .= " AND p.cat = ?";
        $params[] = $category;
    } elseif ($campaign !== null) {
        // $query .= " AND p.cat IN (SELECT category FROM categories WHERE campaign = ?)";
        $query .= " AND ca.campaign = ?";
        $params[] = $campaign;
    }

    return [$query, $params, ""];
}
