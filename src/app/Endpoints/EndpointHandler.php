<?php
// src/app/Endpoints/EndpointHandler.php
declare (strict_types = 1);

namespace App\Endpoints;

use App\Endpoints\Definition\EndpointDefinition;

interface EndpointHandler
{
    // public function definition(): EndpointDefinition;
    public function handle(EndpointContext $ctx): QuerySpec;
}
