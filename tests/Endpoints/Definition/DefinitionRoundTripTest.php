<?php
// tests/DefinitionRoundTripTest.php
declare (strict_types = 1);

use App\Endpoints\Definition\EndpointDefinitions;
use PHPUnit\Framework\TestCase;

final class DefinitionRoundTripTest extends TestCase
{
    private static function legacy(): array
    {
        return json_decode(file_get_contents(__DIR__ . '/../../../src/app/endpoint_params.json'), true, 512, JSON_THROW_ON_ERROR);
    }

    public function testEveryLegacyEndpointMatchesItsDefinition(): void
    {
        $legacy = self::legacy();
        $defs   = EndpointDefinitions::all();

        foreach ($legacy as $name => $entry) {
            $source = isset($entry['redirect']) ? $legacy[$entry['redirect']] : $entry;
            $this->assertArrayHasKey($name, $defs, "missing definition: $name");

            $actual = $defs[$name]->toArray();
            $this->assertEquals($source['params'] ?? [], $actual['params'], "$name: params differ");
            $this->assertEquals($source['columns'] ?? [], $actual['columns'], "$name: columns differ");
            $this->assertEquals($source['order_values'] ?? [], $actual['order_values'] ?? [], "$name: order_values differ");
        }
    }
}
