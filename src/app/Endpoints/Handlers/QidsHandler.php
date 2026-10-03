<?php
// src/app/Endpoints/Handlers/QidsHandler.php
declare(strict_types=1);
/*
# This will make the query time > x10
            # AND A.id < B.id
*/
namespace App\Endpoints\Handlers;

use App\Endpoints\{EndpointContext, EndpointHandler, QuerySpec};

final class QidsHandler implements EndpointHandler
{
    private const TABLES = ['qids', 'qids_others'];

    private const QUERIES = [
        'empty' => "SELECT id, title, qid FROM {table} WHERE (qid = '' OR qid IS NULL)",
        'all'   => 'SELECT id, title, qid FROM {table}',
        'duplicate' => <<<SQL
            SELECT
                A.id AS id, A.title AS title, A.qid AS qid,
                B.id AS id2, B.title AS title2, B.qid AS qid2
            FROM {table} A
            JOIN {table} B ON A.qid = B.qid
            WHERE A.qid != '' AND A.title != B.title AND A.id != B.id
            SQL,
    ];

    public function handle(EndpointContext $ctx): QuerySpec
    {
        $table = in_array($ctx->get, self::TABLES, true) ? $ctx->get : 'qids';

        $dis = $ctx->request->get('dis');
        $template = self::QUERIES[$dis] ?? self::QUERIES['all'];

        return new QuerySpec(str_replace('{table}', $table, $template));
    }
}
