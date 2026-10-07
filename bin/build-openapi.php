<?php
// bin/build-openapi.php  —  php bin/build-openapi.php [--check]
declare(strict_types=1);
require __DIR__ . '/../src/app/bootstrap.php';

use App\Endpoints\Definition\EndpointDefinitions;
use App\Endpoints\EndpointRegistry;
use App\OpenApi\{OpenApiBuilder, OpenApiCatalog};

$builder = new OpenApiBuilder(EndpointDefinitions::all(), OpenApiCatalog::data());

$errors = $builder->validate(array_keys((new EndpointRegistry())->all()));
if ($errors) {
    fwrite(STDERR, implode("\n", $errors) . "\n");
    exit(1);
}

$json = json_encode($builder->build(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
$file = __DIR__ . '/../src/openapi.json';

if (in_array('--check', $argv, true)) {
    if (!is_file($file) || file_get_contents($file) !== $json) {
        fwrite(STDERR, "openapi.json is stale: run php bin/build-openapi.php\n");
        exit(1);
    }
    exit(0);
}
file_put_contents($file, $json);
echo 'openapi.json written (' . count(json_decode($json, true)['paths']) . " endpoints)\n";
