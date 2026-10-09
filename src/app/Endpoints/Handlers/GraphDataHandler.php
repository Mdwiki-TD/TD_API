<?php
// src/app/Endpoints/Handlers/GraphDataHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;
use App\Endpoints\{EndpointContext, EndpointHandler, QuerySpec};

use App\Endpoints\DefinedEndpoint;
use App\Endpoints\Definition\Param;
use App\Endpoints\Definition\EndpointDefinition;
final class GraphDataHandler implements EndpointHandler, DefinedEndpoint
{
    public function definition(): EndpointDefinition
    {
        return new EndpointDefinition(
            endpoint: 'graph_data',
            summary: 'Retrieve graph data',
            tag: 'statistics',
            params: [
                new Param(
                    name: 'user_group',
                    column: 'u.user_group',
                    placeholder: 'User Group Name'
                ),
                new Param(
                    name: 'month',
                    column: 'MONTH(p.pupdate)',
                    type: 'number',
                    placeholder: 'month of date',
                    noEmptyValue: true
                ),
                new Param(
                    name: 'year',
                    column: 'YEAR(p.pupdate)',
                    type: 'number',
                    placeholder: 'year of date',
                    noEmptyValue: true,
                    doc: 'PublicationYearParam'
                ),
                new Param(
                    name: 'user',
                    column: 'p.user',
                    placeholder: 'Username',
                    noEmptyValue: false
                ),
                new Param(
                    name: 'lang',
                    column: 'p.lang',
                    placeholder: 'Language code',
                    noEmptyValue: false
                ),
                new Param(
                    name: 'category',
                    column: 'p.cat',
                    placeholder: 'Category'
                ),
                new Param(
                    name: 'campaign',
                    column: 'p.campaign',
                    placeholder: 'Campaign'
                ),
            ],
        );
    }

    public function handle(EndpointContext $ctx): QuerySpec
    {
        // api.php?get=graph_data&year=All&month=&category=&campaign=All&user_group=all
        $sql = "SELECT DISTINCT LEFT(p.pupdate, 7) AS date, COUNT(*) AS count
            FROM pages p
            LEFT JOIN categories ca ON p.cat = ca.category
            LEFT JOIN users u ON p.user = u.username
            WHERE p.target != ''
        ";

        [$sql, $params] = $ctx->applyFilters($sql, ['campaign', 'cat', 'category']);

        // Apply campaign/category filters
        [$sql, $params] = $ctx->applyCampaignCategory($sql, $params);

        $sql .= " GROUP BY LEFT(p.pupdate, 7)";

        return new QuerySpec($sql, $params, defaultOrder: 'date ASC');
    }
}
