<?php
// src/app/Formatting/GraphDataFormatter.php
declare(strict_types=1);

namespace App\Formatting;

final class GraphDataFormatter
{
    public static function format(array $rows): array
    {
        $result = [
            'labels' => array_column($rows, 'date'),
            'counts' => array_column($rows, 'count'),
        ];
        return $result;
    }
}
