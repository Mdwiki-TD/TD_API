<?php
// src/app/Endpoints/Handlers/LeaderboardHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;
use App\Endpoints\{EndpointContext, EndpointHandler, QuerySpec};

use App\Endpoints\DefinedEndpoint;
use App\Endpoints\Definition\EndpointDefinition;
/** التنسيق (formated) يتم لاحقاً في ResponseBuilder::format حسب قيمة get */
final class LeaderboardHandler implements EndpointHandler, DefinedEndpoint
{
    public function definition(): EndpointDefinition
    {
        return new EndpointDefinition(
        );
    }

    public function handle(EndpointContext $ctx): QuerySpec
    {
        $sql = "SELECT p.title, p.target, p.cat, p.lang, p.word,
                       YEAR(p.pupdate) AS pup_y, p.user, u.user_group,
                       LEFT(p.pupdate, 7) AS m, v.views
                FROM pages p
                LEFT JOIN users u ON p.user = u.username
                LEFT JOIN views_new_all v
                    ON p.target = v.target
                    AND p.lang = v.lang
                WHERE p.target != ''";

        [$sql, $params] = $ctx->applyFilters($sql);
        return new QuerySpec($sql, $params, defaultOrder: '1 DESC');
    }
}
