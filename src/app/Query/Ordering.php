<?php
// src/app/Query/Ordering.php
declare(strict_types=1);

namespace App\Query;

use App\Http\Request;
use App\Logger;
use App\Endpoints\Definition\EndpointDefinition;

/** ORDER BY and GROUP BY: Accept only allowed columns defined in EndpointDefinition */
final class Ordering
{
    /**
     * @param EndpointDefinition $data
     * @return array<string, string> Allowed name => SQL expression
     */
    private static function allowed(EndpointDefinition $data): array
    {
        $map = [];

        foreach ($data->params as $p) {
            $name = $p->name;
            $col = $p->column;
            if ($name === '' || $col === '' || $p->noSelect) {
                continue;
            }
            $map[$name] = $col;
        }

        foreach ($data->columns as $c) {
            $map[$c] = $c;
        }

        return $map;
    }

    /** Allowed list separated by commas (or null) */
    private static function columns(string $value, EndpointDefinition $data): ?string
    {
        $allowed = self::allowed($data);
        $out = [];

        foreach (explode(',', $value) as $item) {
            $item = trim($item);
            if (isset($allowed[$item])) {
                $out[] = $allowed[$item];
            } elseif (ctype_digit($item) && (int) $item >= 1) {
                $out[] = $item;
            } else {
                Logger::debug("rejected order/group value '$item'");
            }
        }

        return $out ? implode(', ', $out) : null;
    }

    public static function group(string $sql, EndpointDefinition $data, ?string $value): string
    {
        if ($value === null || $value === '') {
            return $sql;
        }

        $cols = self::columns($value, $data);
        return $cols ? "$sql GROUP BY $cols" : $sql;
    }

    public static function order(string $sql, EndpointDefinition $data, ?string $value, Request $request): string
    {
        $orderParam = $data->getParam('order');

        if (!$orderParam) {
            return $sql;
        }

        $default = (string) ($orderParam->default ?? '');
        $hasValue = $value !== null && $value !== '';

        if (!$hasValue && $default === '') {
            return $sql;
        }

        $expr = $default;
        if ($hasValue) {
            $fromMap = (string) ($data->orderValues[$value] ?? '');
            $expr = $fromMap !== '' ? $fromMap : (self::columns($value, $data) ?? $default);
        }

        if ($expr === '') {
            return $sql;
        }

        $dir = self::direction($request);
        return "$sql ORDER BY $expr $dir";
    }

    private static function direction(Request $request): string
    {
        $raw = strtoupper($request->get('order_direction') ?? '');
        return in_array($raw, ['ASC', 'DESC'], true) ? $raw : 'DESC';
    }
}
