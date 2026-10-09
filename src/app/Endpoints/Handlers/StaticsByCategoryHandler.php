<?php
// src/app/Endpoints/Handlers/StaticsByCategoryHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;

/*
replace the old statics_by_category
*/
use App\Endpoints\Definition\EndpointDefinition;
use App\Endpoints\EndpointContext;
use App\Endpoints\QuerySpec;

final class StaticsByCategoryHandler extends CategoryLangHandler
{
    public function definition(): EndpointDefinition
    {
        return new EndpointDefinition(
            summary: 'Retrieve the number of available titles per language for a category',
            tag: 'pages_infos',
            params: [self::categoryParam()],
        );
    }

    public function handle(EndpointContext $ctx): QuerySpec
    {
        $sql = 'SELECT aq.code AS language_code, COUNT(*) AS available_title_count
            FROM category_members c
            JOIN qids q ON q.title = c.article_id
            JOIN all_qids_exists aq ON aq.qid = q.qid
            WHERE c.category = ?
            GROUP BY aq.code';

        return new QuerySpec($sql, [$this->category($ctx)], defaultOrder: 'available_title_count ASC');
    }
}
