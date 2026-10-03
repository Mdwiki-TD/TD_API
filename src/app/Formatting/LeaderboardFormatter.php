<?php
// src/app/Formatting/LeaderboardFormatter.php
declare(strict_types=1);

namespace App\Formatting;

final class LeaderboardFormatter
{
    public static function format(array $rows): array
    {
        $result = ['by_lang' => [], 'by_user' => [], 'by_month' => []];

        foreach ($rows as $row) {
            $month = $row['m'] ?? '';
            $lang  = $row['lang'] ?? '';
            $user  = $row['user'] ?? '';
            $words = (int) ($row['word'] ?? 0);
            $views = (int) ($row['views'] ?? 0);

            $result['by_month'][$month] = ($result['by_month'][$month] ?? 0) + 1;

            foreach (['by_lang' => $lang, 'by_user' => $user] as $bucket => $key) {
                $result[$bucket][$key] ??= ['pages' => 0, 'words' => 0, 'views' => 0];
                $result[$bucket][$key]['pages'] += 1;
                $result[$bucket][$key]['words'] += $words;
                $result[$bucket][$key]['views'] += $views;
            }
        }
        return $result;
    }
}
