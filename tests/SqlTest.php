<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

/**
 * Tests for SQL functions in api_cod/sql.php
 */
class SqlTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_GET = [];
        $_REQUEST = [];
    }

    protected function tearDown(): void
    {
        $_GET = [];
        $_REQUEST = [];
        parent::tearDown();
    }

    public function testFetchQueryNewFunctionExists(): void
    {
        $this->assertTrue(\function_exists('API\SQL\fetch_query_new'));
    }

}
