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
        // public readonly string $name,
        public readonly string $endpoint,
        public readonly string $summary,
        public readonly string $tag,
        public readonly array $params = [],
        public readonly array $columns = [],
        public readonly array $orderValues = [],
        public readonly string $description = '',   // empty = default description
    ) {
    }

    public function getrequiredParams(): array
    {
        return array_filter($this->params, static fn(Param $p): bool => $p->required);
    }
    public function getParam(string $name): Param|null
    {
        foreach ($this->params as $param) {
            if ($param->name === $name) {
                return $param;
            }
        }
        return null;
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
