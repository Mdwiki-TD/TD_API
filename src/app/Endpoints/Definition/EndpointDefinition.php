<?php

declare(strict_types=1);

namespace App\Endpoints\Definition;

final class EndpointDefinition
{
    /**
     * @param list<Param>          $params
     * @param list<string>         $columns     الأعمدة المسموح بها في select/order/group
     * @param array<string,string> $orderValues
     */
    public function __construct(
        public readonly string $summary,
        public readonly string $tag,
        public readonly array $params = [],
        public readonly array $columns = [],
        public readonly array $orderValues = [],
        public readonly string $description = '',   // فارغ = الوصف القياسي
    ) {}

    /** الشكل الذي تقرؤه Query/* و ResponseBuilder */
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
