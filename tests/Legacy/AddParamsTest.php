<?php

declare (strict_types = 1);

namespace Tests;

use App\Legacy\AddParams;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

#[CoversClass(AddParams::class)]
class AddParamsTest extends TestCase
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

    // ---------------------
    // add_li_params
    // ---------------------

    public function testAddLiParamsWithEmptyTypes(): void
    {
        $query  = 'SELECT * FROM pages';
        $result = AddParams::add_li_params($query, [], [], []);
        $this->assertSame(['SELECT * FROM pages', []], $result);
    }

    public function testAddLiParamsWithSimpleWhere(): void
    {
        $_GET['title'] = 'TestPage';
        $query         = 'SELECT * FROM pages';
        // Types should be an array of strings, not an associative array
        $types  = ['title'];
        $result = AddParams::add_li_params($query, $types, [], []);
        $this->assertStringContainsString('title = ?', $result[0]);
        $this->assertSame(['TestPage'], $result[1]);
    }

    public function testAddLiParamsWithMultipleConditions(): void
    {
        $_GET['title'] = 'TestPage';
        $_GET['lang']  = 'en';
        $query         = 'SELECT * FROM pages';
        // Types should be an array of strings, not an associative array
        $types  = ['title', 'lang'];
        $result = AddParams::add_li_params($query, $types, [], []);
        $this->assertStringContainsString('title = ?', $result[0]);
        $this->assertStringContainsString('lang = ?', $result[0]);
        $this->assertSame(['TestPage', 'en'], $result[1]);
    }

    public function testAddLiParamsIgnoresLimitColumn(): void
    {
        $_GET['limit'] = '10';
        $query         = 'SELECT * FROM pages';
        // Types should be an array of strings
        $types  = ['limit'];
        $result = AddParams::add_li_params($query, $types, [], []);
        // Should not add WHERE clause for limit
        $this->assertSame('SELECT * FROM pages', $result[0]);
    }

    public function testAddLiParamsIgnoresSelectColumn(): void
    {
        $_GET['select'] = 'title';
        $query          = 'SELECT * FROM pages';
        // Types should be an array of strings
        $types  = ['select'];
        $result = AddParams::add_li_params($query, $types, [], []);
        $this->assertSame('SELECT * FROM pages', $result[0]);
    }

    public function testAddLiParamsWithNotEmptyValue(): void
    {
        $_GET['filter'] = 'not_empty';
        $query          = 'SELECT * FROM pages';
        // Types should be an array of strings
        $types  = ['filter'];
        $result = AddParams::add_li_params($query, $types, [], []);
        $this->assertStringContainsString("filter != '' AND filter IS NOT NULL", $result[0]);
        $this->assertSame([], $result[1]);
    }

    public function testAddLiParamsWithEmptyValue(): void
    {
        $_GET['filter'] = 'empty';
        $query          = 'SELECT * FROM pages';
        // Types should be an array of strings
        $types  = ['filter'];
        $result = AddParams::add_li_params($query, $types, [], []);
        $this->assertStringContainsString("filter = '' OR filter IS NULL", $result[0]);
        $this->assertSame([], $result[1]);
    }

    public function testAddLiParamsWithGreaterThanZero(): void
    {
        $_GET['count'] = '>0';
        $query         = 'SELECT * FROM pages';
        // Types should be an array of strings
        $types  = ['count'];
        $result = AddParams::add_li_params($query, $types, [], []);
        $this->assertStringContainsString('count > 0', $result[0]);
        $this->assertSame([], $result[1]);
    }

    public function testAddLiParamsWithDistinctFlag(): void
    {
        $_GET['distinct'] = '1';
        $query            = 'SELECT * FROM pages';
        // Types should be an array of strings
        $types  = ['distinct'];
        $result = AddParams::add_li_params($query, $types, [], []);
        $this->assertSame('SELECT DISTINCT * FROM pages', $result[0]);
        $this->assertSame([], $result[1]);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testAddLiParamsWithNoEmptyValueSkipsEmpty(): void
    {
        $_GET['filter'] = '';
        $query          = 'SELECT * FROM pages';
        // Types should be an array of strings, pass extra config via endpoint_params
        $types           = [];
        $endpoint_params = [['name' => 'filter', 'column' => 'filter_col', 'no_empty_value' => true]];
        $result          = AddParams::add_li_params($query, $types, $endpoint_params, []);
        $this->assertSame('SELECT * FROM pages', $result[0]);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testAddLiParamsWithValueCanBeNull(): void
    {
        $_GET['status'] = 'active';
        $query          = 'SELECT * FROM pages';
        // Types should be an array of strings, pass extra config via endpoint_params
        $types           = [];
        $endpoint_params = [['name' => 'status', 'column' => 'status', 'value_can_be_null' => true]];
        $result          = AddParams::add_li_params($query, $types, $endpoint_params, []);
        $this->assertStringContainsString('(status = ? OR status IS NULL OR status = \'\')', $result[0]);
    }

    // ---------------------
    // add_distinct
    // ---------------------

    public function testAddDistinct(): void
    {
        $query  = 'SELECT * FROM pages';
        $result = AddParams::add_distinct($query);
        $this->assertSame('SELECT DISTINCT * FROM pages', $result);
    }

    public function testAddDistinctWithLowercase(): void
    {
        $query  = 'select name from pages';
        $result = AddParams::add_distinct($query);
        $this->assertSame('SELECT DISTINCT name from pages', $result);
    }

    // ---------------------
    // add_array_params
    // ---------------------

    public function testAddArrayParamsWithEmptyArray(): void
    {
        $_GET['titles'] = [];
        $query          = 'SELECT * FROM pages';
        $params         = [];

        $result = AddParams::add_array_params($query, $params, 'titles', 'title', ' AND ');

        $this->assertSame('SELECT * FROM pages', $result[0]);
        $this->assertSame([], $result[1]);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testAddArrayParamsWithSingleValue(): void
    {
        $_GET['titles'] = ['Page1'];
        $query          = 'SELECT * FROM pages';
        $params         = [];

        $result = AddParams::add_array_params($query, $params, 'titles', 'title', ' AND ');

        $this->assertStringContainsString('title IN (?)', $result[0]);
        $this->assertSame(['Page1'], $result[1]);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testAddArrayParamsWithMultipleValues(): void
    {
        $_GET['titles'] = ['Page1', 'Page2', 'Page3'];
        $query          = 'SELECT * FROM pages';
        $params         = [];

        $result = AddParams::add_array_params($query, $params, 'titles', 'title', ' AND ');

        $this->assertStringContainsString('title IN (?,?,?)', $result[0]);
        $this->assertSame(['Page1', 'Page2', 'Page3'], $result[1]);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testAddArrayParamsWithWhereClause(): void
    {
        $_GET['titles'] = ['Page1', 'Page2'];
        $query          = 'SELECT * FROM pages WHERE lang = ?';
        $params         = ['en'];

        $result = AddParams::add_array_params($query, $params, 'titles', 'title', ' AND ');

        // Function adds extra spaces: " AND  title IN (?,?)"
        $this->assertStringContainsString('WHERE lang = ?', $result[0]);
        $this->assertStringContainsString('title IN (?,?)', $result[0]);
        $this->assertSame(['en', 'Page1', 'Page2'], $result[1]);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testAddArrayParamsWithDifferentParameterName(): void
    {
        $_GET['langs'] = ['en', 'ar', 'fr'];
        $query         = 'SELECT * FROM pages';
        $params        = [];

        $result = AddParams::add_array_params($query, $params, 'langs', 'lang_code', ' WHERE ');

        // Function adds extra spaces: " WHERE  lang_code IN (?,?,?)"
        $this->assertStringContainsString('lang_code IN (?,?,?)', $result[0]);
        $this->assertSame(['en', 'ar', 'fr'], $result[1]);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testAddArrayParamsAppendsToExistingParams(): void
    {
        $_GET['titles'] = ['Page1'];
        $query          = 'SELECT * FROM pages WHERE id > ?';
        $params         = [100];

        $result = AddParams::add_array_params($query, $params, 'titles', 'title');

        $this->assertSame([100, 'Page1'], $result[1]);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testAddArrayParamsWithoutWhereOrAndUsesWhere(): void
    {
        $_GET['titles'] = ['Page1'];
        $query          = 'SELECT * FROM pages';
        $params         = [];

        // Empty where_or_and should auto-detect based on existing WHERE clause
        $result = AddParams::add_array_params($query, $params, 'titles', 'title', '');

        // Function adds extra spaces: " WHERE  title IN (?)"
        $this->assertStringContainsString('title IN (?)', $result[0]);
        $this->assertStringContainsString('WHERE', $result[0]);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testAddArrayParamsWithExistingWhere(): void
    {
        $_GET['titles'] = ['Page1'];
        $query          = 'SELECT * FROM pages WHERE active = 1';
        $params         = [];

        // Empty where_or_and should auto-detect based on existing WHERE clause
        $result = AddParams::add_array_params($query, $params, 'titles', 'title', '');

        // Function adds extra spaces: " AND  title IN (?)"
        $this->assertStringContainsString('title IN (?)', $result[0]);
        $this->assertStringContainsString('AND', $result[0]);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testAddArrayParamsWhenNotSetInGet(): void
    {
        // Don't set $_GET['titles']
        $query  = 'SELECT * FROM pages';
        $params = [];

        $result = AddParams::add_array_params($query, $params, 'titles', 'title', ' AND ');

        // Should return unchanged
        $this->assertSame('SELECT * FROM pages', $result[0]);
        $this->assertSame([], $result[1]);
    }
    // ---------------------
    // add_one_param
    // ---------------------
    public function testAddOneParamWithRegularValue(): void
    {
        $query  = 'SELECT * FROM pages';
        $column = 'title';
        $added  = 'TestPage';
        $tabe   = [];

        $result = AddParams::add_one_param($query, $column, $added, $tabe);

        // Function outputs extra spaces: "  WHERE  title = ? "
        $this->assertStringContainsString('title = ?', $result[0]);
        $this->assertStringContainsString('WHERE', $result[0]);
        $this->assertSame(['TestPage'], $result[1]);
    }

    public function testAddOneParamWithNotEmptyValue(): void
    {
        $query  = 'SELECT * FROM pages';
        $column = 'content';
        $added  = 'not_empty';
        $tabe   = [];

        $result = AddParams::add_one_param($query, $column, $added, $tabe);

        $this->assertStringContainsString("(content != '' AND content IS NOT NULL)", $result[0]);
        $this->assertSame([], $result[1]);
    }

    public function testAddOneParamWithNotMtAlias(): void
    {
        $query  = 'SELECT * FROM pages';
        $column = 'content';
        $added  = 'not_mt';
        $tabe   = [];

        $result = AddParams::add_one_param($query, $column, $added, $tabe);

        $this->assertStringContainsString("(content != '' AND content IS NOT NULL)", $result[0]);
        $this->assertSame([], $result[1]);
    }

    public function testAddOneParamWithEmptyValue(): void
    {
        $query  = 'SELECT * FROM pages';
        $column = 'content';
        $added  = 'empty';
        $tabe   = [];

        $result = AddParams::add_one_param($query, $column, $added, $tabe);

        $this->assertStringContainsString("(content = '' OR content IS NULL)", $result[0]);
        $this->assertSame([], $result[1]);
    }

    public function testAddOneParamWithMtAlias(): void
    {
        $query  = 'SELECT * FROM pages';
        $column = 'content';
        $added  = 'mt';
        $tabe   = [];

        $result = AddParams::add_one_param($query, $column, $added, $tabe);

        $this->assertStringContainsString("(content = '' OR content IS NULL)", $result[0]);
        $this->assertSame([], $result[1]);
    }

    public function testAddOneParamWithGreaterThanZero(): void
    {
        $query  = 'SELECT * FROM pages';
        $column = 'view_count';
        $added  = '>0';
        $tabe   = [];

        $result = AddParams::add_one_param($query, $column, $added, $tabe);

        $this->assertStringContainsString('view_count > 0', $result[0]);
        $this->assertSame([], $result[1]);
    }

    public function testAddOneParamWithHtmlEncodedGreaterThanZero(): void
    {
        $query  = 'SELECT * FROM pages';
        $column = 'view_count';
        $added  = '&#62;0'; // HTML encoded >0
        $tabe   = [];

        $result = AddParams::add_one_param($query, $column, $added, $tabe);

        $this->assertStringContainsString('view_count > 0', $result[0]);
        $this->assertSame([], $result[1]);
    }

    public function testAddOneParamWithExistingWhereClause(): void
    {
        $query  = 'SELECT * FROM pages WHERE lang = ?';
        $column = 'title';
        $added  = 'TestPage';
        $tabe   = [];

        $result = AddParams::add_one_param($query, $column, $added, $tabe);

        // Function outputs extra spaces: "  AND  title = ? "
        $this->assertStringContainsString('title = ?', $result[0]);
        $this->assertStringContainsString('AND', $result[0]);
        $this->assertSame(['TestPage'], $result[1]);
    }

    public function testAddOneParamWithValueCanBeNull(): void
    {
        $query  = 'SELECT * FROM pages';
        $column = 'status';
        $added  = 'active';
        $tabe   = ['value_can_be_null' => true];

        $result = AddParams::add_one_param($query, $column, $added, $tabe);

        $this->assertStringContainsString('(status = ? OR status IS NULL OR status = \'\')', $result[0]);
        $this->assertSame(['active'], $result[1]);
    }

    public function testAddOneParamWithArrayType(): void
    {
        // Array type should call AddParams::add_array_params
        $_GET['titles'] = ['Page1', 'Page2'];

        $query  = 'SELECT * FROM pages';
        $column = 'title';
        $added  = ''; // Value doesn't matter for array type
        $tabe   = ['type' => 'array', 'name' => 'titles'];

        $result = AddParams::add_one_param($query, $column, $added, $tabe);

        $this->assertStringContainsString('title IN (?,?)', $result[0]);
        $this->assertSame(['Page1', 'Page2'], $result[1]);

        // Clean up
        unset($_GET['titles']);
    }

    public function testAddOneParamWithNumericValue(): void
    {
        $query  = 'SELECT * FROM pages';
        $column = 'id';
        $added  = '123';
        $tabe   = [];

        $result = AddParams::add_one_param($query, $column, $added, $tabe);

        // Function outputs extra spaces: "  WHERE  id = ? "
        $this->assertStringContainsString('id = ?', $result[0]);
        $this->assertStringContainsString('WHERE', $result[0]);
        $this->assertSame(['123'], $result[1]);
    }

    // ---------------------
    // change_types
    // ---------------------

    public function testChangeTypesWithEmptyArrays(): void
    {
        $types           = [];
        $endpoint_params = [];
        $ignore_params   = [];

        $result = AddParams::change_types($types, $endpoint_params, $ignore_params);

        $this->assertSame([], $result);
    }

    public function testChangeTypesWithSimpleTypes(): void
    {
        // When $types is an array of strings, it converts them to column definitions
        $types           = ['title', 'lang', 'user'];
        $endpoint_params = [];
        $ignore_params   = [];

        $result = AddParams::change_types($types, $endpoint_params, $ignore_params);

        $this->assertSame([
            'title' => ['column' => 'title'],
            'lang'  => ['column' => 'lang'],
            'user'  => ['column' => 'user'],
        ], $result);
    }

    public function testChangeTypesFallsBackToEndpointParams(): void
    {
        // When $types is empty, it falls back to using $endpoint_params
        $types           = [];
        $endpoint_params = [
            ['name' => 'title', 'column' => 'w_title'],
            ['name' => 'lang', 'column' => 'lang_code'],
        ];
        $ignore_params = [];

        $result = AddParams::change_types($types, $endpoint_params, $ignore_params);

        $this->assertSame([
            'title' => ['name' => 'title', 'column' => 'w_title'],
            'lang'  => ['name' => 'lang', 'column' => 'lang_code'],
        ], $result);
    }

    public function testChangeTypesSkipsNoSelectParams(): void
    {
        // Params with 'no_select' => true should be skipped when falling back to endpoint_params
        $types           = [];
        $endpoint_params = [
            ['name' => 'title', 'column' => 'w_title'],
            ['name' => 'hidden_field', 'column' => 'hidden_col', 'no_select' => true],
            ['name' => 'lang', 'column' => 'lang_code'],
        ];
        $ignore_params = [];

        $result = AddParams::change_types($types, $endpoint_params, $ignore_params);

        $this->assertArrayHasKey('title', $result);
        $this->assertArrayNotHasKey('hidden_field', $result);
        $this->assertArrayHasKey('lang', $result);
    }

    public function testChangeTypesIgnoresSpecifiedParams(): void
    {
        // When $types is an array of strings, $ignore_params removes items from the result
        $types           = ['title', 'lang', 'user'];
        $endpoint_params = [];
        $ignore_params   = ['lang'];

        $result = AddParams::change_types($types, $endpoint_params, $ignore_params);

        $this->assertArrayHasKey('title', $result);
        $this->assertArrayNotHasKey('lang', $result);
        $this->assertArrayHasKey('user', $result);
    }

    public function testChangeTypesPrefersTypesOverEndpointParams(): void
    {
        // When $types is provided (not empty), it should be used instead of $endpoint_params
        $types           = ['custom_title'];
        $endpoint_params = [
            ['name' => 'title', 'column' => 'w_title'],
        ];
        $ignore_params = [];

        $result = AddParams::change_types($types, $endpoint_params, $ignore_params);

        // Should use $types, not $endpoint_params
        $this->assertSame(['custom_title' => ['column' => 'custom_title']], $result);
    }

    public function testChangeTypesFallsBackToEndpointParamsOnlyWhenTypesEmpty(): void
    {
        // Verify that empty $types triggers fallback to $endpoint_params
        $types           = [];
        $endpoint_params = [
            ['name' => 'param1', 'column' => 'col1'],
            ['name' => 'param2', 'column' => 'col2'],
        ];
        $ignore_params = [];

        $result = AddParams::change_types($types, $endpoint_params, $ignore_params);

        $this->assertCount(2, $result);
        $this->assertArrayHasKey('param1', $result);
        $this->assertArrayHasKey('param2', $result);
    }

    public function testChangeTypesIgnoresFromEndpointParams(): void
    {
        // $ignore_params should work when falling back to $endpoint_params
        $types           = [];
        $endpoint_params = [
            ['name' => 'title', 'column' => 'w_title'],
            ['name' => 'lang', 'column' => 'lang_code'],
        ];
        $ignore_params = ['lang'];

        $result = AddParams::change_types($types, $endpoint_params, $ignore_params);

        $this->assertArrayHasKey('title', $result);
        $this->assertArrayNotHasKey('lang', $result);
    }

    public function testChangeTypesHandlesEmptyIgnoreParams(): void
    {
        $types           = ['title', 'lang'];
        $endpoint_params = [];
        $ignore_params   = [];

        $result = AddParams::change_types($types, $endpoint_params, $ignore_params);

        $this->assertCount(2, $result);
        $this->assertArrayHasKey('title', $result);
        $this->assertArrayHasKey('lang', $result);
    }
    // ---------------------
    // add_one_param
    // ---------------------
}
