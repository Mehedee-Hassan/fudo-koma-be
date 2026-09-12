<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_login_screen_is_available(): void
    {
        $this->get('/login')->assertOk()->assertSee('Welcome back.');
    }
}
