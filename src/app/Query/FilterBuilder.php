<?php
// src/app/Query/FilterBuilder.php
declare(strict_types=1);

namespace App\Query;

use App\Http\Request;
use App\Endpoints\Definition\Param;

/**
 * Change endpoint_params.json parameters to WHERE conditions with placeholders
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
     * @param  list<Param> $endpointParams
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
        $filterdList = self::filters($endpointParams, $ignore);

        foreach ($filterdList as $def) {
            $column = $def->column;
            if ($column === '' || (!$request->has($def->name) && !$request->has($column))) {
                continue;
            }

            $value = $request->get($def->name) ?? '';
            if ($value === '') {
                $value = $request->get($column) ?? '';
            }

            if ($column === 'limit' || $column === 'select' || !self::isValid($value)) {
                continue;
            }
            if ($def->noEmptyValue === true && empty($value)) {
                continue;
            }

            if ($column === 'distinct' && $value === '1') {
                if (stripos($sql, 'distinct') === false) {
                    $sql = preg_replace('/^\s*SELECT\s*/i', 'SELECT DISTINCT ', $sql) ?? $sql;
                }
                continue;
            }

            [$part, $new] = self::condition($sql, $column, $value, $def, $request);
            $sql .= $part;
            $params = array_merge($params, $new);
        }

        return [$sql, $params];
    }

    /**
     * @param  list<Param> $endpointParams
     * @param  list<string> $ignore parameters to handled manually
     * @return list<Param>
     *
     */
    private static function filters(array $endpointParams, array $ignore): array
    {
        $out = [];
        foreach ($endpointParams as $p) {
            if ($p->noSelect) {
                continue;
            }
            if (in_array($p->name, $ignore, true)) {
                continue;
            }
            $out[] = $p;
        }
        return $out;
    }

    /** @return array{0: string, 1: array} */
    private static function condition(
        string $sql,
        string $column,
        string $value,
        Param $def,
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

        if ($def->type === 'array') {
            $values = $request->getArray($def->name);
            if ($values === []) {
                return ['', []];
            }
            $marks = implode(',', array_fill(0, count($values), '?'));
            return [" $glue $column IN ($marks)", $values];
        }

        if (!empty($def->valueCanBeNull)) {
            return [" $glue ($column = ? OR $column IS NULL OR $column = '')", [$value]];
        }
        return [" $glue $column = ?", [$value]];
    }
}
