<?php
// src/app/Database/Cache/NullCache.php
declare(strict_types=1);

namespace App\Database\Cache;

final class NullCache implements CacheInterface
{
    public function get(string $sql, array $params): ?array
    {
        return null;
    }
    public function set(string $sql, array $params, array $results): void {}
}
