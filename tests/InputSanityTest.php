<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

/**
 * Simple test to verify the test environment is working
 */
class InputSanityTest extends TestCase
{
    public function testPhpVersion(): void
    {
        $this->assertTrue(PHP_VERSION_ID >= 80000, 'PHP 8.0 or higher required');
    }

    public function testAutoloadingWorks(): void
    {
        // Test that we can access functions from the codebase
        $this->assertTrue(function_exists('API\Helps\sanitize_input'));
        $this->assertTrue(function_exists('API\SelectHelps\get_select'));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testGetSuperglobalWorksInSeparateProcess(): void
    {
        $_GET['test'] = 'value';
        $this->assertSame('value', $_GET['test']);
    }
}
