<?php
// src/app/APIController.php
declare(strict_types=1);

namespace App;

use App\Database\QueryExecutor;
use App\Endpoints\{EndpointContext, EndpointRegistry};
use App\Formatting\ResponseBuilder;
use App\Http\Request;
use App\Database\Database;
use Throwable;

/*
index.php
  → bootstrap (env, autoload)
  → APIController::handleRequest()
        1. Request            ← Reads $_GET once
        2. EndpointRegistry   ← Resolves Handler and EndpointDefinition
        3. EndpointContext    ← Resolves get, params, columns, select, distinct, group
        4. Handler->handle()  ← Returns a QuerySpec (query, params, error, skipOrder?)
        5. QueryExecutor      ← Applies order/limit/offset + Cache + Database
        6. Formatter          ← Applies custom formatting (leaderboard, langs)
        7. ResponseBuilder    ← Builds metadata (time, source, length, supported_params)
        8. JsonResponse       ← Outputs JSON response
*/

class APIController
{
    private Database $db;

    public function __construct(
        ?Database $db = null,
        private ?EndpointRegistry $registry = null,
        private ?QueryExecutor $executor = null,
        private ?ResponseBuilder $builder = null,
        private ?Request $request = null,
    ) {
        $this->db       = $db ?? new Database();
        $this->registry ??= new EndpointRegistry();
        $this->executor ??= new QueryExecutor($this->db);
        $this->builder  ??= new ResponseBuilder();
        $this->request  ??= new Request();
    }
    /**
     * Main entry point
     */
    public function handleRequest(): void
    {
        if ($this->db->isDbNull()) {
            error_log('Database is null');
        }

        $get = $this->request->get('get') ?? '';

        header('Content-Type: application/json');

        try {
            $resolved = $this->registry->resolve($get);
            if ($resolved === null) {
                $this->emit($this->builder->errorOnly('invalid get request'));
                return;
            }
            [$handler, $definition] = $resolved;

            $ctx  = new EndpointContext($get, $definition->toArray(), $this->request);

            $spec = $handler->handle($ctx);
            if ($spec->sql === '') {
                $this->emit($this->builder->build($ctx, error: $spec->error));
                return;
            }

            $run     = $this->executor->run($spec, $ctx);
            $results = $this->builder->format($get, $run['results']);

            $this->emit($this->builder->build(
                $ctx,
                $results,
                $run['source'],
                $run['time'],
                $run['sql'],
                $spec->params,
                error: $spec->error
            ));
        } catch (Throwable $e) {
            error_log('[API] ' . $e->getMessage());
            http_response_code(500);
            $result = $this->builder->errorOnly('internal error');
            $result["e"] = $e->getMessage();
            $this->emit($result);
        }
    }

    private function emit(array $data): void
    {
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
