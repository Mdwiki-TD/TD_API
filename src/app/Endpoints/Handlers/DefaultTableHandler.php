<?php
// src/app/Endpoints/Handlers/DefaultTableHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;

use App\Endpoints\{AbstractEndpointHandler, EndpointContext, QuerySpec};

final class DefaultTableHandler extends AbstractEndpointHandler
{
    public function __construct(
        private array $params = [],
        private array $columns = [],
    ) {}

    public function handle(EndpointContext $ctx): QuerySpec
    {
        $get = $ctx->get;
        $sql = "SELECT {$ctx->distinct}{$ctx->select} FROM {$get}";
        [$sql, $params] = $ctx->applyFilters($sql);
        return new QuerySpec($sql, $params);
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }
}
