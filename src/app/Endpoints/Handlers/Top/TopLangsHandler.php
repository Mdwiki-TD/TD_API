<?php
// src/app/Endpoints/Handlers/Top/TopLangsHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers\Top;
use App\Endpoints\Handlers\Top\TopHandler;


final class TopLangsHandler extends TopHandler
{
    protected function selectField(): string
    {
        return 'p.lang, la.name AS lang_name';
    }
    protected function groupColumn(): string
    {
        return 'p.lang';
    }
    protected function summary(): string
    {
        return 'Retrieve Top langs';
    }
}
