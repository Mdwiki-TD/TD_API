<?php
// src/app/Endpoints/Handlers/UserDataStatusHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;
use App\Endpoints\Definition\EndpointDefinition;
use App\Endpoints\{EndpointContext, EndpointHandler, QuerySpec};

final class UserDataStatusHandler implements EndpointHandler
{

    public function handle(EndpointContext $ctx): QuerySpec
    {
        $user = $ctx->request->get('user');
        if (!$ctx->isValid($user)) {
            return new QuerySpec(error: 'user param required');
        }

        return new QuerySpec(
            'SELECT DISTINCT YEAR(p.pupdate) AS year, p.lang, ca.campaign
                FROM pages p
                LEFT JOIN categories ca ON p.cat = ca.category
                WHERE p.user = ?',
            [$user],
        );
    }
}
