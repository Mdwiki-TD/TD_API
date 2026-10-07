<?php
// src/app/Logger.php
declare(strict_types=1);

namespace App;

final class Logger
{
    private static ?bool $debug = null;

    private static function isDebug(): bool
    {
        return self::$debug ??= ((getenv('APP_ENV') ?: ($_ENV['APP_ENV'] ?? '')) === 'development');
    }

    public static function debug(mixed $s): void
    {
        if (self::isDebug()) {
            error_log('[debug] ' . (is_string($s) ? $s : print_r($s, true)));
        }
    }

    public static function error(string $message): void
    {
        error_log($message);
    }
}
