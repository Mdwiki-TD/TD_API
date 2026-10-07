<?php
// src/app/Endpoints/EndpointHandler.php
declare(strict_types=1);

namespace App\Endpoints;

interface EndpointHandler
{
    public function handle(EndpointContext $ctx): QuerySpec;

    /** @return array<int, array<string, mixed>> */
    public function getParams(): array;

    /** @return string[] */
    public function getColumns(): array;

    /** @return array<string, string> */
    public function getOrderValues(): array;
}
