<?php
// src/app/Database/QueryExecutor.php
declare(strict_types=1);

namespace App\Database;

use App\Database\Cache\{ApcuCache, CacheInterface, NullCache};
use App\Endpoints\{EndpointContext, QuerySpec};
use function API\Helps\{add_order, add_limit, add_offset};

final class QueryExecutor
{
    /** endpoints لا تُخزَّن أبداً في الكاش */
    private const NO_CACHE = ['settings'];

    private CacheInterface $cache;

    public function __construct(private Database $db, ?CacheInterface $cache = null)
    {
        $this->cache = $cache ?? (ApcuCache::isAvailable() ? new ApcuCache() : new NullCache());
    }

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

        $useCache = $ctx->request->enabled('apcu') && !in_array($ctx->get, self::NO_CACHE, true);

        $results = $useCache ? $this->cache->get($sql, $spec->params) : null;
        $source  = 'apcu';

        if ($results === null) {
            $results = $this->db->fetchQuery($sql, $spec->params);
            $source  = 'db';
            if ($useCache && $results) {
                $this->cache->set($sql, $spec->params, $results);
            }
        }

        return [
            'results' => $results,
            'source'  => $source,
            'sql'     => $sql,
            'time'    => number_format(microtime(true) - $start, 2),
        ];
    }
}
