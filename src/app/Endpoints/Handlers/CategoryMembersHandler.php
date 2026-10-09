<?php
// src/app/Endpoints/Handlers/CategoryMembersHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;
use App\Endpoints\Definition\EndpointDefinition;
use App\Endpoints\{EndpointContext, DefinedEndpoint, EndpointHandler, QuerySpec};

final class CategoryMembersHandler implements EndpointHandler, DefinedEndpoint
{
    public const ENDPOINT_NAME    = 'category_members';
    private const DEFAULT_CATEGORY = 'RTT';

    public function definition(): EndpointDefinition
    {
        return new EndpointDefinition(
            endpoint: 'category_members',
            summary: 'Retrieve article ids of a category',
            tag: 'pages',
        );
    }

    public function handle(EndpointContext $ctx): QuerySpec
    {
        $cat = $ctx->request->get('cat') ?: self::DEFAULT_CATEGORY;

        return new QuerySpec(
            'SELECT article_id FROM category_members WHERE category = ?',
            [$cat],
        );
    }
}
