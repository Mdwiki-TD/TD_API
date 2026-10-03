<?php
// src/app/Query/Pagination.php
declare(strict_types=1);
namespace App\Query;

use App\Http\Request;

final class Pagination
{
    public static function apply(string $sql, Request $request): string
    {
        $hasLimit = preg_match('/\bLIMIT\s+\d+/i', $sql) === 1;

        if (!$hasLimit && ($limit = $request->int('limit')) > 0) {
            $sql .= " LIMIT $limit";
            $hasLimit = true;
        }

        // OFFSET بلا LIMIT خطأ في MySQL، فيُتجاهل
        if ($hasLimit && preg_match('/\bOFFSET\s+\d+/i', $sql) !== 1) {
            $offset = $request->int('offset');
            if ($offset > 0) {
                $sql .= " OFFSET $offset";
            }
        }
        return $sql;
    }
}
