<?php
// src/app/Endpoints/EndpointContext.php
declare(strict_types=1);

namespace App\Endpoints;

use App\Http\Request;
use function API\SelectHelps\get_select;

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
        $this->select   = get_select($this->params, $this->columns);
        $this->distinct = $request->enabled('distinct') ? 'DISTINCT ' : '';
        $this->group    = $request->get('group');
        $this->order    = $request->get('order');
    }
}
