<?php
// src/index.php

use App\APIController;

if (!isset($_GET['get'])) {
    header("Location: /api/openapi.html");
    exit();
}

include_once __DIR__ . '/bootstrap.php';

$env = getenv('APP_ENV') ?: ($_ENV['APP_ENV'] ?? 'development');

if (isset($_REQUEST['test']) && $env === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

$controller = new APIController();
$controller->handleRequest();
