<?php
// src/app/Endpoints/Handlers/UserDataStatusHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;

use App\Endpoints\{AbstractEndpointHandler, EndpointContext, QuerySpec};

final class UserDataStatusHandler extends AbstractEndpointHandler
{
    public function handle(EndpointContext $ctx): QuerySpec
    {
        $sql = "SELECT DISTINCT
            p.lang, p.user, ca.campaign, p.cat, YEAR(p.pupdate) AS year, COUNT(p.target) AS count
            FROM pages p
            LEFT JOIN categories ca ON p.cat = ca.category";

        [$sql, $params] = $ctx->applyFilters($sql);
        $sql .= " GROUP BY lang, user, campaign, cat, year";

        return new QuerySpec($sql, $params, defaultOrder: 'count DESC');
    }
}
