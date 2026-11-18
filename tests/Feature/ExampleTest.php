<?php
// tests/Feature/ExampleTest.php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_application_redirects_to_katalog(): void
    {
        $response = $this->get('/');
        $response->assertRedirect(route('travel.katalog'));
    }
}