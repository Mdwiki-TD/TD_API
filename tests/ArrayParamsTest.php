<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

use function API\Helps\add_array_params;

/**
 * Tests for add_array_params function in api_cod/helps.php
 */
class ArrayParamsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_GET = [];
    }

    protected function tearDown(): void
    {
        $_GET = [];
        parent::tearDown();
    }

    public function testAddArrayParamsWithEmptyArray(): void
    {
        $_GET['titles'] = [];
        $query = 'SELECT * FROM pages';
        $params = [];

        $result = add_array_params($query, $params, 'titles', 'title', ' AND ');

        $this->assertSame('SELECT * FROM pages', $result[0]);
        $this->assertSame([], $result[1]);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testAddArrayParamsWithSingleValue(): void
    {
        $_GET['titles'] = ['Page1'];
        $query = 'SELECT * FROM pages';
        $params = [];

        $result = add_array_params($query, $params, 'titles', 'title', ' AND ');

        $this->assertStringContainsString('title IN (?)', $result[0]);
        $this->assertSame(['Page1'], $result[1]);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testAddArrayParamsWithMultipleValues(): void
    {
        $_GET['titles'] = ['Page1', 'Page2', 'Page3'];
        $query = 'SELECT * FROM pages';
        $params = [];

        $result = add_array_params($query, $params, 'titles', 'title', ' AND ');

        $this->assertStringContainsString('title IN (?,?,?)', $result[0]);
        $this->assertSame(['Page1', 'Page2', 'Page3'], $result[1]);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testAddArrayParamsWithWhereClause(): void
    {
        $_GET['titles'] = ['Page1', 'Page2'];
        $query = 'SELECT * FROM pages WHERE lang = ?';
        $params = ['en'];

        $result = add_array_params($query, $params, 'titles', 'title', ' AND ');

        // Function adds extra spaces: " AND  title IN (?,?)"
        $this->assertStringContainsString('WHERE lang = ?', $result[0]);
        $this->assertStringContainsString('title IN (?,?)', $result[0]);
        $this->assertSame(['en', 'Page1', 'Page2'], $result[1]);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testAddArrayParamsWithDifferentParameterName(): void
    {
        $_GET['langs'] = ['en', 'ar', 'fr'];
        $query = 'SELECT * FROM pages';
        $params = [];

        $result = add_array_params($query, $params, 'langs', 'lang_code', ' WHERE ');

        // Function adds extra spaces: " WHERE  lang_code IN (?,?,?)"
        $this->assertStringContainsString('lang_code IN (?,?,?)', $result[0]);
        $this->assertSame(['en', 'ar', 'fr'], $result[1]);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testAddArrayParamsAppendsToExistingParams(): void
    {
        $_GET['titles'] = ['Page1'];
        $query = 'SELECT * FROM pages WHERE id > ?';
        $params = [100];

        $result = add_array_params($query, $params, 'titles', 'title');

        $this->assertSame([100, 'Page1'], $result[1]);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testAddArrayParamsWithoutWhereOrAndUsesWhere(): void
    {
        $_GET['titles'] = ['Page1'];
        $query = 'SELECT * FROM pages';
        $params = [];

        // Empty where_or_and should auto-detect based on existing WHERE clause
        $result = add_array_params($query, $params, 'titles', 'title', '');

        // Function adds extra spaces: " WHERE  title IN (?)"
        $this->assertStringContainsString('title IN (?)', $result[0]);
        $this->assertStringContainsString('WHERE', $result[0]);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testAddArrayParamsWithExistingWhere(): void
    {
        $_GET['titles'] = ['Page1'];
        $query = 'SELECT * FROM pages WHERE active = 1';
        $params = [];

        // Empty where_or_and should auto-detect based on existing WHERE clause
        $result = add_array_params($query, $params, 'titles', 'title', '');

        // Function adds extra spaces: " AND  title IN (?)"
        $this->assertStringContainsString('title IN (?)', $result[0]);
        $this->assertStringContainsString('AND', $result[0]);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testAddArrayParamsWhenNotSetInGet(): void
    {
        // Don't set $_GET['titles']
        $query = 'SELECT * FROM pages';
        $params = [];

        $result = add_array_params($query, $params, 'titles', 'title', ' AND ');

        // Should return unchanged
        $this->assertSame('SELECT * FROM pages', $result[0]);
        $this->assertSame([], $result[1]);
    }
}
