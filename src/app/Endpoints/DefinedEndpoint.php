<?php
// src/app/Endpoints/DefinedEndpoint.php
declare(strict_types=1);
namespace App\Endpoints;

use App\Endpoints\Definition\EndpointDefinition;
use App\Endpoints\{EndpointContext, QuerySpec};

/** handler يحمل تعريف endpoint بنفسه (النمط النهائي: كلاس واحد = definition + handle) */
interface DefinedEndpoint
{
    public function handle(EndpointContext $ctx): QuerySpec;

    public function definition(): EndpointDefinition;
}
