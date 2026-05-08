<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\Customer;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_model_has_fillable_attributes(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
    }

    public function test_blog_model_can_be_created(): void
    {
        $blog = Blog::create([
            'name' => 'Test Blog',
            'description' => 'This is a test blog description',
        ]);

        $this->assertDatabaseHas('blogs', [
            'name' => 'Test Blog',
            'description' => 'This is a test blog description',
        ]);
    }

    public function test_customer_model_can_be_created(): void
    {
        $customer = Customer::create([
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->assertDatabaseHas('customers', [
            'first_name' => 'Jane',
            'email' => 'jane@example.com',
        ]);
    }

    public function test_menu_model_generates_slug(): void
    {
        $menu = Menu::create([
            'page_name' => 'About Us',
            'page_type' => 'content',
            'placement' => 'header',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->assertEquals('about-us', $menu->slug);
    }

    public function test_menu_get_route_url_for_content_type(): void
    {
        $menu = Menu::create([
            'page_name' => 'Contact',
            'page_type' => 'content',
            'slug' => 'contact',
            'placement' => 'header',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->assertStringContainsString('contact', $menu->getRouteUrl());
    }

    public function test_user_password_is_hashed(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('secret123'),
        ]);

        $this->assertNotEquals('secret123', $user->password);
    }
}
