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
        $types = ['title', 'lang', 'user'];
        $endpoint_params = [];
        $ignore_params = [];

        $result = change_types($types, $endpoint_params, $ignore_params);

        $this->assertSame(['title' => ['column' => 'title'], 'lang' => ['column' => 'lang'], 'user' => ['column' => 'user']], $result);
    }

    public function testChangeTypesFallsBackToEndpointParams(): void
    {
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
        $types = [];
        $endpoint_params = [
            ['name' => 'title', 'column' => 'w_title'],
            ['name' => 'hidden_field', 'column' => 'hidden_col', 'no_select' => true],
            ['name' => 'lang', 'column' => 'lang_code']
        ];
        $ignore_params = [];

        $result = change_types($types, $endpoint_params, $ignore_params);

        // hidden_field should be skipped because it has 'no_select' => true
        $this->assertArrayHasKey('title', $result);
        $this->assertArrayNotHasKey('hidden_field', $result);
        $this->assertArrayHasKey('lang', $result);
    }

    public function testChangeTypesIgnoresSpecifiedParams(): void
    {
        $types = ['title' => ['column' => 'w_title'], 'lang' => ['column' => 'lang_code']];
        $endpoint_params = [];
        $ignore_params = ['lang'];

        $result = change_types($types, $endpoint_params, $ignore_params);

        $this->assertArrayHasKey('title', $result);
        $this->assertArrayNotHasKey('lang', $result);
    }

    public function testChangeTypesPrefersTypesOverEndpointParams(): void
    {
        $types = ['title' => ['column' => 'custom_title']];
        $endpoint_params = [
            ['name' => 'title', 'column' => 'w_title']
        ];
        $ignore_params = [];

        $result = change_types($types, $endpoint_params, $ignore_params);

        // When types is not empty, it should use types, not endpoint_params
        $this->assertSame(['title' => ['column' => 'custom_title']], $result);
    }

    public function testChangeTypesPreservesAdditionalTypeProperties(): void
    {
        $types = [
            'status' => ['column' => 'status_col', 'value_can_be_null' => true],
            'filter' => ['column' => 'filter_col', 'no_empty_value' => true]
        ];
        $endpoint_params = [];
        $ignore_params = [];

        $result = change_types($types, $endpoint_params, $ignore_params);

        $this->assertSame([
            'status' => ['column' => 'status_col', 'value_can_be_null' => true],
            'filter' => ['column' => 'filter_col', 'no_empty_value' => true]
        ], $result);
    }
}
