<?php
// src/app/Endpoints/Handlers/Top/TopLangOfUsersHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers\Top;

use App\Endpoints\{DefinedEndpoint, EndpointContext, QuerySpec};
use App\Endpoints\Definition\{EndpointDefinition, Param};


final class TopLangOfUsersHandler implements DefinedEndpoint
{
    public function definition(): EndpointDefinition
    {
        return new EndpointDefinition(
            endpoint: 'top_lang_of_users',
            summary: 'Retrieve the most used language of each user',
            tag: 'users',
            params: [
                new Param(
                    name: 'users',
                    column: 'p.user',
                    type: 'array',
                    doc: [
                        'in'          => 'query',
                        'name'        => 'users',
                        'description' => 'list of users',
                        'required'    => false,
                        'schema'      => ['type' => 'array', 'items' => ['type' => 'string'], 'maxItems' => 50],
                    ],
                ),
            ],
        );
    }

    public function handle(EndpointContext $ctx): QuerySpec
    {
        $users = $ctx->request->getArray('users');
        $params = [];
        $filter = '';

        if ($users !== []) {
            $filter = 'AND p.user IN (' . implode(',', array_fill(0, count($users), '?')) . ')';
            $params = $users;
        }

        $sql = "SELECT user, lang, cnt
            FROM (
                SELECT p.user, p.lang, COUNT(p.target) AS cnt,
                       ROW_NUMBER() OVER (PARTITION BY p.user ORDER BY COUNT(p.target) DESC) AS rn
                FROM pages p
                WHERE p.target != '' AND p.target IS NOT NULL
                $filter
                GROUP BY p.user, p.lang
            ) AS ranked
            WHERE rn = 1";

        return new QuerySpec($sql, $params, defaultOrder: 'cnt DESC');
    }
}
