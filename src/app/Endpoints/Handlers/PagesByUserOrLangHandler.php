<?php
// src/app/Endpoints/Handlers/PagesByUserOrLangHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;
use App\Query\FilterBuilder;
use App\Endpoints\{EndpointContext, QuerySpec};

use App\Endpoints\DefinedEndpoint;
use App\Endpoints\Definition\Param;
use App\Endpoints\Definition\EndpointDefinition;

final class PagesByUserOrLangHandler implements DefinedEndpoint
{
    public function definition(): EndpointDefinition
    {
        return new EndpointDefinition(
            endpoint: 'pages_by_user_or_lang',
            summary: 'Retrieve pages list by user or language',
            tag: 'pages',
            params: [
                new Param(
                    name: 'lang',
                    column: 'p.lang',
                    placeholder: 'Language code'
                ),
                new Param(
                    name: 'user',
                    column: 'p.user',
                    placeholder: 'Username'
                ),
                new Param(
                    name: 'year',
                    column: 'YEAR(p.date)',
                    type: 'number',
                    placeholder: 'year of date',
                    doc: 'YearParam'
                ),
                new Param(
                    name: 'order',
                    column: 'order',
                    placeholder: 'Order by',
                    noSelect: true
                ),
            ],
        );
    }

    private const BASE = <<<SQL
        SELECT DISTINCT p.title, p.word, p.translate_type, p.cat, p.lang, p.user,
               p.target, p.date, p.pupdate, p.add_date, p.deleted, v.views
        FROM pages p
        LEFT JOIN views_new_all v
            ON p.target = v.target
            AND p.lang = v.lang
        SQL;

    public function handle(EndpointContext $ctx): QuerySpec
    {
        // year يُعالج يدوياً أدناه، فيُستثنى من الفلاتر العامة
        [$sql, $params] = $ctx->applyFilters(self::BASE, ['year']);

        $year = (int) ($ctx->request->get('year') ?? 0);
        if ($year > 0) {
            // لا يوجد WHERE في BASE ما لم تضفه الفلاتر، فنحدد الرابط المناسب
            $glue = FilterBuilder::glue($sql);
            $sql .= "$glue ? IN (YEAR(p.date), YEAR(p.pupdate), YEAR(p.add_date))";
            $params[] = $year;
        }

        $sql = $ctx->applyGroup($sql);
        return new QuerySpec($sql, $params);
    }
}
