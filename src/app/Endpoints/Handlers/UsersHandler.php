<?php
// src/app/Endpoints/Handlers/UsersHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;
use App\Endpoints\{EndpointContext, QuerySpec};

use App\Endpoints\DefinedEndpoint;
use App\Endpoints\Definition\Param;
use App\Endpoints\Definition\EndpointDefinition;

final class UsersHandler implements DefinedEndpoint
{
    public function definition(): EndpointDefinition
    {
        return new EndpointDefinition(
            endpoint: 'users',
            summary: 'Retrieve user information',
            tag: 'users',
            params: [
                new Param(
                    name: 'userlike',
                    column: 'userlike',
                    placeholder: 'Username starts with',
                    required: true
                ),
                new Param(
                    name: 'wiki',
                    column: 'wiki',
                    placeholder: 'Wiki Name'
                ),
                new Param(
                    name: 'user_group',
                    column: 'user_group',
                    placeholder: 'User Group Name',
                    noSelect: true
                ),
            ],
            columns: ['user_id', 'username', 'email', 'wiki', 'user_group', 'reg_date'],
        );
    }

    public const ENDPOINT_NAME = 'users';
    public function handle(EndpointContext $ctx): QuerySpec
    {
        $sql = 'SELECT username FROM users';
        $params = [];

        if ($ctx->request->enabled('userlike')) {
            $like = $ctx->request->get('userlike');
            if ($like !== null) {
                $sql .= ' WHERE username LIKE ?';
                $params[] = $like . '%';
            }
        }
        return new QuerySpec($sql, $params);
    }
}
