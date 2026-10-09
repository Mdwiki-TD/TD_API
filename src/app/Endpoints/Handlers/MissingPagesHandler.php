<?php
// src/app/Endpoints/Handlers/MissingPagesHandler.php
declare(strict_types=1);
namespace App\Endpoints\Handlers;

use App\Endpoints\Definition\{EndpointDefinition, Param};

use App\Endpoints\Handlers\ByCategory\MissingByLangAndCategoryHandler;

final class MissingPagesHandler extends MissingByLangAndCategoryHandler
{
    public function definition(): EndpointDefinition
    {
        return new EndpointDefinition(
            endpoint: 'missing',
            summary: 'Retrieve missing pages',
            tag: 'pages_infos',
            params: [
                new Param(
                    name: 'lang',
                    column: 't.code',
                    placeholder: 'Language code'
                ),
                new Param(
                    name: 'category',
                    column: 'a.category',
                    placeholder: 'Category'
                ),
                new Param(
                    name: 'order',
                    column: 'order',
                    placeholder: 'Order by',
                    noSelect: true
                ),
            ],
        );
    }
}
