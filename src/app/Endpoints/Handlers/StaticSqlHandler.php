<?php
// src/app/Endpoints/Handlers/StaticSqlHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;
use App\Endpoints\{EndpointContext, EndpointHandler, QuerySpec};

/** استعلام ثابت بلا فلاتر (coordinators, langs, graph_data ...) */
final class StaticSqlHandler implements EndpointHandler
{
    public function __construct(
        private string $sql,
        private bool $applyOrder = true,
    ) {}

    public function handle(EndpointContext $ctx): QuerySpec
    {
        return new QuerySpec($this->sql, [], '', $this->applyOrder);
    }
}
