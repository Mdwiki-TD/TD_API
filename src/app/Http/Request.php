<?php
// src/app/Http/Request.php

declare(strict_types=1);

namespace App\Http;

final class Request
{
    /** قيمة نصية خام بلا ترميز HTML (الأمان عبر prepared statements). null للمصفوفات والغائب */
    public function get(string $key): ?string
    {
        $v = $_GET[$key] ?? null;
        if (!is_string($v)) {
            return null;
        }
        return trim(preg_replace('/[\x00-\x1F\x7F]/u', '', $v) ?? '');
    }

    public function has(string $key): bool
    {
        return isset($_GET[$key]);
    }

    /** غير موجود / false / 0 تعني "غير مفعّل" */
    public function enabled(string $key): bool
    {
        return isset($_GET[$key]) && $_GET[$key] !== 'false' && $_GET[$key] !== '0';
    }

    public function int(string $key): int
    {
        return (int) ($this->get($key) ?? 0);
    }

    /** @return list<string> */
    public function getArray(string $key): array
    {
        $v = $_GET[$key] ?? null;
        if (!is_array($v)) {
            return [];
        }
        // سقف 1000 عنصر حماية من placeholders ضخمة
        return array_slice(array_values(array_filter($v, 'is_string')), 0, 1000);
    }
}
