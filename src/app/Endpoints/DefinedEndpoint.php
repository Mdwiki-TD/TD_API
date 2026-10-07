<?php
// src/app/Endpoints/DefinedEndpoint.php
declare(strict_types=1);
namespace App\Endpoints;

use App\Endpoints\Definition\EndpointDefinition;

/** handler يحمل تعريف endpoint بنفسه (النمط النهائي: كلاس واحد = definition + handle) */
interface DefinedEndpoint
{
    public function definition(): EndpointDefinition;
}
