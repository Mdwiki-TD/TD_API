<?php
// src/app/Endpoints/Handlers/PagesByUserOrLangHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;

use App\Endpoints\{AbstractEndpointHandler, EndpointContext, QuerySpec};
use App\Query\FilterBuilder;

final class PagesByUserOrLangHandler extends AbstractEndpointHandler
{
    public function handle(EndpointContext $ctx): QuerySpec
    {
        $base = <<<SQL
            SELECT DISTINCT p.title, p.word, p.translate_type, p.cat, p.lang, p.user, p.target, p.date,
            p.pupdate, p.add_date, p.deleted, v.views
            FROM pages p
            LEFT JOIN views_new_all v
                ON p.target = v.target
                AND p.lang = v.lang
        SQL;

        [$sql, $params] = $ctx->applyFilters($base, ignore: ['year']);

        if ($ctx->request->has('year')) {
            $year = (int) $ctx->request->get('year');
            if ($year > 0) {
                $glue = FilterBuilder::glue($sql);
                $sql .= "$glue ? IN (YEAR(p.date), YEAR(p.pupdate), YEAR(p.add_date))";
                $params[] = $year;
            }
        }

        $sql = $ctx->applyGroup($sql);

        return new QuerySpec($sql, $params);
    }

    public function getParams(): array
    {
        return [
            [
                "name" => "lang",
                "column" => "p.lang",
                "type" => "text",
                "placeholder" => "Language code",
            ],
            [
                "name" => "user",
                "column" => "p.user",
                "type" => "text",
                "placeholder" => "Username",
            ],
            [
                "name" => "year",
                "column" => "YEAR(p.date)",
                "type" => "number",
                "placeholder" => "year of date",
            ],
            [
                "name" => "order",
                "column" => "order",
                "type" => "text",
                "placeholder" => "Order by",
                "no_select" => true,
            ],
        ];
    }
}
