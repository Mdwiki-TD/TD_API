<?php
// src/app/Endpoints/Handlers/UsersHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;

use App\Endpoints\{AbstractEndpointHandler, EndpointContext, QuerySpec};

final class UsersHandler extends AbstractEndpointHandler
{
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

    public function getColumns(): array
    {
        return [
            "user_id",
            "username",
            "email",
            "wiki",
            "user_group",
            "reg_date",
        ];
    }

    public function getParams(): array
    {
        return [
            [
                "name" => "userlike",
                "column" => "userlike",
                "type" => "text",
                "placeholder" => "Username starts with",
                "required" => true,
            ],
            [
                "name" => "wiki",
                "column" => "wiki",
                "type" => "text",
                "placeholder" => "Wiki Name",
            ],
            [
                "name" => "user_group",
                "column" => "user_group",
                "type" => "text",
                "placeholder" => "User Group Name",
                "no_select" => true,
            ],
        ];
    }
}
