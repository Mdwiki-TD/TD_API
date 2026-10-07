<?php
// src/app/Endpoints/Handlers/MissingPagesHandler.php
declare(strict_types=1);
namespace App\Endpoints\Handlers;

use App\Endpoints\Definition\{EndpointDefinition, Param};

/** الاسم القديم `missing`: نفس الاستعلام، لكن يقبل ?order= ولا يطلب lang في تعريفه */
final class MissingPagesHandler extends MissingByLangAndCategoryHandler
{
    public function definition(): EndpointDefinition
    {
        return new EndpointDefinition(
            summary: 'Retrieve missing pages',
            tag: 'pages_infos',
            params: [
                new Param(name: 'lang', column: 't.code', placeholder: 'Language code'),
                new Param(name: 'category', column: 'a.category', placeholder: 'Category'),
                new Param(name: 'order', column: 'order', placeholder: 'Order by', noSelect: true),
            ],
        );
    }
}
