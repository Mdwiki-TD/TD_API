<?php
// src/app/Endpoints/Handlers/UserDataStatusHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;

use App\Endpoints\{EndpointContext, EndpointHandler, QuerySpec};

final class UserDataStatusHandler implements EndpointHandler
{

    public function handle(EndpointContext $ctx): QuerySpec
    {
        $sql = "SELECT YEAR(p.pupdate) AS year, p.lang, ca.campaign
                FROM pages p
                LEFT JOIN categories ca ON p.cat = ca.category
        ";
        $params = [];

        $user = $ctx->request->get('user');

        if ($ctx->isValid($user)) {
            $sql .= "WHERE p.user = ?";
            $params[] = $user;
        }

        return new QuerySpec($sql, $params);
    }
}
