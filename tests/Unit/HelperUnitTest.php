<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class HelperUnitTest extends TestCase
{
    public function test_basic_assertions(): void
    {
        $this->assertTrue(true);
        $this->assertFalse(false);
        $this->assertEquals(4, 2 + 2);
    }

    public function test_string_operations(): void
    {
        $this->assertStringContainsString('laravel', strtolower('FilamentCraft Laravel'));
        $this->assertStringStartsWith('Hello', 'Hello World');
    }

    public function test_array_operations(): void
    {
        $array = ['name' => 'Test', 'email' => 'test@example.com'];
        $this->assertArrayHasKey('name', $array);
        $this->assertCount(2, $array);
    }
}
