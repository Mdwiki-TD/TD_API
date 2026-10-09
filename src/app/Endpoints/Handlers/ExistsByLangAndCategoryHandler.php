<?php
// src/app/Endpoints/Handlers/ExistsByLangAndCategoryHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;

/*
replace the old exists_by_lang_and_category
*/
use App\Endpoints\Definition\EndpointDefinition;
use App\Endpoints\{EndpointContext, QuerySpec};

function exists_by_lang_and_category($endpoint_params)
{

    $lang_raw     = $_GET['lang'] ?? null;
    $category_raw = $_GET['category'] ?? $_GET['cat'] ?? null;

    $lang_code = Helps::sanitize_input($lang_raw ?? '', '/^[A-Za-z0-9- ]+$/');
    $category  = Helps::sanitize_input($category_raw ?? '', '/^[A-Za-z0-9- ]+$/');

    if ($lang_code === null) {
        $error = "lang is missing";
        return ["", [], $error];
    }

    if ($category === null) {
        $category = "RTT";
    }

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

        AND EXISTS ( SELECT 1 FROM langs la WHERE la.code = ? )
    SQL;
    /* to work with valid langs */

    $params = [$lang_code, $category, $lang_code];

    return [$qua, $params, ""];
}

final class ExistsByLangAndCategoryHandler extends CategoryLangHandler
{
    public function definition(): EndpointDefinition
    {
        return new EndpointDefinition(
            summary: 'Retrieve exists statics by language and category',
            tag: 'pages_infos',
            params: [self::langParam(), self::categoryParam()],
        );
    }

    public function handle(EndpointContext $ctx): QuerySpec
    {
        $lang = $this->lang($ctx);
        if ($lang === null) {
            return new QuerySpec(error: 'lang is missing');
        }

        $sql = 'SELECT
                c.article_id AS title, c.category AS category, ase.importance,
                rc.r_lead_refs, rc.r_all_refs, ep.en_views, q.qid,
                w.w_lead_words, w.w_all_words, aq.target
            FROM ' . self::ARTICLE_JOINS . '
            WHERE c.category = ?
              AND aq.target IS NOT NULL
              AND EXISTS (SELECT 1 FROM langs la WHERE la.code = ?)';

        return new QuerySpec($sql, [$lang, $this->category($ctx), $lang]);
    }
}
