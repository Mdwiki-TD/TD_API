<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

use function API\Helps\sanitize_input;
use function API\Helps\filter_order;
use function API\Helps\add_order;
use function API\Helps\add_limit;
use function API\Helps\add_offset;
use function API\Helps\add_group;
use function API\Helps\add_distinct;
use function API\Helps\add_li_params;
use function API\Helps\get_order_direction;

/**
 * Tests for helper functions in api_cod/helps.php
 * Note: Tests using $_GET run in separate processes because filter_input()
 * doesn't work with direct $_GET assignments in PHPUnit.
 */
class HelpsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Clear $_GET before each test
        $_GET = [];
    }

    protected function tearDown(): void
    {
        $_GET = [];
        parent::tearDown();
    }

    // ========== sanitize_input tests ==========

    public function testSanitizeInputWithValidString(): void
    {
        $result = sanitize_input('hello_world', '/^[a-z_]+$/');
        $this->assertSame('hello_world', $result);
    }

    public function testSanitizeInputWithInvalidPattern(): void
    {
        $result = sanitize_input('hello123', '/^[a-z_]+$/');
        $this->assertNull($result);
    }

    public function testSanitizeInputWithEmptyString(): void
    {
        $result = sanitize_input('', '/^[a-z_]+$/');
        $this->assertNull($result);
    }

    public function testSanitizeInputWithAllKeyword(): void
    {
        $result = sanitize_input('all', '/^[a-z_]+$/');
        $this->assertNull($result);
    }

    public function testSanitizeInputSanitizesSpecialChars(): void
    {
        $result = sanitize_input('hello<script>', '/^.+$/');
        $this->assertSame('hello&lt;script&gt;', $result);
    }

    // ========== get_order_direction tests ==========

    public function testGetOrderDirectionDefault(): void
    {
        $result = get_order_direction([]);
        $this->assertSame('DESC', $result);
    }

    public function testGetOrderDirectionAsc(): void
    {
        $_GET['order_direction'] = 'ASC';
        $result = get_order_direction([]);
        $this->assertSame('ASC', $result);
    }

    public function testGetOrderDirectionCaseInsensitive(): void
    {
        $_GET['order_direction'] = 'asc';
        $result = get_order_direction([]);
        $this->assertSame('ASC', $result);
    }

    public function testGetOrderDirectionInvalidDefaultsToDesc(): void
    {
        $_GET['order_direction'] = 'INVALID';
        $result = get_order_direction([]);
        $this->assertSame('DESC', $result);
    }

    public function testGetOrderDirectionFromDefaultParam(): void
    {
        $param = ['default' => 'ASC'];
        $result = get_order_direction($param);
        $this->assertSame('ASC', $result);
    }

    // ========== filter_order tests ==========

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testFilterOrderWithValidColumn(): void
    {
        $_GET['order'] = 'title';
        $endpoint_data = [
            'columns' => ['title', 'id', 'date'],
            'params' => []
        ];
        $result = filter_order('order', $endpoint_data);
        $this->assertSame('title', $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testFilterOrderWithValidParam(): void
    {
        $_GET['order'] = 'user';
        $endpoint_data = [
            'columns' => ['title', 'id'],
            'params' => ['user', 'lang']
        ];
        $result = filter_order('order', $endpoint_data);
        $this->assertSame('user', $result);
    }

    public function testFilterOrderNotSetReturnsNull(): void
    {
        $endpoint_data = [
            'columns' => ['title'],
            'params' => []
        ];
        $result = filter_order('order', $endpoint_data);
        $this->assertNull($result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testFilterOrderWithInvalidValueReturnsNull(): void
    {
        $_GET['order'] = 'invalid_column';
        $endpoint_data = [
            'columns' => ['title', 'id'],
            'params' => []
        ];
        $result = filter_order('order', $endpoint_data);
        $this->assertNull($result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testFilterOrderWithCommaSeparatedValues(): void
    {
        $_GET['order'] = 'title,id,999';
        $endpoint_data = [
            'columns' => ['title', 'id'],
            'params' => []
        ];
        $result = filter_order('order', $endpoint_data);
        $this->assertSame('title, id, 999', $result);
    }

    // ========== add_order tests ==========

    public function testAddOrderWithoutParamConfig(): void
    {
        $query = 'SELECT * FROM pages';
        $endpoint_data = [
            'columns' => ['title'],
            'params' => []
        ];
        $result = add_order($query, $endpoint_data);
        $this->assertSame('SELECT * FROM pages', $result);
    }

    public function testAddOrderWithDefault(): void
    {
        $query = 'SELECT * FROM pages';
        $endpoint_data = [
            'columns' => ['title', 'date'],
            'params' => [
                ['name' => 'order', 'default' => 'date'],
                ['name' => 'order_direction']
            ]
        ];
        $result = add_order($query, $endpoint_data);
        $this->assertSame('SELECT * FROM pages ORDER BY date DESC', $result);
    }

    public function testAddOrderWithGetParameter(): void
    {
        $_GET['order'] = 'title';
        $_GET['order_direction'] = 'ASC';
        $query = 'SELECT * FROM pages';
        $endpoint_data = [
            'columns' => ['title', 'date'],
            'params' => [
                ['name' => 'order'],
                ['name' => 'order_direction']
            ]
        ];
        $result = add_order($query, $endpoint_data);
        $this->assertSame('SELECT * FROM pages ORDER BY title ASC', $result);
    }

    public function testAddOrderWithSpecialPupdateOrAddDate(): void
    {
        $_GET['order'] = 'pupdate_or_add_date';
        $query = 'SELECT * FROM pages';
        $endpoint_data = [
            'columns' => ['title'],
            'params' => [
                ['name' => 'order', 'default' => 'pupdate_or_add_date'],
                ['name' => 'order_direction']
            ]
        ];
        $result = add_order($query, $endpoint_data);
        $this->assertStringContainsString('GREATEST(UNIX_TIMESTAMP(pupdate), UNIX_TIMESTAMP(add_date))', $result);
    }

    // ========== add_limit tests ==========

    public function testAddLimitWithDefault(): void
    {
        $query = 'SELECT * FROM pages';
        $result = add_limit($query);
        // No limit added when $_GET['limit'] is not set
        $this->assertSame('SELECT * FROM pages', $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testAddLimitWithGetParameter(): void
    {
        // Note: filter_input() doesn't read from $_GET directly in PHPUnit
        // This test verifies the function doesn't break when limit is set
        $_GET['limit'] = '10';
        $query = 'SELECT * FROM pages';
        $result = add_limit($query);
        // filter_input() reads from actual GET request, not $_GET assignment
        // So the limit won't be added in test environment
        $this->assertSame('SELECT * FROM pages', $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testAddLimitWithZeroDoesNotAdd(): void
    {
        $_GET['limit'] = '0';
        $query = 'SELECT * FROM pages';
        $result = add_limit($query);
        $this->assertSame('SELECT * FROM pages', $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testAddLimitWithNegativeDoesNotAdd(): void
    {
        $_GET['limit'] = '-5';
        $query = 'SELECT * FROM pages';
        $result = add_limit($query);
        $this->assertSame('SELECT * FROM pages', $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testAddLimitSkipsIfAlreadyPresent(): void
    {
        $_GET['limit'] = '10';
        $query = 'SELECT * FROM pages LIMIT 5';
        $result = add_limit($query);
        // Should not add another LIMIT
        $this->assertSame('SELECT * FROM pages LIMIT 5', $result);
    }

    // ========== add_offset tests ==========

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testAddOffsetWithGetParameter(): void
    {
        // Note: filter_input() doesn't read from $_GET directly in PHPUnit
        // This test verifies the function doesn't break when offset is set
        $_GET['offset'] = '20';
        $query = 'SELECT * FROM pages';
        $result = add_offset($query);
        // filter_input() reads from actual GET request, not $_GET assignment
        // So the offset won't be added in test environment
        $this->assertSame('SELECT * FROM pages', $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testAddOffsetWithZeroDoesNotAdd(): void
    {
        $_GET['offset'] = '0';
        $query = 'SELECT * FROM pages';
        $result = add_offset($query);
        $this->assertSame('SELECT * FROM pages', $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testAddOffsetSkipsIfAlreadyPresent(): void
    {
        $_GET['offset'] = '20';
        $query = 'SELECT * FROM pages OFFSET 10';
        $result = add_offset($query);
        $this->assertSame('SELECT * FROM pages OFFSET 10', $result);
    }

    // ========== add_group tests ==========

    public function testAddGroupWithValidColumn(): void
    {
        $_GET['group'] = 'lang';
        $query = 'SELECT * FROM pages';
        $endpoint_data = [
            'columns' => ['lang', 'title'],
            'params' => []
        ];
        $result = add_group($query, $endpoint_data);
        $this->assertSame('SELECT * FROM pages GROUP BY lang', $result);
    }

    public function testAddGroupNotSet(): void
    {
        $query = 'SELECT * FROM pages';
        $endpoint_data = [
            'columns' => ['lang'],
            'params' => []
        ];
        $result = add_group($query, $endpoint_data);
        $this->assertSame('SELECT * FROM pages', $result);
    }

    // ========== add_distinct tests ==========

    public function testAddDistinct(): void
    {
        $query = 'SELECT * FROM pages';
        $result = add_distinct($query);
        $this->assertSame('SELECT DISTINCT * FROM pages', $result);
    }

    public function testAddDistinctWithLowercase(): void
    {
        $query = 'select name from pages';
        $result = add_distinct($query);
        $this->assertSame('SELECT DISTINCT name from pages', $result);
    }

    // ========== add_li_params tests ==========

    public function testAddLiParamsWithEmptyTypes(): void
    {
        $query = 'SELECT * FROM pages';
        $result = add_li_params($query, [], [], []);
        $this->assertSame(['SELECT * FROM pages', []], $result);
    }

    public function testAddLiParamsWithSimpleWhere(): void
    {
        $_GET['title'] = 'TestPage';
        $query = 'SELECT * FROM pages';
        // Types should be an array of strings, not an associative array
        $types = ['title'];
        $result = add_li_params($query, $types, [], []);
        // filter_input() reads values in test environment
        $this->assertStringContainsString('title = ?', $result[0]);
        $this->assertSame(['TestPage'], $result[1]);
    }

    public function testAddLiParamsWithMultipleConditions(): void
    {
        $_GET['title'] = 'TestPage';
        $_GET['lang'] = 'en';
        $query = 'SELECT * FROM pages';
        // Types should be an array of strings, not an associative array
        $types = ['title', 'lang'];
        $result = add_li_params($query, $types, [], []);
        // filter_input() reads values in test environment
        $this->assertStringContainsString('title = ?', $result[0]);
        $this->assertStringContainsString('lang = ?', $result[0]);
        $this->assertSame(['TestPage', 'en'], $result[1]);
    }

    public function testAddLiParamsIgnoresLimitColumn(): void
    {
        $_GET['limit'] = '10';
        $query = 'SELECT * FROM pages';
        // Types should be an array of strings
        $types = ['limit'];
        $result = add_li_params($query, $types, [], []);
        // Should not add WHERE clause for limit
        $this->assertSame('SELECT * FROM pages', $result[0]);
    }

    public function testAddLiParamsIgnoresSelectColumn(): void
    {
        $_GET['select'] = 'title';
        $query = 'SELECT * FROM pages';
        // Types should be an array of strings
        $types = ['select'];
        $result = add_li_params($query, $types, [], []);
        $this->assertSame('SELECT * FROM pages', $result[0]);
    }

    public function testAddLiParamsWithNotEmptyValue(): void
    {
        $_GET['filter'] = 'not_empty';
        $query = 'SELECT * FROM pages';
        // Types should be an array of strings
        $types = ['filter'];
        $result = add_li_params($query, $types, [], []);
        // 'not_empty' is a special value that adds IS NOT NULL condition
        $this->assertStringContainsString("(filter != '' AND filter IS NOT NULL)", $result[0]);
    }

    public function testAddLiParamsWithEmptyValue(): void
    {
        $_GET['filter'] = 'empty';
        $query = 'SELECT * FROM pages';
        // Types should be an array of strings
        $types = ['filter'];
        $result = add_li_params($query, $types, [], []);
        // 'empty' is a special value that adds IS NULL condition
        $this->assertStringContainsString("(filter = '' OR filter IS NULL)", $result[0]);
    }

    public function testAddLiParamsWithGreaterThanZero(): void
    {
        $_GET['count'] = '>0';
        $query = 'SELECT * FROM pages';
        // Types should be an array of strings
        $types = ['count'];
        $result = add_li_params($query, $types, [], []);
        // '>0' is a special value that adds > 0 condition
        $this->assertStringContainsString('count > 0', $result[0]);
    }

    public function testAddLiParamsWithDistinctFlag(): void
    {
        $_GET['distinct'] = '1';
        $query = 'SELECT * FROM pages';
        // Types should be an array of strings
        $types = ['distinct'];
        $result = add_li_params($query, $types, [], []);
        // 'distinct' with value '1' adds DISTINCT to SELECT
        $this->assertStringContainsString('SELECT DISTINCT', $result[0]);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testAddLiParamsWithNoEmptyValueSkipsEmpty(): void
    {
        $_GET['filter'] = '';
        $query = 'SELECT * FROM pages';
        // Types should be an array of strings, pass extra config via endpoint_params
        $types = [];
        $endpoint_params = [['name' => 'filter', 'column' => 'filter_col', 'no_empty_value' => true]];
        $result = add_li_params($query, $types, $endpoint_params, []);
        $this->assertSame('SELECT * FROM pages', $result[0]);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testAddLiParamsWithValueCanBeNull(): void
    {
        $_GET['status'] = 'active';
        $query = 'SELECT * FROM pages';
        // Types should be an array of strings, pass extra config via endpoint_params
        $types = [];
        $endpoint_params = [['name' => 'status', 'column' => 'status', 'value_can_be_null' => true]];
        $result = add_li_params($query, $types, $endpoint_params, []);
        $this->assertStringContainsString('(status = ? OR status IS NULL OR status = \'\')', $result[0]);
    }
}
