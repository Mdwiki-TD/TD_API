<?php
// src/app/OpenApi/OpenApiBuilder.php
declare(strict_types=1);

namespace App\OpenApi;

use App\Endpoints\{EndpointRegistry};
use App\Endpoints\Definition\{EndpointDefinition, Param};

final class OpenApiBuilder
{
    /** بارامترات عامة تطبّقها المنصة على كل endpoint (Pagination/الكاش/الترتيب) */
    private const COMMON = ['limit', 'offset', 'apcu', 'order_direction'];

    public function __construct(
        private EndpointRegistry $registry,
        private array $info,   // title, version, description
    ) {}

    public function build(): array
    {
        $paths = [];
        foreach ($this->registry->all() as $name => $handler) {
            $paths['/api.php?get=' . $name] = ['get' => $this->operation($name, $handler->definition())];
        }
        ksort($paths);   // ترتيب ثابت = diff نظيف في git

        return [
            'openapi'    => '3.0.3',
            'info'       => $this->info,
            'paths'      => $paths,
            'components' => $this->components(),
        ];
    }

    private function operation(string $name, EndpointDefinition $def): array
    {
        $params = [];
        foreach ($def->params as $p) {
            if (in_array($p->name, self::COMMON, true)) {
                continue;   // تُضاف كمراجع أدناه
            }
            $params[] = $this->parameter($p);
        }
        foreach (self::COMMON as $c) {
            $params[] = ['$ref' => "#/components/parameters/$c"];
        }

        $item = $def->responseOverride ?? $this->itemSchema($def->response);

        $op = [
            'tags'        => [$def->tag],
            'operationId' => $name,
            'summary'     => $def->summary ?: $name,
            'parameters'  => $params,
            'responses'   => [
                '200' => ['description' => 'OK', 'content' => ['application/json' => ['schema' => [
                    'allOf' => [
                        ['$ref' => '#/components/schemas/Envelope'],
                        ['type' => 'object', 'properties' => ['results' => $def->responseOverride
                            ? $item
                            : ['type' => 'array', 'items' => $item]]],
                    ],
                ]]]],
                '500' => ['$ref' => '#/components/responses/ServerError'],
            ],
        ];
        if ($def->description !== '') $op['description'] = $def->description;
        if ($def->deprecated)         $op['deprecated']  = true;
        return $op;
    }

    private function parameter(Param $p): array
    {
        $schema = match ($p->type) {
            'number' => ['type' => 'integer'],
            'switch' => ['type' => 'boolean'],
            'array'  => ['type' => 'array', 'items' => ['type' => 'string']],
            default  => ['type' => 'string'],
        };
        if ($p->options)            $schema['enum']    = $p->options;
        if ($p->default !== null)   $schema['default'] = $p->default;

        $desc = $p->description ?: $p->placeholder;
        // القيم الخاصة التي يفهمها FilterBuilder على أي فلتر عادي
        if (!$p->noSelect && $p->type === 'text') {
            $desc .= ($desc ? ' — ' : '') . 'special values: `empty`, `not_empty`';
        }

        $out = ['name' => $p->name, 'in' => 'query', 'required' => $p->required, 'schema' => $schema];
        if ($desc !== '')          $out['description'] = $desc;
        if ($p->example !== null)  $out['example']     = $p->example;
        return $out;
    }

    private function itemSchema(array $fields): array
    {
        $props = [];
        foreach ($fields as $name => $type) {
            $props[$name] = ['type' => $type];
        }
        return ['type' => 'object', 'properties' => $props];
    }

    private function components(): array
    {
        return [
            'parameters' => [
                'limit'           => ['name' => 'limit',  'in' => 'query', 'schema' => ['type' => 'integer', 'minimum' => 1]],
                'offset'          => ['name' => 'offset', 'in' => 'query', 'description' => 'Ignored without limit', 'schema' => ['type' => 'integer', 'minimum' => 1]],
                'apcu'            => ['name' => 'apcu',   'in' => 'query', 'description' => 'Use the APCu cache (12h TTL)', 'allowEmptyValue' => true, 'schema' => ['type' => 'boolean']],
                'order_direction' => ['name' => 'order_direction', 'in' => 'query', 'schema' => ['type' => 'string', 'enum' => ['ASC', 'DESC']]],
            ],
            'schemas' => [
                'Envelope' => ['type' => 'object', 'properties' => [
                    'time' => ['type' => 'string'],
                    'source' => ['type' => 'string', 'enum' => ['db', 'apcu']],
                    'length' => ['type' => 'integer'],
                    'error' => ['type' => 'object', 'properties' => ['error' => ['type' => 'string']]],
                    'supported_params' => ['type' => 'array', 'items' => ['type' => 'string']],
                    'supported_values' => ['type' => 'object'],
                    'columns' => ['type' => 'array', 'items' => ['type' => 'string']],
                ]],
            ],
            'responses' => [
                'ServerError' => ['description' => 'Internal error', 'content' => ['application/json' => ['schema' => [
                    'type' => 'object',
                    'properties' => ['error' => ['type' => 'object']],
                ]]]],
            ],
        ];
    }
}
