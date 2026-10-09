<?php
// src/app/Endpoints/Handlers/QidsHandler.php
declare(strict_types=1);
/*
# This will make the query time > x10
            # AND A.id < B.id
*/
namespace App\Endpoints\Handlers;
use App\Endpoints\{EndpointContext, QuerySpec};

use App\Endpoints\DefinedEndpoint;
use App\Endpoints\Definition\Param;
use App\Endpoints\Definition\EndpointDefinition;

final class QidsHandler implements DefinedEndpoint
{

    private const TABLES = ['qids', 'qids_others'];

    private const QUERIES = [
        'empty'     => "SELECT id, title, qid FROM {table} WHERE (qid = '' OR qid IS NULL)",
        'all'       => 'SELECT id, title, qid FROM {table}',
        'duplicate' => <<<SQL
            SELECT
                A.id AS id, A.title AS title, A.qid AS qid,
                B.id AS id2, B.title AS title2, B.qid AS qid2
            FROM {table} A
            JOIN {table} B ON A.qid = B.qid
            WHERE A.qid != '' AND A.title != B.title AND A.id != B.id
            SQL,
    ];

    private string $table;

    public function __construct($table)
    {
        $this->table = in_array($table, self::TABLES, true) ? $table : 'qids';
    }
    public function definition(): EndpointDefinition
    {
        $definitions = [
            'qids'        => new EndpointDefinition(
                endpoint: 'qids',
                summary: 'Retrieve QIDs',
                tag: 'identifiers',
                params: [
                    new Param(
                        name: 'dis',
                        column: 'dis',
                        type: 'select',
                        options: ['', 'empty', 'all', 'duplicate']
                    ),
                ],
                columns: ['title', 'qid'],
            ),
            'qids_others' => new EndpointDefinition(
                endpoint: 'qids_others',
                summary: 'Retrieve other QIDs',
                tag: 'identifiers',
                params: [
                    new Param(
                        name: 'dis',
                        column: 'dis',
                        type: 'select',
                        options: ['', 'empty', 'all', 'duplicate']
                    ),
                ],
                columns: ['title', 'qid'],
            ),
        ];
        return $definitions[$this->table];
    }
    public function handle(EndpointContext $ctx): QuerySpec
    {
        $dis = $ctx->request->get('dis');
        $template = self::QUERIES[$dis] ?? self::QUERIES['all'];

        return new QuerySpec(str_replace('{table}', $this->table, $template));
    }
}
