<?php
// bin/build-openapi.php  —  php bin/build-test.php
declare(strict_types=1);
require __DIR__ . '/../src/app/bootstrap.php';

use App\Endpoints\Definition\EndpointDefinitions;

$map = EndpointDefinitions::alltoArray();

$json = json_encode($map, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";

$file = __DIR__ . '/test.json';

file_put_contents($file, $json);
echo 'openapi.json written (' . count(json_decode($json, true)['paths']) . " endpoints)\n";
