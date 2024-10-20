<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ConfigTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testConfig(): void
    {
        $firstName = config("test.name.first_name");
        $lastName = config("test.name.last_name");

        self::assertEquals("Mochammad", $firstName);
        self::assertEquals("Fajrin", $lastName);
    }
}
