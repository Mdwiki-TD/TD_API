<?php
// src/app/Endpoints/Handlers/FilteredSqlHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;
use App\Endpoints\{EndpointContext, EndpointHandler, QuerySpec};


/**
 * Base query + filters from endpoint_params.json (user_access, language_settings ...)
 */

final class FilteredSqlHandler implements EndpointHandler
{
    public function __construct(
        private string $sql,
        private string $suffix = '',        // Appended after filters (constant GROUP BY ...)
        private string $defaultOrder = '',
        private bool $groupable = false,    // Supports ?group= from the user
    ) {
    }

    public function handle(EndpointContext $ctx): QuerySpec
    {
        [$sql, $params] = $ctx->applyFilters($this->sql);
        $sql .= $this->suffix;

        if ($this->groupable) {
            $sql = $ctx->applyGroup($sql);
        }

        return new QuerySpec($sql, $params, defaultOrder: $this->defaultOrder);
    }
}

