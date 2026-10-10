<?php
// src/index.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Legacy\LegacyController;

include_once __DIR__ . '/bootstrap.php';

$controller = new LegacyController();
$controller->handleRequest();
