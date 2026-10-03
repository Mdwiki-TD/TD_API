<?php
// src/app/Database/Cache/CacheInterface.php
declare(strict_types=1);

namespace App\Database\Cache;

interface CacheInterface
{
    /** @return array|null null عند عدم الوجود */
    public function get(string $sql, array $params): ?array;

    public function set(string $sql, array $params, array $results): void;
}
