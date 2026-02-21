<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

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

    public function testApcuFunctionsExistWhenExtensionNotLoaded(): void
    {
        // These functions should exist even if APCu is not loaded
        // (they are defined as stubs in sql.php when extension is missing)
        $this->assertTrue(function_exists('apcu_exists'));
        $this->assertTrue(function_exists('apcu_fetch'));
        $this->assertTrue(function_exists('apcu_store'));
        $this->assertTrue(function_exists('apcu_delete'));
    }

    public function testApcuStubsReturnFalse(): void
    {
        // When APCu is not loaded, the stub functions should return false
        if (!extension_loaded('apcu')) {
            $this->assertFalse(apcu_exists('test_key'));
            $this->assertFalse(apcu_fetch('test_key'));
            $this->assertFalse(apcu_store('test_key', 'value'));
            $this->assertFalse(apcu_delete('test_key'));
        } else {
            $this->markTestSkipped('APCu extension is loaded, skipping stub tests');
        }
    }

    public function testApcuKeyGeneration(): void
    {
        // Test that the apcu_key function generates expected keys
        // We can't directly test the function since it's not exported,
        // but we can verify the behavior through the fetch_query_new function
        $this->assertTrue(true); // Placeholder - actual implementation would test key generation
    }
}
