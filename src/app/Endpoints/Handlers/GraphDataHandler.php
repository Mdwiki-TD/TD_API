<?php
// src/app/Endpoints/Handlers/GraphDataHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;

use App\Endpoints\{EndpointContext, EndpointHandler, QuerySpec};

final class GraphDataHandler implements EndpointHandler
{
    public function handle(EndpointContext $ctx): QuerySpec
    {
        $sql = "SELECT DISTINCT LEFT(p.pupdate, 7) AS date, COUNT(*) AS count
            FROM pages p
            LEFT JOIN categories ca ON p.cat = ca.category
            WHERE p.target != ''
        ";

        [$sql, $params] = $ctx->applyFilters($sql, ['campaign', 'cat', 'category']);

        // Apply campaign/category filters
        [$sql, $params] = $ctx->applyCampaignCategory($sql, $params);

        $sql .= " GROUP BY LEFT(p.pupdate, 7)";

        return new QuerySpec($sql, $params, defaultOrder: 'date ASC');
    }
}
