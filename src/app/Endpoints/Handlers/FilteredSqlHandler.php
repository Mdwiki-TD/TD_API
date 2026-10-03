<?php
// src/app/Endpoints/Handlers/FilteredSqlHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;

use App\Endpoints\{EndpointContext, EndpointHandler, QuerySpec};
use function API\Helps\add_li_params;

/** استعلام أساسي + فلاتر من endpoint_params.json (user_access, language_settings ...) */
final class FilteredSqlHandler implements EndpointHandler
{
    public function __construct(private string $sql) {}

    public function handle(EndpointContext $ctx): QuerySpec
    {
        [$sql, $params] = add_li_params($this->sql, [], $ctx->params);
        return new QuerySpec($sql, $params);
    }
}
