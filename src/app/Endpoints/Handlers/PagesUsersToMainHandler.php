<?php
// src/app/Endpoints/Handlers/PagesUsersToMainHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;
use App\Endpoints\{EndpointContext, EndpointHandler, QuerySpec};

use App\Endpoints\DefinedEndpoint;
use App\Endpoints\Definition\EndpointDefinition;
final class PagesUsersToMainHandler implements EndpointHandler, DefinedEndpoint
{
    public function definition(): EndpointDefinition
    {
        return new EndpointDefinition(
        );
    }

    public function handle(EndpointContext $ctx): QuerySpec
    {
        $old_query = "SELECT pum.id, pum.new_target, pum.new_user, pum.new_qid
            FROM pages_users_to_main pum, pages_users pu
            where pum.id = pu.id
        ";
        $sql = 'SELECT pum.id, pum.new_target, pum.new_user, pum.new_qid
                FROM pages_users_to_main pum
                JOIN pages_users pu ON pum.id = pu.id';

        $params = [];

        if ($ctx->request->enabled('lang')) {
            $lang = $ctx->request->get('lang');
            if ($lang !== null) {
                $sql .= ' WHERE pu.lang = ?';
                $params[] = $lang;
            }
        }
        return new QuerySpec($sql, $params);
    }
}
