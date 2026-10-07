<?php
// src/app/Endpoints/Handlers/PagesUsersToMainHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;

use App\Endpoints\{AbstractEndpointHandler, EndpointContext, QuerySpec};

final class PagesUsersToMainHandler extends AbstractEndpointHandler
{
    public function handle(EndpointContext $ctx): QuerySpec
    {
        $sql = "SELECT pum.id, pum.new_target, pum.new_user, pum.new_qid
            FROM pages_users_to_main pum, pages_users pu
            WHERE pum.id = pu.id";
        $params = [];

        if ($ctx->request->enabled('lang')) {
            $lang = $ctx->request->get('lang');
            if ($lang !== null && $lang !== '') {
                $sql .= ' AND pu.lang = ?';
                $params[] = $lang;
            }
        }

        return new QuerySpec($sql, $params);
    }

    public function getColumns(): array
    {
        return [
            "new_target",
            "new_user",
            "new_qid",
        ];
    }
}
