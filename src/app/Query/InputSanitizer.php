<?php
// src/app/Query/InputSanitizer.php
declare(strict_types=1);
namespace App\Query;

final class InputSanitizer
{
    /** القيمة إن طابقت النمط، وإلا null. ("all" والفارغ مرفوضان كما في القديم) */
    public static function match(?string $value, string $pattern): ?string
    {
        if ($value === null || empty($value) || $value === 'all') {
            return null;
        }
        return preg_match($pattern, $value) === 1 ? $value : null;
    }
}
