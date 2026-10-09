<?php
// src/app/Endpoints/Handlers/UserStatusHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;
use App\Endpoints\{EndpointContext, EndpointHandler, QuerySpec};

use App\Endpoints\DefinedEndpoint;
use App\Endpoints\Definition\Param;
use App\Endpoints\Definition\EndpointDefinition;

final class UserStatusHandler implements EndpointHandler, DefinedEndpoint
{
    public function definition(): EndpointDefinition
    {
        return new EndpointDefinition(
            endpoint: 'user_status',
            summary: 'User status',
            tag: 'users',
            description: 'list of users (langs, campaigns, categories)',
            params: [
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
                    name: 'select',
                    column: 'select',
                    placeholder: 'Select fields',
                    options: ['lang', 'campaign', 'cat', 'year'],
                    doc: [
                        'in'          => 'query',
                        'name'        => 'select',
                        'description' => 'Select fields',
                        'required'    => false,
                        'schema'      => [
                            'type' => 'string',
                            'enum' => ['lang', 'campaign', 'category', 'year'],
                        ],
                    ]
                ),
            ],
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
