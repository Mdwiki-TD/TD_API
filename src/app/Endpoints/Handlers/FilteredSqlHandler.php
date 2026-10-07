<?php
// src/app/Endpoints/Handlers/FilteredSqlHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;

use App\Endpoints\{AbstractEndpointHandler, EndpointContext, QuerySpec};

/** استعلام أساسي + فلاتر مع تعريف معلماته وأعمدته داخل الكلاس */
final class FilteredSqlHandler extends AbstractEndpointHandler
{
    public function __construct(
        private string $sql,
        private string $suffix = '',        // يُلصق بعد الفلاتر (GROUP BY ثابت ...)
        private string $defaultOrder = '',
        private bool $groupable = false,    // يدعم ?group= من المستخدم
        private array $params = [],
        private array $columns = [],
        private array $orderValues = [],
    ) {}

    public function handle(EndpointContext $ctx): QuerySpec
    {
        [$sql, $params] = $ctx->applyFilters($this->sql);
        $sql .= $this->suffix;

        if ($this->groupable) {
            $sql = $ctx->applyGroup($sql);
        }

        return new QuerySpec($sql, $params, defaultOrder: $this->defaultOrder);
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getOrderValues(): array
    {
        return $this->orderValues;
    }
}
