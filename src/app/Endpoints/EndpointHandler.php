<?php
// src/app/Endpoints/EndpointHandler.php
declare(strict_types=1);

namespace App\Endpoints;

interface EndpointHandler
{
    // public function definition(): EndpointDefinition;
    public function handle(EndpointContext $ctx): QuerySpec;
}
