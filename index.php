<?php

if (!isset($_GET['get'])) {
    header('Location: test.html');
    exit();
}

include_once __DIR__ . '/request.php';
