<?php
// src/app/Endpoints/Handlers/ExistsStaticsByCategoryHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;

/*
replace the old exists_statics_by_category
*/
use App\Endpoints\{EndpointContext, QuerySpec};
use App\Endpoints\Definition\EndpointDefinition;

function exists_statics_by_category($endpoint_params)
{

    $category_raw = $_GET['category'] ?? $_GET['cat'] ?? null;

    $category = Helps::sanitize_input($category_raw ?? '', '/^[A-Za-z0-9- ]+$/');

    if ($category === null) {
        $category = "RTT";
    }

    $qua = <<<SQL
        SELECT
            la.code AS language_code,
            la.autonym,
            la.name AS language_name,

            COUNT(*) AS total,

            COUNT(aq.qid) AS available_title_count,

            COUNT(*) - COUNT(aq.qid) AS missing_title_count

        FROM langs la

        CROSS JOIN (
            SELECT DISTINCT article_id
            FROM category_members
            WHERE category = ?
        ) c

        LEFT JOIN qids q
            ON q.title = c.article_id

        LEFT JOIN all_qids_exists aq
            ON aq.qid = q.qid
        AND aq.code = la.code

        GROUP BY
            la.code,
            la.autonym,
            la.name

        ORDER BY available_title_count ASC;
    SQL;

    $params = [$category];

    return [$qua, $params, ""];
}

final class ExistsStaticsByCategoryHandler extends CategoryLangHandler
{
    public function definition(): EndpointDefinition
    {
        // required: true محفوظ كما في JSON القديم (round-trip)، مع أن الكود يرجع إلى RTT عند غيابه
        return new EndpointDefinition(
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
