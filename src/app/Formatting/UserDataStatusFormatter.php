<?php
// src/app/Formatting/LeaderboardFormatter.php
declare(strict_types=1);

namespace App\Formatting;

final class UserDataStatusFormatter
{
    public static function format(array $rows): array
    {
        $result = [
            "years" => [],
            "langs" => [],
            "camps" => [],
        ];

        foreach ($rows as $row) {
            $year      = $row['year'] ?? '';
            $lang      = $row['lang'] ?? '';
            $campaign  = $row['campaign'] ?? '';

            foreach (['years' => $year, 'langs' => $lang, 'camps' => $campaign] as $bucket => $key) {
                $result[$bucket][$key] ??= 0;
                $result[$bucket][$key] += 1;
            }
        }
        // { "years": { "2021": 6, ... }, "langs": { "ar": 14, ... }, "camps": { "Main": 12, ... } }
        return $result;
    }
}
