<?php
// src/app/Query/Ordering.php
declare(strict_types=1);
namespace App\Query;

use App\Http\Request;
use App\Logger;

/** ORDER BY و GROUP BY: كلاهما يقبل فقط أعمدة معرّفة في JSON */
final class Ordering
{
    /** @return array<string, string> الاسم المسموح => تعبير SQL */
    private static function allowed(array $data): array
    {
        $map = [];
        foreach ($data['params'] ?? [] as $p) {
            $name = $p['name'] ?? '';
            $col  = $p['column'] ?? '';
            if ($name === '' || $col === '' || isset($p['noSelect'])) {
                continue;
            }
            $map[$name] = $col;   // اسم البارامتر يُترجم إلى عموده الحقيقي
        }
        foreach ($data['columns'] ?? [] as $c) {
            $map[$c] = $c;        // الأعمدة لها الأولوية
        }
        return $map;
    }

    /** قائمة مفصولة بفواصل، تُرجَع المسموح منها فقط (أو null) */
    public static function columns(string $value, array $data): ?string
    {
        $allowed = self::allowed($data);
        $out = [];

        foreach (explode(',', $value) as $item) {
            $item = trim($item);
            if (isset($allowed[$item])) {
                $out[] = $allowed[$item];
            } elseif (ctype_digit($item) && (int) $item >= 1) {
                $out[] = $item;   // ترتيب بموضع العمود
            } else {
                Logger::debug("rejected order/group value '$item'");
            }
        }
        return $out ? implode(', ', $out) : null;
    }

    public static function group(string $sql, array $data, ?string $value): string
    {
        if ($value === null || $value === '') {
            return $sql;
        }
        $cols = self::columns($value, $data);
        return $cols ? "$sql GROUP BY $cols" : $sql;
    }

    public static function order(string $sql, array $data, ?string $value, Request $request): string
    {
        $params     = array_column($data['params'] ?? [], null, 'name');
        $orderParam = $params['order'] ?? null;
        if (!$orderParam) {
            return $sql;
        }

        $default  = (string) ($orderParam['default'] ?? '');
        $hasValue = $value !== null && $value !== '';

        if (!$hasValue && $default === '') {
            return $sql;
        }

        $expr = $default;
        if ($hasValue) {
            $fromMap = (string) ($data['order_values'][$value] ?? '');
            $expr = $fromMap !== '' ? $fromMap : (self::columns($value, $data) ?? $default);
        }
        if ($expr === '') {
            return $sql;
        }

        $dir = self::direction($params['order_direction'] ?? [], $request);
        return "$sql ORDER BY $expr $dir";
    }

    private static function direction(array $def, Request $request): string
    {
        $raw = strtoupper($request->get('order_direction') ?? (string) ($def['default'] ?? ''));
        return in_array($raw, ['ASC', 'DESC'], true) ? $raw : 'DESC';
    }
}
