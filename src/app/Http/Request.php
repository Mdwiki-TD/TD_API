<?php
// src/app/Http/Request.php

declare(strict_types=1);

namespace App\Http;

final class Request
{
    public function get(string $key): ?string
    {
        $v = $_GET[$key] ?? null;
        if (!is_string($v)) {
            return null;
        }
        return trim(preg_replace('/[\x00-\x1F\x7F]/u', '', $v) ?? '');
    }
    /** نفس منطق الكود القديم: غير موجود / false / 0 تعني "غير مفعّل" */
    public function enabled(string $key): bool
    {
        return isset($_GET[$key]) && $_GET[$key] !== 'false' && $_GET[$key] !== '0';
    }
}
