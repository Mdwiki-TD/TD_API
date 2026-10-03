<?php
// src/app/Formatting/LangsFormatter.php
declare(strict_types=1);
namespace App\Formatting;

final class LangsFormatter
{
    public static function format(array $rows): array
    {
        foreach ($rows as &$row) {
            $decoded = json_decode((string) ($row['redirects'] ?? '[]'), true);
            $row['redirects'] = is_array($decoded) ? $decoded : [];
        }
        return $rows;
    }
}
