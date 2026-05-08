<?php

namespace Database\Seeders;

use App\Models\CmsSetting;
use Illuminate\Database\Seeder;

class CmsSettingsSeeder extends Seeder
{
    public function run(): void
    {
        // ============================================
        // Global Settings
        // ============================================
        CmsSetting::set('main_bg_color', '#6366f1', 'global', 'color');
        CmsSetting::set('light_bg_color', '#f9fafb', 'global', 'color');
        CmsSetting::set('text_color', '#111827', 'global', 'color');
        CmsSetting::set('heading_color', '#111827', 'global', 'color');
        CmsSetting::set('font_family', 'DM Sans', 'global', 'text');

        // ============================================
        // Hero Section
        // ============================================
        CmsSetting::set('badge_text', '⚡ Build Applications Without Writing Code', 'hero', 'text');
        CmsSetting::set('title', 'Turn Ideas Into Production-Ready Apps in Minutes', 'hero', 'text');
        CmsSetting::set('description', 'Craft Laravel is a dynamic system builder powered by Laravel & Filament. Generate full CRUD modules, manage users with granular permissions, and customize your CMS — all from a visual interface. No boilerplate, no repetition.', 'hero', 'text');
        CmsSetting::set('primary_button_text', 'Start Building Free', 'hero', 'text');
        CmsSetting::set('primary_button_url', url('/login'), 'hero', 'text');
        CmsSetting::set('secondary_button_text', 'See How It Works', 'hero', 'text');
        CmsSetting::set('secondary_button_url', route('guide'), 'hero', 'text');
        CmsSetting::set('stats', [
            ['label' => '10x', 'text' => 'Faster Development'],
            ['label' => '100%', 'text' => 'Laravel Native Code'],
            ['label' => '∞', 'text' => 'Modules You Can Create'],
        ], 'hero', 'json');

        // ============================================
        // Features Section (6 cards)
        // ============================================
        CmsSetting::set('items', [
            [
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>',
                'title' => 'Visual CRUD Generator',
                'description' => 'Define models, fields, validations, and relationships through an intuitive wizard. Craft Laravel generates Models, Migrations, Filament Resources, Controllers, and Views — instantly.',
            ],
            [
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>',
                'title' => 'Smart Relationships',
                'description' => 'Wire up BelongsTo, HasMany, HasOne, and BelongsToMany relationships visually. Foreign keys, pivot tables, and display columns are handled automatically.',
            ],
            [
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>',
                'title' => 'Granular Role-Based Access',
                'description' => 'Built on Spatie Permission. Create roles, assign permissions per module, and control exactly who can create, edit, view, or delete — down to the action level.',
            ],
            [
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>',
                'title' => 'Dynamic CMS & Menus',
                'description' => 'Manage pages, content sections, navigation menus, and site settings on the fly. Your marketing team can update content without touching a single line of code.',
            ],
            [
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>',
                'title' => 'Advanced Query Conditions',
                'description' => 'Apply custom Where, OrWhere, WhereHas, and WhereDoesntHave filters to your generated tables and forms. Control data visibility with zero code.',
            ],
            [
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>',
                'title' => 'Production-Ready Laravel Code',
                'description' => 'Every generated file follows Laravel and Filament best practices. Clean architecture, proper type hints, and full PSR compliance — ready to deploy.',
            ],
        ], 'features', 'json');

        // ============================================
        // How It Works Section (4 steps)
        // ============================================
        CmsSetting::set('items', [
            [
                'number' => 1,
                'title' => 'Design Your Module',
                'description' => 'Name your model and define fields using 15+ input types — text, number, date, select, file upload, rich text, and more.',
            ],
            [
                'number' => 2,
                'title' => 'Link Relationships',
                'description' => 'Connect your module to other models. Choose relationship types, display columns, and let the system handle keys and pivot tables.',
            ],
            [
                'number' => 3,
                'title' => 'Set Rules & Filters',
                'description' => 'Configure validations, query conditions, and table columns. Decide what appears in forms, tables, and search results.',
            ],
            [
                'number' => 4,
                'title' => 'Generate & Go Live',
                'description' => 'Click Generate. Your Model, Migration, Filament Resource, and Views are created instantly. Start managing data immediately.',
            ],
        ], 'steps', 'json');

        // ============================================
        // CTA Section
        // ============================================
        CmsSetting::set('title', 'Stop Writing Repetitive CRUD. Start Building.', 'cta', 'text');
        CmsSetting::set('subtitle', 'Join developers who use Craft Laravel to ship Laravel applications in hours, not weeks.', 'cta', 'text');
        CmsSetting::set('button_text', 'Get Started Now', 'cta', 'text');
        CmsSetting::set('button_url', url('/login'), 'cta', 'text');

        // ============================================
        // Legal Pages
        // ============================================
        CmsSetting::set('title', 'Privacy Policy', 'privacy', 'text');
        CmsSetting::set('subtitle', 'Your data privacy matters to us', 'privacy', 'text');
        CmsSetting::set('content', '<p>At Craft Laravel, we respect your privacy. This policy outlines how we collect, use, and protect your information when you use our platform.</p><h3>Information We Collect</h3><p>We collect account information, generated module configurations, and usage data to provide and improve our services.</p><h3>How We Use Your Data</h3><p>Your data is used solely for operating the platform, generating your modules, and enhancing user experience. We never sell your data.</p>', 'privacy', 'text');

        CmsSetting::set('title', 'Terms & Conditions', 'terms', 'text');
        CmsSetting::set('subtitle', 'Please read these terms carefully before using Craft Laravel', 'terms', 'text');
        CmsSetting::set('content', '<p>By accessing and using Craft Laravel, you agree to be bound by these Terms & Conditions.</p><h3>Acceptance of Terms</h3><p>By creating an account or using the platform, you acknowledge that you have read, understood, and agree to these terms.</p><h3>Use License</h3><p>You are granted a limited license to use Craft Laravel for building and managing your applications. Generated code is yours to own and modify.</p>', 'terms', 'text');

        // ============================================
        // Auth Section
        // ============================================
        CmsSetting::set('header_button_text', 'Get Started', 'auth', 'text');
        CmsSetting::set('login_layout', 'simple', 'auth', 'text');

        // Login
        CmsSetting::set('login_heading', 'Welcome Back', 'auth', 'text');
        CmsSetting::set('login_description', 'Sign in to access your Craft Laravel dashboard and manage your modules.', 'auth', 'text');
        CmsSetting::set('login_right_heading', 'Build Faster with Craft Laravel', 'auth', 'text');
        CmsSetting::set('login_right_description', 'Generate complete Laravel modules in minutes. Manage users, content, and permissions from one powerful admin panel.', 'auth', 'text');

        // Register
        CmsSetting::set('register_heading', 'Create Your Account', 'auth', 'text');
        CmsSetting::set('register_description', 'Start building dynamic Laravel applications without writing repetitive boilerplate code.', 'auth', 'text');
        CmsSetting::set('register_right_heading', 'Ship Laravel Apps 10x Faster', 'auth', 'text');
        CmsSetting::set('register_right_description', 'Join a growing community of developers using Craft Laravel to turn ideas into production-ready admin panels and APIs.', 'auth', 'text');

        // Forgot Password
        CmsSetting::set('forgot_heading', 'Reset Password', 'auth', 'text');
        CmsSetting::set('forgot_description', 'Enter your email to receive a secure password reset link.', 'auth', 'text');
        CmsSetting::set('forgot_right_heading', 'Recover Access', 'auth', 'text');
        CmsSetting::set('forgot_right_description', 'No worries. Enter your email and we will send you a secure link to reset your password.', 'auth', 'text');

        // Reset Password
        CmsSetting::set('reset_heading', 'Set New Password', 'auth', 'text');
        CmsSetting::set('reset_description', 'Enter your new password below to regain access to your account.', 'auth', 'text');
        CmsSetting::set('reset_right_heading', 'Secure Your Account', 'auth', 'text');
        CmsSetting::set('reset_right_description', 'Choose a strong password to keep your Craft Laravel account secure.', 'auth', 'text');

        // ============================================
        // Footer Settings
        // ============================================
        CmsSetting::set('description', 'Craft Laravel — A dynamic system builder and CMS platform powered by Laravel & Filament. Build modules, manage users, and launch faster.', 'footer', 'text');
        CmsSetting::set('copyright_text', '© :year Craft Laravel. All rights reserved.', 'footer', 'text');
        CmsSetting::set('copyright_pages', [], 'footer', 'json');
        CmsSetting::set('social_icons', [
            ['platform' => 'Facebook', 'url' => 'https://www.facebook.com', 'icon_svg' => '<path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>', 'is_active' => true],
            ['platform' => 'X', 'url' => 'https://x.com', 'icon_svg' => '<path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>', 'is_active' => true],
            ['platform' => 'GitHub', 'url' => 'https://github.com', 'icon_svg' => '<path d="M12 0C5.374 0 0 5.373 0 12c0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23A11.509 11.509 0 0112 5.803c1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576C20.566 21.797 24 17.3 24 12c0-6.627-5.373-12-12-12z"/>', 'is_active' => true],
            ['platform' => 'LinkedIn', 'url' => 'https://www.linkedin.com', 'icon_svg' => '<path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>', 'is_active' => true],
        ], 'footer', 'json');

        // ============================================
        // About Page
        // ============================================
        CmsSetting::set('title', 'About Craft Laravel', 'about', 'text');
        CmsSetting::set('subtitle', 'The Laravel system builder built for developers who value speed without sacrificing quality', 'about', 'text');
        CmsSetting::set('story_title', 'Why We Built Craft Laravel', 'about', 'text');
        CmsSetting::set('story_content', 'We were tired of writing the same CRUD boilerplate for every new Laravel project. Models, migrations, controllers, Filament resources, validation rules — hours of repetitive work before any real feature could be built. Craft Laravel was born to eliminate that friction. Now, a complete admin module with relationships, permissions, and query filters can be generated in under five minutes.', 'about', 'text');
        CmsSetting::set('mission_title', 'Our Mission', 'about', 'text');
        CmsSetting::set('mission_content', 'To empower Laravel developers and teams to ship robust, scalable applications faster than ever. We believe your time is best spent solving business problems — not wiring up database tables.', 'about', 'text');
        CmsSetting::set('why_title', 'Why Developers Choose Craft Laravel', 'about', 'text');
        CmsSetting::set('why_items', [
            ['title' => 'Laravel Native', 'description' => 'Every file generated follows Laravel conventions. No proprietary lock-in.'],
            ['title' => 'Filament Powered', 'description' => 'Beautiful, accessible admin panels built on the industry-leading Filament framework.'],
            ['title' => 'Granular Permissions', 'description' => 'Built-in RBAC with Spatie Permission. Control access at the module and action level.'],
            ['title' => 'Dynamic CMS', 'description' => 'Manage landing pages, menus, and site content without deploying code.'],
            ['title' => 'Always Improving', 'description' => 'Regular updates with new field types, relationship handlers, and CMS features.'],
        ], 'about', 'json');

        // ============================================
        // Guide Page
        // ============================================
        CmsSetting::set('title', 'Craft Laravel Guide', 'guide', 'text');
        CmsSetting::set('subtitle', 'Master the platform and build complete Laravel modules in minutes', 'guide', 'text');
        CmsSetting::set('hero_icon', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>', 'guide', 'text');
        CmsSetting::set('steps_title', 'Getting Started', 'guide', 'text');
        CmsSetting::set('steps', [
            ['number' => 1, 'title' => 'Access the Generator', 'description' => 'Navigate to CRUD Generator in the admin sidebar. Click "Create" to launch the module wizard.'],
            ['number' => 2, 'title' => 'Define Fields & Types', 'description' => 'Add fields with database types and HTML input types. Choose from text, number, date, select, file upload, and more.'],
            ['number' => 3, 'title' => 'Configure Relationships', 'description' => 'Link your module to existing models. Select BelongsTo, HasMany, HasOne, or BelongsToMany. The system auto-configures keys and pivot tables.'],
            ['number' => 4, 'title' => 'Set Query Conditions', 'description' => 'Add custom Where, WhereHas, and OrWhere filters to control what data appears in tables and dropdowns.'],
            ['number' => 5, 'title' => 'Generate & Manage', 'description' => 'Click Generate. Your module is live. Manage data through the auto-generated Filament resource with full search, sort, and export support.'],
        ], 'guide', 'json');
        CmsSetting::set('field_types', [
            ['icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>', 'title' => 'Text Fields', 'description' => 'String, Text, Textarea, Email, URL, Rich Text Editor'],
            ['icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>', 'title' => 'Number Fields', 'description' => 'Integer, BigInteger, Decimal, Float, Double'],
            ['icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>', 'title' => 'Date & Time', 'description' => 'Date, DateTime, Time, Timestamp — with calendar pickers'],
            ['icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>', 'title' => 'Selection', 'description' => 'Select, Multiselect, Radio, Checkbox, Toggle'],
            ['icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>', 'title' => 'Media', 'description' => 'Image Upload, File Upload with preview and validation'],
            ['icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>', 'title' => 'Relationships', 'description' => 'BelongsTo, HasMany, HasOne, BelongsToMany with auto key handling'],
        ], 'guide', 'json');
        CmsSetting::set('relations', [
            ['type' => 'BelongsTo', 'title' => 'BelongsTo', 'description' => 'Many-to-one (e.g., Post belongs to a Category)'],
            ['type' => 'HasMany', 'title' => 'HasMany', 'description' => 'One-to-many (e.g., Category has many Posts)'],
            ['type' => 'HasOne', 'title' => 'HasOne', 'description' => 'One-to-one (e.g., User has one Profile)'],
            ['type' => 'BelongsToMany', 'title' => 'BelongsToMany', 'description' => 'Many-to-many with pivot table support (e.g., Post belongs to many Tags)'],
        ], 'guide', 'json');
        CmsSetting::set('best_practices', [
            ['text' => 'Use singular CamelCase names for your models (e.g., BlogPost, ProductCategory)'],
            ['text' => 'Set unique and required validations on critical fields early'],
            ['text' => 'Define relationships before generating if other modules depend on them'],
            ['text' => 'Use query conditions to filter dropdown data and improve performance'],
            ['text' => 'Configure table columns to show only the most relevant data'],
            ['text' => 'Review generated code and customize business logic as needed'],
        ], 'guide', 'json');
        CmsSetting::set('help_title', 'Need Help?', 'guide', 'text');
        CmsSetting::set('help_content', 'Our team is here to help you get the most out of Craft Laravel. Reach out via the contact page or check the documentation for detailed guides on advanced features.', 'guide', 'text');

        // ============================================
        // Contact Page
        // ============================================
        CmsSetting::set('title', 'Get in Touch', 'contact', 'text');
        CmsSetting::set('subtitle', 'Have questions about Craft Laravel? We would love to hear from you.', 'contact', 'text');
        CmsSetting::set('heading', "Let's Build Something Great", 'contact', 'text');
        CmsSetting::set('intro', "Whether you need support, have a feature request, or want to discuss how Craft Laravel can accelerate your team's development workflow.", 'contact', 'text');
        CmsSetting::set('info_items', [
            ['icon' => 'mail', 'title' => 'Email Us', 'line1' => 'support@quebixtechnology.com', 'line2' => 'We respond within 24 hours'],
            ['icon' => 'location', 'title' => 'Location', 'line1' => 'Remote-First Team', 'line2' => 'Serving developers worldwide'],
            ['icon' => 'clock', 'title' => 'Support Hours', 'line1' => '24/7 Priority Support', 'line2' => 'For enterprise and pro plans'],
        ], 'contact', 'json');
        CmsSetting::set('form_title', 'Send us a Message', 'contact', 'text');
        CmsSetting::set('form_subtitle', "Fill out the form below and we'll get back to you shortly.", 'contact', 'text');
    }
}
