<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

use function API\Helps\change_types;

/**
 * Tests for change_types function in api_cod/helps.php
 * This function converts type definitions for query parameter handling
 */
class ChangeTypesTest extends TestCase
{
    public function testChangeTypesWithEmptyArrays(): void
    {
        $types = [];
        $endpoint_params = [];
        $ignore_params = [];

        $result = change_types($types, $endpoint_params, $ignore_params);

        $this->assertSame([], $result);
    }

    public function testChangeTypesWithSimpleTypes(): void
    {
        // When $types is an array of strings, it converts them to column definitions
        $types = ['title', 'lang', 'user'];
        $endpoint_params = [];
        $ignore_params = [];

        $result = change_types($types, $endpoint_params, $ignore_params);

        $this->assertSame([
            'title' => ['column' => 'title'],
            'lang' => ['column' => 'lang'],
            'user' => ['column' => 'user']
        ], $result);
    }

    public function testChangeTypesFallsBackToEndpointParams(): void
    {
        // When $types is empty, it falls back to using $endpoint_params
        $types = [];
        $endpoint_params = [
            ['name' => 'title', 'column' => 'w_title'],
            ['name' => 'lang', 'column' => 'lang_code']
        ];
        $ignore_params = [];

        $result = change_types($types, $endpoint_params, $ignore_params);

        $this->assertSame([
            'title' => ['name' => 'title', 'column' => 'w_title'],
            'lang' => ['name' => 'lang', 'column' => 'lang_code']
        ], $result);
    }

    public function testChangeTypesSkipsNoSelectParams(): void
    {
        // Params with 'no_select' => true should be skipped when falling back to endpoint_params
        $types = [];
        $endpoint_params = [
            ['name' => 'title', 'column' => 'w_title'],
            ['name' => 'hidden_field', 'column' => 'hidden_col', 'no_select' => true],
            ['name' => 'lang', 'column' => 'lang_code']
        ];
        $ignore_params = [];

        $result = change_types($types, $endpoint_params, $ignore_params);

        $this->assertArrayHasKey('title', $result);
        $this->assertArrayNotHasKey('hidden_field', $result);
        $this->assertArrayHasKey('lang', $result);
    }

    public function testChangeTypesIgnoresSpecifiedParams(): void
    {
        // When $types is an array of strings, $ignore_params removes items from the result
        $types = ['title', 'lang', 'user'];
        $endpoint_params = [];
        $ignore_params = ['lang'];

        $result = change_types($types, $endpoint_params, $ignore_params);

        $this->assertArrayHasKey('title', $result);
        $this->assertArrayNotHasKey('lang', $result);
        $this->assertArrayHasKey('user', $result);
    }

    public function testChangeTypesPrefersTypesOverEndpointParams(): void
    {
        // When $types is provided (not empty), it should be used instead of $endpoint_params
        $types = ['custom_title'];
        $endpoint_params = [
            ['name' => 'title', 'column' => 'w_title']
        ];
        $ignore_params = [];

        $result = change_types($types, $endpoint_params, $ignore_params);

        // Should use $types, not $endpoint_params
        $this->assertSame(['custom_title' => ['column' => 'custom_title']], $result);
    }

    public function testChangeTypesFallsBackToEndpointParamsOnlyWhenTypesEmpty(): void
    {
        // Verify that empty $types triggers fallback to $endpoint_params
        $types = [];
        $endpoint_params = [
            ['name' => 'param1', 'column' => 'col1'],
            ['name' => 'param2', 'column' => 'col2']
        ];
        $ignore_params = [];

        $result = change_types($types, $endpoint_params, $ignore_params);

        $this->assertCount(2, $result);
        $this->assertArrayHasKey('param1', $result);
        $this->assertArrayHasKey('param2', $result);
    }

    public function testChangeTypesIgnoresFromEndpointParams(): void
    {
        // $ignore_params should work when falling back to $endpoint_params
        $types = [];
        $endpoint_params = [
            ['name' => 'title', 'column' => 'w_title'],
            ['name' => 'lang', 'column' => 'lang_code']
        ];
        $ignore_params = ['lang'];

        $result = change_types($types, $endpoint_params, $ignore_params);

        $this->assertArrayHasKey('title', $result);
        $this->assertArrayNotHasKey('lang', $result);
    }

    public function testChangeTypesHandlesEmptyIgnoreParams(): void
    {
        $types = ['title', 'lang'];
        $endpoint_params = [];
        $ignore_params = [];

        $result = change_types($types, $endpoint_params, $ignore_params);

        $this->assertCount(2, $result);
        $this->assertArrayHasKey('title', $result);
        $this->assertArrayHasKey('lang', $result);
    }
}
