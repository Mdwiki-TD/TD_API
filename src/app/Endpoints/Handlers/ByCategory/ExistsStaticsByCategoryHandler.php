<?php
// src/app/Endpoints/Handlers/ByCategory/ExistsStaticsByCategoryHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers\ByCategory;

/*
replace the old exists_statics_by_category
*/
use App\Endpoints\{EndpointContext, QuerySpec};
use App\Endpoints\Definition\EndpointDefinition;
use App\Endpoints\Handlers\ByCategory\CategoryLangHandler;


final class ExistsStaticsByCategoryHandler extends CategoryLangHandler
{
    public function definition(): EndpointDefinition
    {
        // required: true saved as in the old JSON, but the code returns RTT when it is missing
        return new EndpointDefinition(
            endpoint: 'exists_statics_by_category',
            summary: 'Retrieve missing statics',
            tag: 'pages_infos',
            params: [self::categoryParam(required: true)],
        );
    }

    public function handle(EndpointContext $ctx): QuerySpec
    {
        $sql = 'SELECT
                la.code AS language_code, la.autonym, la.name AS language_name,
                COUNT(*) AS total,
                COUNT(aq.qid) AS available_title_count,
                COUNT(*) - COUNT(aq.qid) AS missing_title_count
            FROM langs la
            CROSS JOIN (
                SELECT DISTINCT article_id FROM category_members WHERE category = ?
            ) c
            LEFT JOIN qids q ON q.title = c.article_id
            LEFT JOIN all_qids_exists aq ON aq.qid = q.qid AND aq.code = la.code
            GROUP BY la.code, la.autonym, la.name';

        return new QuerySpec($sql, [$this->category($ctx)], defaultOrder: 'available_title_count ASC');
    }
}
