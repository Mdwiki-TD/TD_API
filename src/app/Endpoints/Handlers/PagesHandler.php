<?php
// src/app/Endpoints/Handlers/PagesHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;

use App\Endpoints\{AbstractEndpointHandler, EndpointContext, QuerySpec};
use function API\TitlesInfos\pages_query;

final class PagesHandler extends AbstractEndpointHandler
{
    public function __construct(private string $get = 'pages') {}

    public function handle(EndpointContext $ctx): QuerySpec
    {
        [$sql, $params, $error] = pages_query($ctx->params, $ctx->select, $ctx->distinct, $this->get);
        return new QuerySpec($sql, $params, $error);
    }

    public function getColumns(): array
    {
        return [
            "title",
            "word",
            "translate_type",
            "cat",
            "lang",
            "user",
            "target",
            "date",
            "pupdate",
            "add_date",
            "deleted",
            "mdwiki_revid",
        ];
    }

    public function getOrderValues(): array
    {
        return [
            "pupdate_or_add_date" => "GREATEST(UNIX_TIMESTAMP(pupdate), UNIX_TIMESTAMP(add_date))",
        ];
    }

    public function getParams(): array
    {
        if ($this->get === 'pages_users') {
            return [
                [
                    "name" => "lang",
                    "column" => "lang",
                    "type" => "text",
                    "placeholder" => "Language code",
                ],
                [
                    "name" => "user",
                    "column" => "user",
                    "type" => "text",
                    "placeholder" => "Username",
                ],
                [
                    "name" => "target",
                    "column" => "target",
                    "type" => "text",
                    "placeholder" => "Target",
                ],
                [
                    "name" => "title",
                    "column" => "title",
                    "type" => "text",
                    "placeholder" => "Page Title",
                ],
                [
                    "name" => "order",
                    "column" => "order",
                    "type" => "text",
                    "placeholder" => "Order by",
                    "no_select" => true,
                ],
                [
                    "name" => "pupdate",
                    "column" => "pupdate",
                    "type" => "text",
                    "placeholder" => "Date of publication",
                ],
                [
                    "name" => "add_date",
                    "column" => "add_date",
                    "type" => "text",
                    "placeholder" => "Date of addition to DB",
                ],
                [
                    "name" => "group",
                    "column" => "group",
                    "type" => "text",
                    "placeholder" => "Group by field",
                    "no_select" => true,
                    "options" => [],
                ],
                [
                    "name" => "limit",
                    "column" => "limit",
                    "type" => "number",
                    "placeholder" => "Limit results",
                    "value" => "50",
                    "no_select" => true,
                ],
                [
                    "name" => "offset",
                    "column" => "p.offset",
                    "type" => "number",
                    "placeholder" => "Offset results",
                    "value" => "0",
                    "no_select" => true,
                ],
                [
                    "name" => "select",
                    "column" => "select",
                    "type" => "text",
                    "placeholder" => "Select fields",
                    "no_select" => true,
                ],
                [
                    "name" => "distinct",
                    "column" => "distinct",
                    "type" => "switch",
                    "no_select" => true,
                ],
            ];
        }

        return [
            [
                "name" => "title",
                "column" => "p.title",
                "type" => "text",
                "placeholder" => "Page Title",
            ],
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
                "name" => "target",
                "column" => "p.target",
                "type" => "text",
                "placeholder" => "Target",
            ],
            [
                "name" => "cat",
                "column" => "p.cat",
                "type" => "text",
                "placeholder" => "Category",
            ],
            [
                "name" => "campaign",
                "column" => "campaign",
                "type" => "text",
                "placeholder" => "Campaign",
            ],
            [
                "name" => "group",
                "column" => "group",
                "type" => "text",
                "placeholder" => "Group by field",
                "no_select" => true,
                "options" => [],
            ],
            [
                "name" => "order",
                "column" => "order",
                "type" => "text",
                "placeholder" => "Order by",
                "no_select" => true,
            ],
            [
                "name" => "pupdate",
                "column" => "p.pupdate",
                "type" => "text",
                "placeholder" => "Date of publication",
            ],
            [
                "name" => "add_date",
                "column" => "p.add_date",
                "type" => "text",
                "placeholder" => "Date of addition to DB",
            ],
            [
                "name" => "limit",
                "column" => "p.limit",
                "type" => "number",
                "placeholder" => "Limit results",
                "value" => "50",
                "no_select" => true,
            ],
            [
                "name" => "offset",
                "column" => "p.offset",
                "type" => "number",
                "placeholder" => "Offset results",
                "value" => "0",
                "no_select" => true,
            ],
            [
                "name" => "year",
                "column" => "YEAR(p.pupdate)",
                "type" => "number",
                "placeholder" => "Year of publication",
            ],
            [
                "name" => "date_year",
                "column" => "YEAR(p.date)",
                "type" => "number",
                "placeholder" => "year of date",
            ],
            [
                "name" => "select",
                "column" => "select",
                "type" => "text",
                "placeholder" => "Select fields",
                "no_select" => true,
                "options" => [
                    "count(*)",
                ],
            ],
            [
                "name" => "translate_type",
                "column" => "p.translate_type",
                "type" => "select",
                "options" => [
                    "all",
                    "lead",
                ],
            ],
            [
                "name" => "distinct",
                "column" => "p.distinct",
                "type" => "switch",
                "no_select" => true,
            ],
            [
                "name" => "Deleted",
                "column" => "p.deleted",
                "type" => "switch",
                "placeholder" => "0 or 1",
            ],
        ];
    }
}
