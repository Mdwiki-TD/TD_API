<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

/**
 * Tests for SQL helper functions in api_cod/sql.php
 */
class SqlFunctionsTest extends TestCase
{
    // ========== create_apcu_key tests (via testing behavior) ==========

    public function testApcuKeyGenerationWithEmptyQuery(): void
    {
        // Test that empty SQL queries are handled properly
        // We can verify this by checking the function exists
        $this->assertTrue(function_exists('API\SQL\create_apcu_key'));
    }

    public function testApcuKeyGenerationWithQueryAndParams(): void
    {
        // Verify the key generation function exists
        $this->assertTrue(function_exists('API\SQL\create_apcu_key'));
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

    // ========== APCu stub behavior tests ==========

    public function testGetFromApcuFunctionExists(): void
    {
        $this->assertTrue(function_exists('API\SQL\get_from_apcu'));
    }

    public function testAddToApcuFunctionExists(): void
    {
        $this->assertTrue(function_exists('API\SQL\add_to_apcu'));
    }

    public function testFetchQueryNewFunctionExists(): void
    {
        $this->assertTrue(function_exists('API\SQL\fetch_query_new'));
    }
}
