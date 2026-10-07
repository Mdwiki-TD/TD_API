<?php
// src/app/Query/FilterBuilder.php
declare(strict_types=1);

namespace App\Query;

use App\Http\Request;

/**
 * Change endpoint definition parameters to WHERE conditions with placeholders
 */
final class FilterBuilder
{
    public static function isValid(?string $v): bool
    {
        return $v !== null && $v !== '' && strtolower($v) !== 'all';
    }

    /**
     * Check if `AND` in sql query else `WHERE`
     * @param string $sql
     * @return string
     */
    public static function glue(string $sql): string
    {
        return preg_match('/\bWHERE\b/i', $sql) === 1 ? ' AND' : ' WHERE';
    }

    /**
     * @param  array<int, array<string, mixed>> $endpointParams
     * @param  list<string> $ignore parameters to handled manually
     * @return array{0: string, 1: array} [sql, params]
     */
    public static function apply(
        string $sql,
        array $endpointParams,
        Request $request,
        array $ignore = []
    ): array {
        $params = [];

        foreach (self::filters($endpointParams, $ignore) as $name => $def) {
            $column = (string) ($def['column'] ?? '');
            if ($column === '' || (!$request->has($name) && !$request->has($column))) {
                continue;
            }

            $value = $request->get($name) ?? '';
            if ($value === '') {
                $value = $request->get($column) ?? '';
            }

            if ($column === 'limit' || $column === 'select' || !self::isValid($value)) {
                continue;
            }
            if (isset($def['no_empty_value']) && empty($value)) {
                continue;
            }

            if ($column === 'distinct' && $value === '1') {
                if (stripos($sql, 'distinct') === false) {
                    $sql = preg_replace('/^\s*SELECT\s*/i', 'SELECT DISTINCT ', $sql) ?? $sql;
                }
                continue;
            }

            [$part, $new] = self::condition($sql, $name, $column, $value, $def, $request);
            $sql   .= $part;
            $params = array_merge($params, $new);
        }

        return [$sql, $params];
    }

    /** @return array<string, array<string, mixed>> */
    private static function filters(array $endpointParams, array $ignore): array
    {
        $out = [];
        foreach ($endpointParams as $p) {
            if (!isset($p['name']) || isset($p['no_select'])) {
                continue;
            }
            $out[$p['name']] = $p;
        }
        return array_diff_key($out, array_flip($ignore));
    }

    /** @return array{0: string, 1: array} */
    private static function condition(
        string $sql,
        string $name,
        string $column,
        string $value,
        array $def,
        Request $request
    ): array {
        $glue = self::glue($sql);

        if ($value === 'not_mt' || $value === 'not_empty') {
            return [" $glue ($column != '' AND $column IS NOT NULL)", []];
        }
        if ($value === 'mt' || $value === 'empty') {
            return [" $glue ($column = '' OR $column IS NULL)", []];
        }
        if ($value === '>0') {
            return [" $glue $column > 0", []];
        }

        if (($def['type'] ?? '') === 'array') {
            $values = $request->getArray($name);
            if ($values === []) {
                return ['', []];
            }
            $marks = implode(',', array_fill(0, count($values), '?'));
            return [" $glue $column IN ($marks)", $values];
        }

        if (!empty($def['value_can_be_null'])) {
            return [" $glue ($column = ? OR $column IS NULL OR $column = '')", [$value]];
        }
        return [" $glue $column = ?", [$value]];
    }
}
