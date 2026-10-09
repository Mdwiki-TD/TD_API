<?php
// src/app/Endpoints/Handlers/CallableHandler.php
declare (strict_types = 1);

namespace App\Endpoints\Handlers;

use App\Endpoints\EndpointContext;
use App\Endpoints\EndpointHandler;
use App\Endpoints\QuerySpec;
use Closure;

/**
 * Warraper for legacy functions that return [query, params, error?]
 */
final class CallableHandler implements EndpointHandler
{
    public function __construct(private Closure $fn)
    {}

    public function handle(EndpointContext $ctx): QuerySpec
    {
        return QuerySpec::fromLegacy(($this->fn)($ctx));
    }
}
