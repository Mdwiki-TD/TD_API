<?php
// src/app/Endpoints/Handlers/Top/TopLangsHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers\Top;
use App\Endpoints\{EndpointContext, EndpointHandler, QuerySpec};

final class TopLangsHandler implements EndpointHandler
{
    public function handle(EndpointContext $ctx): QuerySpec
    {
        return new QuerySpec(
            'SELECT ',
        );
    }
}
