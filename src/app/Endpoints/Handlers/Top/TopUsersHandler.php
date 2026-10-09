<?php
// src/app/Endpoints/Handlers/Top/TopUsersHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers\Top;
use App\Endpoints\Handlers\Top\TopHandler;

final class TopUsersHandler extends TopHandler
{
    protected function selectField(): string
    {
        return 'p.user';
    }
    protected function groupColumn(): string
    {
        return 'p.user';
    }
    protected function summary(): string
    {
        return 'Retrieve Top users';
    }
}
