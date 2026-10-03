<?php
// src/app/Database/QueryExecutor.php
declare(strict_types=1);

namespace App\Database;

use App\Endpoints\{EndpointContext, QuerySpec};
use function API\SQL\fetch_query_new;
use function API\Helps\{add_order, add_limit, add_offset};

final class QueryExecutor
{
    /** @return array{results: array, source: string, sql: string, time: string} */
    public function run(QuerySpec $spec, EndpointContext $ctx): array
    {
        $start = microtime(true);

        $sql = $spec->sql;
        if ($spec->applyOrder) {
            $ordered = add_order($sql, $ctx->data, $ctx->order);
            if ($ordered === $sql && $spec->defaultOrder !== '') {
                $ordered .= ' ORDER BY ' . $spec->defaultOrder;
            }
            $sql = $ordered;
        }

        $sql = add_offset(add_limit($sql));
        [$results, $source] = fetch_query_new($sql, $spec->params, $ctx->get);

        return [
            'results' => $results,
            'source'  => $source,
            'sql'     => $sql,
            'time'    => number_format(microtime(true) - $start, 2),
        ];
    }
}
