<?php
// src/app/Config/EndpointConfig.php
declare(strict_types=1);

namespace App\Config;

use RuntimeException;

final class EndpointConfig
{
    private array $config;

    public function __construct(string $path)
    {
        $raw = is_file($path) ? file_get_contents($path) : false;
        if ($raw === false) {
            throw new RuntimeException("endpoint config not readable: $path");
        }
        $this->config = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    }

    /** بيانات الـ endpoint مع تتبع redirect */
    public function find(string $get): array
    {
        $data = $this->config[$get] ?? [];
        if (isset($data['redirect'])) {
            $data = $this->config[$data['redirect']] ?? [];
        }
        return $data;
    }
}
