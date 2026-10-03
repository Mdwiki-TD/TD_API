<?php
// src/app/Endpoints/Handlers/FilteredSqlHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;

use App\Endpoints\{EndpointContext, EndpointHandler, QuerySpec};
use function API\Helps\{add_li_params, add_group};


/** استعلام أساسي + فلاتر من endpoint_params.json (user_access, language_settings ...) */

final class FilteredSqlHandler implements EndpointHandler
{
    public function __construct(
        private string $sql,
        private string $suffix = '',        // يُلصق بعد الفلاتر (GROUP BY ثابت ...)
        private string $defaultOrder = '',
        private bool $groupable = false,    // يدعم ?group= من المستخدم
    ) {}

    public function handle(EndpointContext $ctx): QuerySpec
    {
        [$sql, $params] = add_li_params($this->sql, [], $ctx->params);
        $sql .= $this->suffix;

        if ($this->groupable) {
            $sql = add_group($sql, $ctx->data, $ctx->group);
        }

        return new QuerySpec($sql, $params, defaultOrder: $this->defaultOrder);
    }
}
