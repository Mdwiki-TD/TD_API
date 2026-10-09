<?php
// src/app/Endpoints/Handlers/UserStatusHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;
use App\Endpoints\{EndpointContext, EndpointHandler, QuerySpec};

use App\Endpoints\DefinedEndpoint;
use App\Endpoints\Definition\EndpointDefinition;

final class UserStatusHandler implements EndpointHandler, DefinedEndpoint
{
    public function definition(): EndpointDefinition
    {
        return new EndpointDefinition(
        );
    }

    public function handle(EndpointContext $ctx): QuerySpec
    {
        $select = ($ctx->select === '*' || $ctx->select === 'year')
            ? 'YEAR(p.pupdate) AS year'
            : $ctx->select;

        $sql = "SELECT DISTINCT $select
                FROM pages p
                LEFT JOIN categories ca ON p.cat = ca.category";

        [$sql, $params] = $ctx->applyFilters($sql);
        return new QuerySpec($sql, $params);
    }
}
