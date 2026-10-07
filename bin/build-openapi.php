<?php
// bin/build-openapi.php   —   php bin/build-openapi.php [--check]
require __DIR__ . '/../src/app/bootstrap.php';

use App\Endpoints\EndpointRegistry;
use App\OpenApi\OpenApiBuilder;

$spec = (new OpenApiBuilder(new EndpointRegistry(), [
    'title' => 'MDWiki Translation Dashboard API',
    'version' => '2.0.0',
]))->build();

$json = json_encode($spec, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
$file = __DIR__ . '/../src/openapi.json';

if (in_array('--check', $argv, true)) {
    exit(file_get_contents($file) === $json ? 0 : (fwrite(STDERR, "openapi.json is stale: run bin/build-openapi.php\n") ?: 1));
}
file_put_contents($file, $json);
