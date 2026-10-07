<?php
// src/app/Endpoints/Handlers/DefaultTableHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;

use App\Endpoints\{EndpointContext, EndpointHandler, QuerySpec};

final class DefaultTableHandler implements EndpointHandler
{
    public function __construct(private string $table) {}

    public function handle(EndpointContext $ctx): QuerySpec
    {
        $sql = "SELECT {$ctx->distinct}{$ctx->select} FROM `{$this->table}`";
        [$sql, $params] = $ctx->applyFilters($sql);
        return new QuerySpec($sql, $params);
    }
}
