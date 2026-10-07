<?php
// src/app/Endpoints/Handlers/GraphDataHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;

use App\Endpoints\{AbstractEndpointHandler, EndpointContext, QuerySpec};

final class GraphDataHandler extends AbstractEndpointHandler
{
    public function handle(EndpointContext $ctx): QuerySpec
    {
        $base = <<<SQL
            SELECT LEFT(p.pupdate, 7) AS m, COUNT(*) AS c
            FROM pages p
            LEFT JOIN users u ON p.user = u.username
            LEFT JOIN categories ca ON p.cat = ca.category
            WHERE p.target != ''
        SQL;

        [$sql, $params] = $ctx->applyFilters($base);
        [$sql, $params] = $ctx->applyCampaignCategory($sql, $params);

        $sql .= " GROUP BY LEFT(p.pupdate, 7) ORDER BY LEFT(p.pupdate, 7) ASC";

        return new QuerySpec($sql, $params, applyOrder: false);
    }

    public function getParams(): array
    {
        return [
            [
                "name" => "user_group",
                "column" => "u.user_group",
                "type" => "text",
                "placeholder" => "User Group Name",
            ],
            [
                "name" => "month",
                "column" => "MONTH(p.pupdate)",
                "type" => "number",
                "placeholder" => "month of date",
                "no_empty_value" => true,
            ],
            [
                "name" => "year",
                "column" => "YEAR(p.pupdate)",
                "type" => "number",
                "placeholder" => "year of date",
                "no_empty_value" => true,
            ],
            [
                "name" => "user",
                "column" => "p.user",
                "type" => "text",
                "placeholder" => "Username",
                "no_empty_value" => false,
            ],
            [
                "name" => "lang",
                "column" => "p.lang",
                "type" => "text",
                "placeholder" => "Language code",
                "no_empty_value" => false,
            ],
            [
                "name" => "category",
                "column" => "p.cat",
                "type" => "text",
                "placeholder" => "Category",
            ],
            [
                "name" => "campaign",
                "column" => "p.campaign",
                "type" => "text",
                "placeholder" => "Campaign",
            ],
        ];
    }
}
