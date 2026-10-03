<?php
// src/app/Database/Cache/ApcuCache.php
declare(strict_types=1);

namespace App\Database\Cache;

final class ApcuCache implements CacheInterface
{
    public function __construct(private int $ttl = 3600 * 12) {}

    public static function isAvailable(): bool
    {
        return extension_loaded('apcu') && function_exists('apcu_fetch') && apcu_enabled();
    }

    public function get(string $sql, array $params): ?array
    {
        $value = apcu_fetch($this->key($sql, $params), $hit);
        return ($hit && is_array($value) && $value !== []) ? $value : null;
    }

    public function set(string $sql, array $params, array $results): void
    {
        if ($results !== []) {
            apcu_store($this->key($sql, $params), $results, $this->ttl);
        }
    }

    private function key(string $sql, array $params): string
    {
        return 'apcu_' . md5($sql . json_encode($params));
    }
}
