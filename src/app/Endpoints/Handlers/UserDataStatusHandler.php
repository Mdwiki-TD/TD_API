<?php
// src/app/Endpoints/Handlers/UserDataStatusHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;
use App\Endpoints\{EndpointContext, EndpointHandler, QuerySpec};

use App\Endpoints\DefinedEndpoint;
use App\Endpoints\Definition\Param;
use App\Endpoints\Definition\EndpointDefinition;

final class UserDataStatusHandler implements EndpointHandler, DefinedEndpoint
{
    public function definition(): EndpointDefinition
    {
        return new EndpointDefinition(
            endpoint: 'user_data_status',
            summary: "Retrieve years, languages and campaigns of a user's pages",
            tag: 'users',
            params: [
                new Param(
                    name: 'user',
                    column: 'p.user',
                    placeholder: 'Username',
                    required: true
                ),
            ],
        );
    }


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
