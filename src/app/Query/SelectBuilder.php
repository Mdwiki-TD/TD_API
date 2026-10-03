<?php
// src/app/Query/SelectBuilder.php
declare(strict_types=1);

namespace App\Query;

use App\Http\Request;

final class SelectBuilder
{
    private const OFF = ['false', '0', 'select'];

    private const VALID = [
        'count',
        'count(*) as count',
        'count(title) as count',
        'count(p.title) as count',
        'year(date) as year',
        'year(p.date) as year',
        'year(pupdate) as year',
        'year(p.pupdate) as year',
        'lang',
        'p.lang',
        'p.user',
        'user',
        'g_title',
    ];

    private const ALIAS = [
        'count(*)' => 'count(*) as count',
        'year'     => 'year(pupdate) as year',
    ];

    public static function build(array $endpointParams, array $columns, Request $request): string
    {
        $select = $request->get('select');
        if ($select === null || $select === '' || $select === '*' || in_array($select, self::OFF, true)) {
            return '*';
        }

        $lower    = strtolower($select);
        $resolved = self::ALIAS[$lower] ?? $select;

        $names   = array_column($endpointParams, 'name');
        $options = array_column($endpointParams, null, 'name')['select']['options'] ?? [];

        $allowed = in_array($lower, self::VALID, true)
            || in_array($lower, $names, true)
            || in_array($lower, $options, true)
            || in_array($lower, $columns, true);

        if (!$allowed) {
            $resolved = '*';
        }

        // سلوك قديم: COUNT يُلصق حتى لو رُجع إلى *
        $count = $request->get('count');
        if ($count !== null && ($count === '*' || in_array($count, $columns, true))) {
            $resolved .= ", COUNT($count) as count";
        }

        return $resolved;
    }
}
