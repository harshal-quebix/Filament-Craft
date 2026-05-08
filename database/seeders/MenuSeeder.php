<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing menus first
        Menu::truncate();

        // Header Menus
        Menu::create([
            'page_name' => 'About',
            'page_type' => 'url',
            'url' => '/about',
            'placement' => 'header',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Menu::create([
            'page_name' => 'Guide',
            'page_type' => 'url',
            'url' => '/crud-builder-guide',
            'placement' => 'header',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        Menu::create([
            'page_name' => 'Contact',
            'page_type' => 'url',
            'url' => '/contact',
            'placement' => 'header',
            'sort_order' => 3,
            'is_active' => true,
        ]);

        // Footer Menus
        Menu::create([
            'page_name' => 'About Us',
            'page_type' => 'url',
            'url' => '/about',
            'placement' => 'footer',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Menu::create([
            'page_name' => 'Documentation',
            'page_type' => 'url',
            'url' => '/crud-builder-guide',
            'placement' => 'footer',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        Menu::create([
            'page_name' => 'Support',
            'page_type' => 'url',
            'url' => '/contact',
            'placement' => 'footer',
            'sort_order' => 3,
            'is_active' => true,
        ]);

        // Example Content Page
        Menu::create([
            'page_name' => 'FAQ',
            'page_type' => 'content',
            'slug' => 'faq',
            'content' => '<h2>Frequently Asked Questions</h2>
            <h3>What is Craft Laravel?</h3>
            <p>Craft Laravel is a dynamic system builder and CMS platform powered by Laravel & Filament. It lets you generate complete CRUD modules, manage users with role-based permissions, and customize your landing pages — all without writing repetitive boilerplate code.</p>
            <h3>How do I get started?</h3>
            <p>Log in to the admin panel, open the CRUD Generator, and follow the wizard to define your model, fields, relationships, and validations. Click Generate and your module is live instantly.</p>
            <h3>What can I build with it?</h3>
            <p>Anything that needs a database-backed admin panel — blogs, e-commerce catalogs, project management tools, inventory systems, user directories, and more. The generated code is 100% Laravel native.</p>
            <h3>Is the generated code mine to keep?</h3>
            <p>Absolutely. Every Model, Migration, Controller, and Filament Resource is generated directly into your project. You own the code and can customize it freely.</p>',
            'placement' => 'footer',
            'sort_order' => 4,
            'is_active' => true,
        ]);
    }
}
