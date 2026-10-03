<?php
// src/app/Endpoints/Handlers/DefaultTableHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;

use App\Endpoints\{EndpointContext, EndpointHandler, QuerySpec};
use function API\Helps\add_li_params;

final class DefaultTableHandler implements EndpointHandler
{
    public function handle(EndpointContext $ctx): QuerySpec
    {
        // $ctx->get سبق التحقق منه في Registry (whitelist)، والـ regex هنا حماية إضافية
        if (!preg_match('/^[A-Za-z0-9_]+$/', $ctx->get)) {
            return new QuerySpec(error: 'invalid table name');
        }
        $sql = "SELECT {$ctx->distinct}{$ctx->select} FROM `{$ctx->get}`";
        [$sql, $params] = add_li_params($sql, [], $ctx->params);
        return new QuerySpec($sql, $params);
    }
}
