<?php
// src/app/Endpoints/Handlers/MissingByLangAndCategoryHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;

use App\Endpoints\{EndpointContext, QuerySpec};
use App\Endpoints\Definition\EndpointDefinition;

class MissingByLangAndCategoryHandler extends CategoryLangHandler
{
    public function definition(): EndpointDefinition
    {
        return new EndpointDefinition(
            endpoint: 'missing_by_lang_and_category',
            summary: 'Retrieve missing statics by language and category',
            tag: 'pages_infos',
            params: [self::langParamRequired(), self::categoryParam()],
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
                w.w_lead_words, w.w_all_words
            FROM ' . self::ARTICLE_JOINS . '
            WHERE c.category = ?
              AND aq.target IS NULL
              AND EXISTS (SELECT 1 FROM langs la WHERE la.code = ?)';   // يقبل لغات صالحة فقط

        return new QuerySpec($sql, [$lang, $this->category($ctx), $lang]);
    }
}
