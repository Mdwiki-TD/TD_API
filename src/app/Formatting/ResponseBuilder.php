<?php
// src/app/Formatting/ResponseBuilder.php
declare(strict_types=1);

namespace App\Formatting;

use App\Endpoints\EndpointContext;
use App\Formatting\GraphDataFormatter;
use App\Formatting\LeaderboardFormatter;
use App\Formatting\UserDataStatusFormatter;
use App\Formatting\LangsFormatter;
use App\Http\Environment;

final class ResponseBuilder
{
    public function format(string $get, array $results): array
    {
        return match ($get) {
            // 'graph_data'              => GraphDataFormatter::format($results),
            'user_data_status'           => UserDataStatusFormatter::format($results),
            'leaderboard_table_formated' => LeaderboardFormatter::format($results),
            'langs'                      => LangsFormatter::format($results),
            default                      => $results,
        };
    }
    public function build(
        EndpointContext $ctx,
        array $results = [],
        string $source = 'db',
        string $time = '0',
        string $sql = '',
        array $params = [],
        string $error = '',
    ): array {
        $out = [
            'time'    => $time,
            'query'   => '',
            'source'  => $source,
            'length'  => count($results),
            'results' => $results,
        ];

        if (!Environment::isLocalhost()) {
            unset($out['query']);
        } else {
            $out['query'] = trim(preg_replace('/\s+/', ' ', $sql));
            // apply $params to $qua
            $out['query'] = " " . sprintf(str_replace('?', "'%s'", $out['query']), ...$params) . " ";
        }

        if ($error !== '') {
            $out['error'] = ['error' => $error];   // same old format
        }

        $out['supported_params'] = array_column($ctx->params, 'name');

        // supported_values should not have null values
        $out['supported_values'] = array_filter(array_column($ctx->params, 'options', 'name'), fn($value) => $value !== null);

        $out['columns'] = $ctx->columns;
        return $out;
    }

    public function errorOnly(string $message): array
    {
        return ['error' => ['error' => $message], 'results' => [], 'length' => 0];
    }
}
