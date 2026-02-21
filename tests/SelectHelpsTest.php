<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

use function API\SelectHelps\get_select;

class SelectHelpsTest extends TestCase
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

    public function testGetSelectDefault(): void
    {
        $endpoint_params = [];
        $endpoint_columns = ['title', 'lang', 'date'];
        $result = get_select($endpoint_params, $endpoint_columns);
        $this->assertSame('*', $result);
    }

    public function testGetSelectWithExplicitWildcard(): void
    {
        $_GET['select'] = '*';
        $endpoint_params = [];
        $endpoint_columns = ['title', 'lang'];
        $result = get_select($endpoint_params, $endpoint_columns);
        $this->assertSame('*', $result);
    }

    public function testGetSelectWithFalseSelects(): void
    {
        $false_selects = ['false', '0', 'select'];
        $endpoint_params = [];
        $endpoint_columns = ['title'];

        foreach ($false_selects as $false_select) {
            $_GET['select'] = $false_select;
            $result = get_select($endpoint_params, $endpoint_columns);
            $this->assertSame('*', $result, "Failed for false select: $false_select");
        }
    }

    public function testGetSelectWithValidColumn(): void
    {
        $_GET['select'] = 'title';
        $endpoint_params = [];
        $endpoint_columns = ['title', 'lang'];
        $result = get_select($endpoint_params, $endpoint_columns);
        $this->assertSame('title', $result);
    }

    public function testGetSelectWithInvalidColumnReturnsWildcard(): void
    {
        $_GET['select'] = 'invalid_column';
        $endpoint_params = [];
        $endpoint_columns = ['title', 'lang'];
        $result = get_select($endpoint_params, $endpoint_columns);
        $this->assertSame('*', $result);
    }

    public function testGetSelectWithValidCountExpressions(): void
    {
        $valid_counts = [
            'count',
            'count(*) as count',
            'count(title) as count',
            'count(p.title) as count'
        ];
        $endpoint_params = [];
        $endpoint_columns = ['title'];

        foreach ($valid_counts as $count_expr) {
            $_GET['select'] = $count_expr;
            $result = get_select($endpoint_params, $endpoint_columns);
            $this->assertSame($count_expr, $result, "Failed for count expression: $count_expr");
        }
    }

    public function testGetSelectWithYearExpressions(): void
    {
        $year_exprs = [
            'year(date) as year',
            'year(p.date) as year',
            'year(pupdate) as year',
            'year(p.pupdate) as year'
        ];
        $endpoint_params = [];
        $endpoint_columns = ['title'];

        foreach ($year_exprs as $year_expr) {
            $_GET['select'] = $year_expr;
            $result = get_select($endpoint_params, $endpoint_columns);
            $this->assertSame($year_expr, $result, "Failed for year expression: $year_expr");
        }
    }

    public function testGetSelectWithAliasExpansion(): void
    {
        $endpoint_params = [];
        $endpoint_columns = ['title'];

        $_GET['select'] = 'count(*)';
        $result = get_select($endpoint_params, $endpoint_columns);
        $this->assertSame('count(*) as count', $result);

        $_GET['select'] = 'year';
        $result = get_select($endpoint_params, $endpoint_columns);
        $this->assertSame('year(pupdate) as year', $result);
    }

    public function testGetSelectWithSupportedParam(): void
    {
        $_GET['select'] = 'user';
        $endpoint_params = [
            ['name' => 'user'],
            ['name' => 'lang']
        ];
        $endpoint_columns = ['title'];
        $result = get_select($endpoint_params, $endpoint_columns);
        $this->assertSame('user', $result);
    }

    public function testGetSelectWithSelectOptions(): void
    {
        $_GET['select'] = 'option1';
        $endpoint_params = [
            [
                'name' => 'select',
                'options' => ['option1', 'option2', 'option3']
            ]
        ];
        $endpoint_columns = ['title'];
        $result = get_select($endpoint_params, $endpoint_columns);
        $this->assertSame('option1', $result);
    }

    public function testGetSelectWithCountParameter(): void
    {
        $_GET['select'] = 'lang';
        $_GET['count'] = '*';
        $endpoint_params = [];
        $endpoint_columns = ['lang', 'title'];
        $result = get_select($endpoint_params, $endpoint_columns);
        $this->assertSame('lang, COUNT(*) as count', $result);
    }

    public function testGetSelectWithCountOnColumn(): void
    {
        $_GET['select'] = 'user';
        $_GET['count'] = 'title';
        $endpoint_params = [];
        $endpoint_columns = ['user', 'title'];
        $result = get_select($endpoint_params, $endpoint_columns);
        $this->assertSame('user, COUNT(title) as count', $result);
    }

    public function testGetSelectCaseInsensitiveValidation(): void
    {
        $_GET['select'] = 'LANG';
        $endpoint_params = [];
        $endpoint_columns = ['title'];
        $result = get_select($endpoint_params, $endpoint_columns);
        // 'lang' is in select_valids, but 'LANG' is not (case-sensitive check)
        // Actually the code does strtolower check, so this should work
        $this->assertSame('LANG', $result);
    }

    public function testGetSelectWithLangColumn(): void
    {
        $_GET['select'] = 'lang';
        $endpoint_params = [];
        $endpoint_columns = ['title'];
        $result = get_select($endpoint_params, $endpoint_columns);
        $this->assertSame('lang', $result);
    }

    public function testGetSelectWithPrefixedColumns(): void
    {
        $_GET['select'] = 'p.lang';
        $endpoint_params = [];
        $endpoint_columns = ['title'];
        $result = get_select($endpoint_params, $endpoint_columns);
        $this->assertSame('p.lang', $result);

        $_GET['select'] = 'p.user';
        $result = get_select($endpoint_params, $endpoint_columns);
        $this->assertSame('p.user', $result);
    }

    public function testGetSelectWithGTitle(): void
    {
        $_GET['select'] = 'g_title';
        $endpoint_params = [];
        $endpoint_columns = ['title'];
        $result = get_select($endpoint_params, $endpoint_columns);
        $this->assertSame('g_title', $result);
    }

    public function testGetSelectWithUserColumn(): void
    {
        $_GET['select'] = 'user';
        $endpoint_params = [];
        $endpoint_columns = ['title'];
        $result = get_select($endpoint_params, $endpoint_columns);
        $this->assertSame('user', $result);
    }
}
