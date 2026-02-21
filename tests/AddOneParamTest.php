<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

use function API\Helps\add_one_param;

/**
 * Tests for add_one_param function in api_cod/helps.php
 * This function handles special parameter values like not_empty, empty, >0
 */
class AddOneParamTest extends TestCase
{
    public function testAddOneParamWithRegularValue(): void
    {
        $query = 'SELECT * FROM pages';
        $column = 'title';
        $added = 'TestPage';
        $tabe = [];

        $result = add_one_param($query, $column, $added, $tabe);

        // Function outputs extra spaces: "  WHERE  title = ? "
        $this->assertStringContainsString('title = ?', $result[0]);
        $this->assertStringContainsString('WHERE', $result[0]);
        $this->assertSame(['TestPage'], $result[1]);
    }

    public function testAddOneParamWithNotEmptyValue(): void
    {
        $query = 'SELECT * FROM pages';
        $column = 'content';
        $added = 'not_empty';
        $tabe = [];

        $result = add_one_param($query, $column, $added, $tabe);

        $this->assertStringContainsString("(content != '' AND content IS NOT NULL)", $result[0]);
        $this->assertSame([], $result[1]);
    }

    public function testAddOneParamWithNotMtAlias(): void
    {
        $query = 'SELECT * FROM pages';
        $column = 'content';
        $added = 'not_mt';
        $tabe = [];

        $result = add_one_param($query, $column, $added, $tabe);

        $this->assertStringContainsString("(content != '' AND content IS NOT NULL)", $result[0]);
        $this->assertSame([], $result[1]);
    }

    public function testAddOneParamWithEmptyValue(): void
    {
        $query = 'SELECT * FROM pages';
        $column = 'content';
        $added = 'empty';
        $tabe = [];

        $result = add_one_param($query, $column, $added, $tabe);

        $this->assertStringContainsString("(content = '' OR content IS NULL)", $result[0]);
        $this->assertSame([], $result[1]);
    }

    public function testAddOneParamWithMtAlias(): void
    {
        $query = 'SELECT * FROM pages';
        $column = 'content';
        $added = 'mt';
        $tabe = [];

        $result = add_one_param($query, $column, $added, $tabe);

        $this->assertStringContainsString("(content = '' OR content IS NULL)", $result[0]);
        $this->assertSame([], $result[1]);
    }

    public function testAddOneParamWithGreaterThanZero(): void
    {
        $query = 'SELECT * FROM pages';
        $column = 'view_count';
        $added = '>0';
        $tabe = [];

        $result = add_one_param($query, $column, $added, $tabe);

        $this->assertStringContainsString('view_count > 0', $result[0]);
        $this->assertSame([], $result[1]);
    }

    public function testAddOneParamWithHtmlEncodedGreaterThanZero(): void
    {
        $query = 'SELECT * FROM pages';
        $column = 'view_count';
        $added = '&#62;0';  // HTML encoded >0
        $tabe = [];

        $result = add_one_param($query, $column, $added, $tabe);

        $this->assertStringContainsString('view_count > 0', $result[0]);
        $this->assertSame([], $result[1]);
    }

    public function testAddOneParamWithExistingWhereClause(): void
    {
        $query = 'SELECT * FROM pages WHERE lang = ?';
        $column = 'title';
        $added = 'TestPage';
        $tabe = [];

        $result = add_one_param($query, $column, $added, $tabe);

        // Function outputs extra spaces: "  AND  title = ? "
        $this->assertStringContainsString('title = ?', $result[0]);
        $this->assertStringContainsString('AND', $result[0]);
        $this->assertSame(['TestPage'], $result[1]);
    }

    public function testAddOneParamWithValueCanBeNull(): void
    {
        $query = 'SELECT * FROM pages';
        $column = 'status';
        $added = 'active';
        $tabe = ['value_can_be_null' => true];

        $result = add_one_param($query, $column, $added, $tabe);

        $this->assertStringContainsString('(status = ? OR status IS NULL OR status = \'\')', $result[0]);
        $this->assertSame(['active'], $result[1]);
    }

    public function testAddOneParamWithArrayType(): void
    {
        // Array type should call add_array_params
        $_GET['titles'] = ['Page1', 'Page2'];

        $query = 'SELECT * FROM pages';
        $column = 'title';
        $added = '';  // Value doesn't matter for array type
        $tabe = ['type' => 'array', 'name' => 'titles'];

        $result = add_one_param($query, $column, $added, $tabe);

        $this->assertStringContainsString('title IN (?,?)', $result[0]);
        $this->assertSame(['Page1', 'Page2'], $result[1]);

        // Clean up
        unset($_GET['titles']);
    }

    public function testAddOneParamWithNumericValue(): void
    {
        $query = 'SELECT * FROM pages';
        $column = 'id';
        $added = '123';
        $tabe = [];

        $result = add_one_param($query, $column, $added, $tabe);

        // Function outputs extra spaces: "  WHERE  id = ? "
        $this->assertStringContainsString('id = ?', $result[0]);
        $this->assertStringContainsString('WHERE', $result[0]);
        $this->assertSame(['123'], $result[1]);
    }
}
