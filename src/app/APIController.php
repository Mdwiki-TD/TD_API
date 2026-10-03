<?php
// src/app/APIController.php

namespace App;

use App\MdwikiSql\Database;

class APIController
{
    private Database $db;

    public function __construct(?Database $db = null)
    {
        $this->db = $db ?? new Database();
    }
    /**
     * Main entry point: load data, build rows, render the template.
     */
    public function handleRequest(): void
    {
        if ($this->db->isDbNull()) {
            error_log("Database is null");
        }
        // TODO: implement
    }
}
