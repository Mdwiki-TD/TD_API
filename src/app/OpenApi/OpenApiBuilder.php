<?php
// src/app/OpenApi/OpenApiBuilder.php
declare(strict_types=1);

namespace App\OpenApi;

use App\Endpoints\Definition\{EndpointDefinition, Param};

/**
 * يولّد openapi.json (3.0.0، متوافق مع Swagger UI 4.5) من EndpointDefinition لكل endpoint
 * + OpenApiCatalog (info/servers/tags/المكوّنات المشتركة).
 *
 * ربط البارامتر بالوثيقة:
 *   Param::$doc = string → مكوّن مشترك صريح | array → inline كامل
 *   null → مكوّن مشترك واحد بنفس الاسم (غير حساس لحالة الأحرف)، وإلا inline مولَّد من Param
 *   limit و offset يُضافان تلقائياً (Pagination تطبقهما على كل endpoint)
 */
final class OpenApiBuilder
{
    private const DEFAULT_DESCRIPTION = 'Corresponds to calling `api.php?get=%s` with query parameters.';

    /** @var array<string, list<string>> اسم بارامتر (lower) => مكوّنات تحمله */
    private array $byName = [];

    /** @param array<string, EndpointDefinition> $definitions */
    public function __construct(private array $definitions, private array $catalog)
    {
        foreach ($catalog['parameters'] as $key => $c) {
            $this->byName[strtolower($c['name'])][] = $key;
        }
    }

    public function build(): array
    {
        $paths = [];
        foreach ($this->definitions as $name => $def) {
            $paths["/api.php?get=$name"] = ['get' => [
                'summary'     => $def->summary,
                'description' => $def->description !== '' ? $def->description : sprintf(self::DEFAULT_DESCRIPTION, $name),
                'tags'        => [$def->tag],
                'parameters'  => $this->parameters($def),
                'responses'   => ['200' => ['$ref' => '#/components/responses/Success']],
            ]];
        }

        return [
            'openapi'    => '3.0.0',
            'info'       => $this->catalog['info'],
            'servers'    => $this->catalog['servers'],
            'tags'       => $this->catalog['tags'],
            'components' => [
                'parameters' => $this->catalog['parameters'],
                'responses'  => $this->catalog['responses'],
            ],
            'paths'      => $paths,
        ];
    }

    private function parameters(EndpointDefinition $def): array
    {
        $out  = [];
        $seen = [];
        foreach ($def->params as $p) {
            $out[] = $this->parameter($p);
            $seen[strtolower($p->name)] = true;
        }
        if (!isset($seen['limit']))  array_unshift($out, ['$ref' => '#/components/parameters/LimitParam']);
        if (!isset($seen['offset'])) $out[] = ['$ref' => '#/components/parameters/OffsetParam'];
        return $out;
    }

    private function parameter(Param $p): array
    {
        if (is_array($p->doc)) {
            return $p->doc;
        }
        $key = $p->doc ?? $this->uniqueComponent($p->name);
        return $key !== null ? ['$ref' => "#/components/parameters/$key"] : $this->inline($p);
    }

    private function uniqueComponent(string $name): ?string
    {
        $c = $this->byName[strtolower($name)] ?? [];
        return count($c) === 1 ? $c[0] : null;
    }

    private function inline(Param $p): array
    {
        $schema = match ($p->type) {
            'number' => ['type' => 'number'],
            'switch' => ['type' => 'boolean'],
            'array'  => ['type' => 'array', 'items' => ['type' => 'string']],
            default  => ['type' => 'string'],
        };
        if ($p->options) {
            $schema['enum'] = $p->options;
        }
        if ($p->default !== null) {
            $schema['default'] = $p->default;
        }
        return [
            'in' => 'query',
            'name' => $p->name,
            'description' => $p->placeholder,
            'required' => $p->required,
            'schema' => $schema,
        ];
    }

    /** أخطاء الاتساق: تُستخدم في bin/build-openapi.php وفي الاختبارات */
    public function validate(array $registryNames): array
    {
        $errors = [];
        foreach (array_diff($registryNames, array_keys($this->definitions)) as $n) {
            $errors[] = "endpoint '$n' has no definition";
        }
        foreach (array_diff(array_keys($this->definitions), $registryNames) as $n) {
            $errors[] = "defined endpoint '$n' is not registered";
        }
        $tags = array_column($this->catalog['tags'], 'name');
        foreach ($this->definitions as $n => $d) {
            if ($d->summary === '' || str_starts_with($d->summary, 'TODO')) {
                $errors[] = "$n: summary is missing";
            }
            if (!in_array($d->tag, $tags, true)) {
                $errors[] = "$n: unknown tag '{$d->tag}'";
            }
            foreach ($d->params as $p) {
                if (is_string($p->doc) && !isset($this->catalog['parameters'][$p->doc])) {
                    $errors[] = "$n: unknown shared parameter '{$p->doc}'";
                }
                if ($p->doc === null && count($this->byName[strtolower($p->name)] ?? []) > 1) {
                    $errors[] = "$n: parameter '{$p->name}' is ambiguous, set doc:";
                }
            }
        }
        return $errors;
    }
}
