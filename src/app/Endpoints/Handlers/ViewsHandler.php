<?php
// src/app/Endpoints/Handlers/ViewsHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;
use App\Endpoints\{EndpointContext, QuerySpec};


use App\Endpoints\DefinedEndpoint;
use App\Endpoints\Definition\Param;
use App\Endpoints\Definition\EndpointDefinition;

final class ViewsHandler implements DefinedEndpoint
{

    public function __construct(
        private string $endpoint,
        private string $defaultOrder = '',
    ) {
        $this->endpoint = $endpoint;
    }

    public function definition(): EndpointDefinition
    {
        $yearParam = new Param(name: 'year', column: 'YEAR(p.pupdate)', type: 'number', placeholder: 'Year of publication', doc: 'PublicationYearParam');
        $definitions = [
            'user_views' => new EndpointDefinition(
                endpoint: 'user_views',
                summary: 'Retrieve page views for a user',
                tag: 'views',
                params: [
                    new Param(name: 'user', column: 'p.user', placeholder: 'Username', required: true),
                    new Param(name: 'lang', column: 'p.lang', placeholder: 'Language code'),
                    $yearParam,
                ],
            ),
            'lang_views' => new EndpointDefinition(
                endpoint: 'lang_views',
                summary: 'Retrieve language view statistics',
                tag: 'views',
                params: [
                    new Param(name: 'user', column: 'p.user', placeholder: 'Username'),
                    new Param(name: 'lang', column: 'p.lang', placeholder: 'Language code', required: true),
                    $yearParam,
                ],
            ),
            'views'      => new EndpointDefinition(
                endpoint: 'views',
                summary: 'Retrieve new page views',
                tag: 'views',
                params: [
                    new Param(name: 'lang', column: 'p.lang', placeholder: 'Language code'),
                    new Param(name: 'views', column: 'v.views', type: 'number', placeholder: 'Views'),
                    $yearParam,
                ],
            ),
        ];
        return $definitions[$this->endpoint];
    }

    public function handle(EndpointContext $ctx): QuerySpec
    {
        $sql = "SELECT p.title, v.target, v.lang, v.views
                FROM views_new_all v
                LEFT JOIN pages p
                    ON p.target = v.target
                    AND p.lang = v.lang";

        [$sql, $params] = $ctx->applyFilters($sql);

        return new QuerySpec($sql, $params, defaultOrder: $this->defaultOrder);
    }
}
