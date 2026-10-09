<?php

declare(strict_types=1);

namespace App\Endpoints\Definition;

final class EndpointDefinition
{
    /**
     * @param list<Param>          $params
     * @param list<string>         $columns     allowed columns in select/order/group
     * @param array<string,string> $orderValues
     */
    public function __construct(
        public readonly string $summary,
        public readonly string $tag,
        public readonly array $params = [],
        public readonly array $columns = [],
        public readonly array $orderValues = [],
        public readonly string $description = '',   // empty = default description
    ) {
    }

    /**
     * With form of Query/* and ResponseBuilder (alternative to endpoint_params.json)
     * */
    public function toArray(): array
    {
        $a = [
            'columns' => $this->columns,
            'params'  => array_map(static fn(Param $p): array => $p->toArray(), $this->params),
        ];
        if ($this->orderValues) {
            $a['order_values'] = $this->orderValues;
        }
        return $a;
    }
}
