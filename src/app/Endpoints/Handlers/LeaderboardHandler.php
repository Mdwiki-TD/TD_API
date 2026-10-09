<?php
// src/app/Endpoints/Handlers/LeaderboardHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;
use App\Endpoints\{EndpointContext, QuerySpec};

use App\Endpoints\DefinedEndpoint;
use App\Endpoints\Definition\Param;
use App\Endpoints\Definition\EndpointDefinition;
/** التنسيق (formated) يتم لاحقاً في ResponseBuilder::format حسب قيمة get */
final class LeaderboardHandler implements DefinedEndpoint
{
    public function __construct(
        private string $endpoint,
    ) {
    }

    public function definition(): EndpointDefinition
    {
        $summary = $this->endpoint == ""
            ? "Retrieve leaderboard table data"
            : "Retrieve formatted leaderboard table data";

        return new EndpointDefinition(
            endpoint: $this->endpoint,
            summary: $summary,
            tag: 'statistics',
            params: [
                new Param(
                    name: 'year',
                    column: 'YEAR(p.pupdate)',
                    type: 'number',
                    placeholder: 'Year of publication',
                    doc: 'PublicationYearParam'
                ),
                new Param(
                    name: 'cat',
                    column: 'cat',
                    placeholder: 'Category',
                    valueCanBeNull: true
                ),
                new Param(
                    name: 'user_group',
                    column: 'u.user_group',
                    placeholder: 'User Group Name'
                ),
                new Param(
                    name: 'order',
                    column: 'order',
                    placeholder: 'Order by',
                    noSelect: true
                ),
            ],
            columns: ['u.user_group'],
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
