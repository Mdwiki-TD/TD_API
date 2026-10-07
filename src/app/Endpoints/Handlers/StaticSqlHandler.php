<?php
// src/app/Endpoints/Handlers/StaticSqlHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;

use App\Endpoints\{AbstractEndpointHandler, EndpointContext, QuerySpec};

final class StaticSqlHandler extends AbstractEndpointHandler
{
    public function __construct(
        private string $sql,
        private bool $applyOrder = true,
        private array $params = [],
        private array $columns = [],
    ) {}

    public function handle(EndpointContext $ctx): QuerySpec
    {
        return new QuerySpec($this->sql, [], applyOrder: $this->applyOrder);
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
