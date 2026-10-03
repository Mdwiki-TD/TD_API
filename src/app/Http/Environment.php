<?php
// src/app/Http/Environment.php
declare(strict_types=1);
namespace App\Http;

final class Environment
{
    public static function isLocalhost(): bool
    {
        return ($_SERVER['SERVER_NAME'] ?? '') === 'localhost';
    }
}
