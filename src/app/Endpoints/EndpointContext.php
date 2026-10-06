<?php
// src/app/Endpoints/EndpointContext.php
declare(strict_types=1);
namespace App\Endpoints;

use App\Query\Ordering;
use App\Query\FilterBuilder;
use App\Query\SelectBuilder;
use App\Http\Request;

final class EndpointContext
{
    public readonly array $params;
    public readonly array $columns;
    public readonly string $select;
    public readonly string $distinct;
    public readonly ?string $group;
    public readonly ?string $order;

    public function __construct(
        public readonly string $get,
        public readonly array $data,
        public readonly Request $request,
    ) {
        $this->params   = $data['params'] ?? [];

        $this->columns  = $data['columns'] ?? [];
        $this->select   = SelectBuilder::build($this->params, $this->columns, $request);

        $this->distinct = $request->enabled('distinct') ? 'DISTINCT ' : '';
        $this->group    = $request->get('group');
        $this->order    = $request->get('order');
    }

    /** @return array{0: string, 1: array} [sql, params] */
    public function applyFilters(string $sql, array $ignore = []): array
    {
        return FilterBuilder::apply($sql, $this->params, $this->request, $ignore);
    }

    public function applyGroup(string $sql): string
    {
        return Ordering::group($sql, $this->data, $this->group);
    }
}
