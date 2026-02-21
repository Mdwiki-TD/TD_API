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

    public function testGetDbnameFunctionExists(): void
    {
        $this->assertTrue(\function_exists('API\SQL\get_dbname'));
    }

    public function testFetchQueryNewFunctionExists(): void
    {
        $this->assertTrue(\function_exists('API\SQL\fetch_query_new'));
    }

    // ========== get_dbname tests ==========

    public function testGetDbnameReturnsDefaultForUnknownTable(): void
    {
        $result = \API\SQL\get_dbname('unknown_table_xyz');
        $this->assertSame('DB_NAME', $result);
    }

    public function testGetDbnameReturnsDefaultForEmptyTable(): void
    {
        $result = \API\SQL\get_dbname('');
        $this->assertSame('DB_NAME', $result);
    }

    public function testGetDbnameReturnsNewDbForMissingTable(): void
    {
        $result = \API\SQL\get_dbname('missing');
        $this->assertSame('DB_NAME_NEW', $result);
    }

    public function testGetDbnameReturnsNewDbForMissingByQidsTable(): void
    {
        $result = \API\SQL\get_dbname('missing_by_qids');
        $this->assertSame('DB_NAME_NEW', $result);
    }

    public function testGetDbnameReturnsNewDbForExistsByQidsTable(): void
    {
        $result = \API\SQL\get_dbname('exists_by_qids');
        $this->assertSame('DB_NAME_NEW', $result);
    }

    public function testGetDbnameReturnsNewDbForPublishReportsTable(): void
    {
        $result = \API\SQL\get_dbname('publish_reports');
        $this->assertSame('DB_NAME_NEW', $result);
    }

    public function testGetDbnameReturnsNewDbForLoginAttemptsTable(): void
    {
        $result = \API\SQL\get_dbname('login_attempts');
        $this->assertSame('DB_NAME_NEW', $result);
    }

    public function testGetDbnameReturnsNewDbForLoginsTable(): void
    {
        $result = \API\SQL\get_dbname('logins');
        $this->assertSame('DB_NAME_NEW', $result);
    }

    public function testGetDbnameReturnsNewDbForPublishReportsStatsTable(): void
    {
        $result = \API\SQL\get_dbname('publish_reports_stats');
        $this->assertSame('DB_NAME_NEW', $result);
    }

    public function testGetDbnameReturnsNewDbForAllQidsTitlesTable(): void
    {
        $result = \API\SQL\get_dbname('all_qids_titles');
        $this->assertSame('DB_NAME_NEW', $result);
    }

    public function testGetDbnameIsCaseSensitive(): void
    {
        // These should return default DB_NAME because the matching is case-sensitive
        $result = \API\SQL\get_dbname('MISSING');
        $this->assertSame('DB_NAME', $result);

        $result = \API\SQL\get_dbname('Missing');
        $this->assertSame('DB_NAME', $result);
    }
}
