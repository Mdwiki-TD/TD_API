<?php
// src/app/Endpoints/Handlers/LeaderboardHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;

use App\Endpoints\{AbstractEndpointHandler, EndpointContext, QuerySpec};

final class LeaderboardHandler extends AbstractEndpointHandler
{
    public function handle(EndpointContext $ctx): QuerySpec
    {
        $base = "SELECT p.title,
            p.target, p.cat, p.lang, p.word, YEAR(p.pupdate) AS pup_y, p.user, u.user_group, LEFT(p.pupdate, 7) as m, v.views
            FROM pages p
            LEFT JOIN users u
                ON p.user = u.username
            LEFT JOIN views_new_all v
                ON p.target = v.target
                AND p.lang = v.lang
            WHERE p.target != ''
        ";

        [$sql, $params] = $ctx->applyFilters($base);

        return new QuerySpec($sql, $params, defaultOrder: '1 DESC');
    }

    public function getColumns(): array
    {
        return [
            "u.user_group",
        ];
    }

    public function getParams(): array
    {
        return [
            [
                "name" => "year",
                "column" => "YEAR(p.pupdate)",
                "type" => "number",
                "placeholder" => "Year of publication",
            ],
            [
                "name" => "cat",
                "column" => "cat",
                "type" => "text",
                "placeholder" => "Category",
                "value_can_be_null" => true,
            ],
            [
                "name" => "user_group",
                "column" => "u.user_group",
                "type" => "text",
                "placeholder" => "User Group Name",
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
