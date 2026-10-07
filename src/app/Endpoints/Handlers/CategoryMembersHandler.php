<?php
// src/app/Endpoints/Handlers/CategoryMembersHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;

use App\Endpoints\{AbstractEndpointHandler, EndpointContext, QuerySpec};

final class CategoryMembersHandler extends AbstractEndpointHandler
{
    public function handle(EndpointContext $ctx): QuerySpec
    {
        $cat = 'RTT';
        $sql = 'SELECT article_id FROM category_members';
        $params = [];

        if ($ctx->request->has('cat')) {
            $inputCat = $ctx->request->get('cat');
            if ($inputCat !== null && $inputCat !== '') {
                $cat = $inputCat;
            }
        }
        $sql .= ' WHERE category = ?';
        $params[] = $cat;

        return new QuerySpec($sql, $params);
    }
}
