<?php
// src/app/Endpoints/Handlers/UsersHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;
use App\Endpoints\{EndpointContext, EndpointHandler, QuerySpec};

final class UsersHandler implements EndpointHandler
{
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
