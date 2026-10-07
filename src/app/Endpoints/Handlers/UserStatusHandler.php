<?php
// src/app/Endpoints/Handlers/UserStatusHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;

use App\Endpoints\{AbstractEndpointHandler, EndpointContext, QuerySpec};

final class UserStatusHandler extends AbstractEndpointHandler
{
    public function handle(EndpointContext $ctx): QuerySpec
    {
        $select = ($ctx->select === '*' || $ctx->select === 'year') ? 'YEAR(p.pupdate) AS year' : $ctx->select;
        $base = "SELECT DISTINCT {$select} FROM pages p LEFT JOIN categories ca ON p.cat = ca.category";
        [$sql, $params] = $ctx->applyFilters($base);

        return new QuerySpec($sql, $params);
    }

    public function getParams(): array
    {
        return [
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
                "name" => "select",
                "column" => "select",
                "type" => "text",
                "placeholder" => "Select fields",
                "options" => [
                    "lang",
                    "campaign",
                    "cat",
                    "year",
                ],
            ],
        ];
    }
}
