<?php
// src/app/OpenApi/OpenApiBuilder.php
declare(strict_types=1);
namespace App\OpenApi;

/**
 * يولّد openapi.json (3.0.0، متوافق مع Swagger UI 4.5) من:
 *  - OpenApiCatalog: info/servers/tags/components المشتركة
 *  - $docs: توثيق كل endpoint (يأتي لاحقاً من EndpointDefinition)
 */
final class OpenApiBuilder
{
    private const DEFAULT_DESCRIPTION = 'Corresponds to calling `api.php?get=%s` with query parameters.';

    /** @param array<string, array{summary:string, tag:string, description?:string, params:list<string|array>}> $docs */
    public function __construct(private array $docs, private array $catalog) {}

    public function build(): array
    {
        $paths = [];
        foreach ($this->docs as $name => $doc) {
            $paths["/api.php?get=$name"] = ['get' => [
                'summary'     => $doc['summary'],
                'description' => $doc['description'] ?? sprintf(self::DEFAULT_DESCRIPTION, $name),
                'tags'        => [$doc['tag']],
                'parameters'  => array_map(
                    static fn($p) => is_string($p) ? ['$ref' => "#/components/parameters/$p"] : $p,
                    $doc['params']
                ),
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

    /** أخطاء الاتساق (تُستخدم في الاختبار و bin/build-openapi.php) */
    public function validate(array $registryNames): array
    {
        $errors = [];
        foreach (array_diff($registryNames, array_keys($this->docs)) as $n) {
            $errors[] = "endpoint '$n' has no documentation";
        }
        foreach (array_diff(array_keys($this->docs), $registryNames) as $n) {
            $errors[] = "documented endpoint '$n' is not registered";
        }
        $tags = array_column($this->catalog['tags'], 'name');
        foreach ($this->docs as $n => $d) {
            if (!in_array($d['tag'], $tags, true)) {
                $errors[] = "$n: unknown tag '{$d['tag']}'";
            }
            foreach ($d['params'] as $p) {
                if (is_string($p) && !isset($this->catalog['parameters'][$p])) {
                    $errors[] = "$n: unknown shared parameter '$p'";
                }
            }
        }
        return $errors;
    }
}
