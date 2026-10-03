<?php
// src/app/Formatting/ResponseBuilder.php
declare(strict_types=1);

namespace App\Formatting;

use App\Endpoints\EndpointContext;
use App\Http\Environment;
use function API\Leaderboard\{leaderboard_table_format, langs_format};

final class ResponseBuilder
{
    public function format(string $get, array $results): array
    {
        return match ($get) {
            'leaderboard_table_formated' => leaderboard_table_format($results),
            'langs'                      => langs_format($results),
            default                      => $results,
        };
    }

    public function build(
        EndpointContext $ctx,
        array $results = [],
        string $source = 'db',
        string $time = '0',
        string $sql = '',
        string $params = '',
        string $error = '',
    ): array {
        $out = [
            'time'   => $time,
            'query'  => '',
            'source' => $source,
            'length' => count($results),
            'results' => $results,
        ];

        if (!Environment::isLocalhost()) {
            unset($out['query']);
        } else {
            $out['query'] = trim(preg_replace('/\s+/', ' ', $sql));
        }

        if ($error !== '') {
            $out['error'] = ['error' => $error];   // نفس الشكل القديم
        }

        $out['supported_params'] = array_column($ctx->params, 'name');
        $out['supported_values'] = array_column($ctx->params, 'options', 'name');
        $out['columns']          = $ctx->columns;
        return $out;
    }

    public function errorOnly(string $message): array
    {
        return ['error' => ['error' => $message], 'results' => [], 'length' => 0];
    }
}
