<?php
// src/app/Endpoints/AbstractEndpointHandler.php
declare(strict_types=1);

namespace App\Endpoints;

abstract class AbstractEndpointHandler implements EndpointHandler
{
    /** @return array<int, array<string, mixed>> */
    public function getParams(): array
    {
        return [];
    }

    /** @return string[] */
    public function getColumns(): array
    {
        return [];
    }

    /** @return array<string, string> */
    public function getOrderValues(): array
    {
        return [];
    }
}
