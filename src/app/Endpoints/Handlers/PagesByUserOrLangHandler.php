<?php
// src/app/Endpoints/Handlers/PagesByUserOrLangHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;
use App\Endpoints\Definition\EndpointDefinition;
use App\Query\FilterBuilder;
use App\Endpoints\{EndpointContext, EndpointHandler, QuerySpec};

final class PagesByUserOrLangHandler implements EndpointHandler
{
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
