<?php

namespace Tests\Feature;

use Tests\TestCase;

class RoutingTest extends TestCase
{
    public function test_landing_page_route_exists(): void
    {
        $response = $this->get('/');
        // May redirect to installer if not installed
        $this->assertTrue(in_array($response->getStatusCode(), [200, 302]));
    }

    public function test_css_theme_route_exists(): void
    {
        $response = $this->get('/css/theme.css');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/css; charset=UTF-8');
    }

    public function test_login_route_exists(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_register_route_exists(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
    }

    public function test_password_reset_route_exists(): void
    {
        $response = $this->get('/password/reset');
        $response->assertStatus(200);
    }

    public function test_logout_requires_post_method(): void
    {
        $response = $this->get('/logout');
        $response->assertStatus(405);
    }
}
