<?php
// src/app/Http/Request.php

declare(strict_types=1);

namespace App\Http;

final class Request
{
    /** القيمة المفلترة، أو null إذا كانت غير موجودة */
    public function get(string $key, int $filter = FILTER_SANITIZE_FULL_SPECIAL_CHARS): ?string
    {
        if (!isset($_GET[$key]) || is_array($_GET[$key])) {
            return null;
        }
        $v = filter_var($_GET[$key], $filter);
        return is_string($v) ? $v : null;
    }

    /** نفس منطق الكود القديم: غير موجود / false / 0 تعني "غير مفعّل" */
    public function enabled(string $key): bool
    {
        return isset($_GET[$key]) && $_GET[$key] !== 'false' && $_GET[$key] !== '0';
    }
}
