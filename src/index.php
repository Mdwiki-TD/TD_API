<?php
// src/index.php

use App\Controllers\APIController;

if (isset($_REQUEST['test'])) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

if (!isset($_GET['get'])) {
    header("Location: /api/openapi.html");
    exit();
}

include_once __DIR__ . '/bootstrap.php';
include_once __DIR__ . '/app/request.php';

$controller = new APIController();
$controller->handleRequest();
