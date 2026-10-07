<?php
// src/app/Endpoints/Handlers/CallableHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;

use App\Endpoints\{AbstractEndpointHandler, EndpointContext, QuerySpec};

final class CallableHandler extends AbstractEndpointHandler
{
    /** @var callable(EndpointContext): (QuerySpec|array{0: string, 1: array, 2?: string}) */
    private $callable;

    public function __construct(
        callable $callable,
        private array $params = [],
        private array $columns = [],
    ) {
        $this->callable = $callable;
    }

    public function handle(EndpointContext $ctx): QuerySpec
    {
        $res = ($this->callable)($ctx);
        if ($res instanceof QuerySpec) {
            return $res;
        }
        return new QuerySpec($res[0], $res[1], $res[2] ?? '');
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
