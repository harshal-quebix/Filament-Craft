<?php

if (!function_exists('setting')) {
    function setting($key, $default = null)
    {
        return \App\Models\Setting::where('key', $key)->value('value') ?? $default;
    }
}

if (!function_exists('getSetting')) {
    function getSetting($key, $default = null)
    {
        try {
            return \App\Models\Setting::where('key', $key)->value('value') ?? $default;
        } catch (\Exception $e) {
            return $default;
        }
    }
}

if (!function_exists('getLogo')) {
    function getLogo($type = 'light')
    {
        $key = $type === 'light' ? 'logo_light' : 'logo_dark';

        // Try to get from settings first
        try {
            $path = \App\Models\Setting::where('key', $key)->value('value');
            if ($path) {
                $url = getImageUrl($path);
                if ($url) {
                    return $url;
                }
            }
        } catch (\Exception $e) {
            // Database might not be available yet
        }

        // Fallback to public default images
        $default = $type === 'light' ? 'default-img/light_logo.png' : 'default-img/dark_logo.png';
        return asset($default);
    }
}

if (!function_exists('getFavicon')) {
    function getFavicon()
    {
        // Try to get from settings first
        try {
            $path = \App\Models\Setting::where('key', 'favicon')->value('value');
            if ($path) {
                $url = getImageUrl($path);
                if ($url) {
                    return $url;
                }
            }
        } catch (\Exception $e) {
            // Database might not be available yet
        }

        // Fallback to default favicon
        return asset('default-img/favicon.png');
    }
}

if (!function_exists('getSettingImageUrl')) {
    /**
     * Get image URL from settings by key (stored as path)
     */
    function getSettingImageUrl(string $key, string $default): string
    {
        try {
            $path = \App\Models\Setting::where('key', $key)->value('value');
            if ($path) {
                $url = getImageUrl($path);
                if ($url) {
                    return $url;
                }
            }
        } catch (\Exception $e) {
            // Handle any database errors gracefully
        }

        return asset($default);
    }
}

// ============================================
// CMS Helper Functions
// ============================================

if (!function_exists('cms')) {
    /**
     * Get CMS setting - universal function
     *
     * @param string $key The setting key
     * @param string $group The settings group (global, hero, features, steps, cta)
     * @param mixed $default Default value if not found
     * @return mixed
     */
    function cms($key, $group = 'general', $default = null)
    {
        return \App\Models\CmsSetting::get($key, $group, $default);
    }
}

if (!function_exists('cmsGroup')) {
    /**
     * Get all settings for a group
     *
     * @param string $group The settings group
     * @return array
     */
    function cmsGroup($group)
    {
        return \App\Models\CmsSetting::getGroup($group);
    }
}

if (!function_exists('landingData')) {
    /**
     * Get all landing page data in one call
     *
     * @return array
     */
    function landingData()
    {
        return [
            // Global Settings
            'main_bg_color' => cms('main_bg_color', 'global', '#7369dd'),
            'light_bg_color' => cms('light_bg_color', 'global', '#f9fafb'),
            'text_color' => cms('text_color', 'global', '#111827'),
            'heading_color' => cms('heading_color', 'global', '#111827'),
            'font_family' => cms('font_family', 'global', 'DM Sans'),

            // Hero Section
            'hero' => cmsGroup('hero'),

            // Features (stored as json array)
            'features' => cms('items', 'features', []),

            // Steps (stored as json array)
            'steps' => cms('items', 'steps', []),

            // CTA Section
            'cta' => cmsGroup('cta'),
        ];
    }
}

if (!function_exists('aboutData')) {
    /**
     * Get all About page data in one call
     *
     * @return array
     */
    function aboutData()
    {
        return [
            'title' => cms('title', 'about', 'About Us'),
            'subtitle' => cms('subtitle', 'about', 'Building tools that empower developers to create faster and better'),
            'story_title' => cms('story_title', 'about', 'Our Story'),
            'story_content' => cms('story_content', 'about', 'CRUD Generator was born out of frustration with repetitive coding tasks. We realized that developers spend countless hours writing the same CRUD operations over and over again. We decided to change that.'),
            'mission_title' => cms('mission_title', 'about', 'Our Mission'),
            'mission_content' => cms('mission_content', 'about', 'To empower developers worldwide by providing tools that eliminate repetitive work and let them focus on what truly matters - building amazing features and solving real problems.'),
            'why_title' => cms('why_title', 'about', 'Why Choose Us?'),
            'why_items' => cms('why_items', 'about', [
                ['title' => 'Built by Developers', 'description' => 'We understand your pain points and challenges'],
                ['title' => 'Production Ready', 'description' => 'Following Laravel and Filament best practices'],
                ['title' => 'Always Improving', 'description' => 'Regular updates and new features'],
            ]),
        ];
    }
}

if (!function_exists('guideData')) {
    /**
     * Get all Guide page data in one call
     *
     * @return array
     */
    function guideData()
    {
        return [
            'title' => cms('title', 'guide', 'CRUD Builder Guide'),
            'subtitle' => cms('subtitle', 'guide', 'Master the art of rapid development with our comprehensive guide'),
            'hero_icon' => cms('hero_icon', 'guide', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>'),
            'steps_title' => cms('steps_title', 'guide', 'Getting Started'),
            'steps' => cms('steps', 'guide', [
                ['number' => 1, 'title' => 'Access the Generator', 'description' => 'Navigate to the admin panel and click on "CRUD Generator" in the sidebar to begin.'],
                ['number' => 2, 'title' => 'Define Your Model', 'description' => 'Enter your model name (e.g., "Product", "Customer") and start adding fields.'],
                ['number' => 3, 'title' => 'Configure Fields', 'description' => 'Add fields with appropriate types (text, number, date, etc.) and set validations.'],
                ['number' => 4, 'title' => 'Generate', 'description' => 'Click the "Generate" button and your CRUD is ready to use!'],
            ]),
            'field_types' => cms('field_types', 'guide', [
                ['icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>', 'title' => 'Text Fields', 'description' => 'String, Text, Textarea, Email, URL'],
                ['icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>', 'title' => 'Number Fields', 'description' => 'Integer, Decimal, Float'],
                ['icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>', 'title' => 'Date/Time', 'description' => 'Date, DateTime, Time, Timestamp'],
                ['icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>', 'title' => 'Selection', 'description' => 'Select, Radio, Checkbox, Toggle'],
                ['icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>', 'title' => 'Media', 'description' => 'File Upload, Image Upload'],
                ['icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>', 'title' => 'Advanced', 'description' => 'JSON, Tags, Rich Text'],
            ]),
            'relations' => cms('relations', 'guide', [
                ['type' => 'BelongsTo', 'title' => 'BelongsTo', 'description' => 'One-to-one or many-to-one (e.g., Post belongs to User)'],
                ['type' => 'HasMany', 'title' => 'HasMany', 'description' => 'One-to-many (e.g., User has many Posts)'],
                ['type' => 'BelongsToMany', 'title' => 'BelongsToMany', 'description' => 'Many-to-many (e.g., User belongs to many Roles)'],
            ]),
            'best_practices' => cms('best_practices', 'guide', [
                ['text' => 'Use descriptive model and field names'],
                ['text' => 'Set appropriate validations for data integrity'],
                ['text' => 'Configure permissions for security'],
                ['text' => 'Test generated CRUD before deploying'],
                ['text' => 'Review and customize generated code as needed'],
            ]),
            'help_title' => cms('help_title', 'guide', 'Need Help?'),
            'help_content' => cms('help_content', 'guide', 'If you have questions or need assistance, our support team is here for you.'),
        ];
    }
}

if (!function_exists('contactData')) {
    /**
     * Get all Contact page data in one call
     *
     * @return array
     */
    function contactData()
    {
        return [
            'title' => cms('title', 'contact', 'Get in Touch'),
            'subtitle' => cms('subtitle', 'contact', "Have questions about CRUD Generator? We're here to help you build faster."),
            'heading' => cms('heading', 'contact', "Let's Connect"),
            'intro' => cms('intro', 'contact', "Whether you have a question, feedback, or just want to say hello, we'd love to hear from you."),
            'info_items' => cms('info_items', 'contact', [
                ['icon' => 'mail', 'title' => 'Email Us', 'line1' => 'support@quebixtechnology.com', 'line2' => "We'll respond within 24 hours"],
                ['icon' => 'location', 'title' => 'Location', 'line1' => 'Remote Team, Worldwide', 'line2' => 'Available across all timezones'],
                ['icon' => 'clock', 'title' => 'Support Hours', 'line1' => '24/7 Support Available', 'line2' => 'Always here when you need us'],
            ]),
            'form_title' => cms('form_title', 'contact', 'Send us a Message'),
            'form_subtitle' => cms('form_subtitle', 'contact', "Fill out the form and we'll get back to you shortly."),
        ];
    }
}

if (!function_exists('footerData')) {
    /**
     * Get all footer settings in one call
     *
     * @return array
     */
    function footerData()
    {
        $copyrightText = cms('copyright_text', 'footer', '© ' . date('Y') . ' Craft Laravel. All rights reserved.');
        $copyrightText = str_replace(':year', date('Y'), $copyrightText);

        $copyrightPageIds = cms('copyright_pages', 'footer', []);
        $copyrightPages = [];
        if (!empty($copyrightPageIds)) {
            $copyrightPages = \App\Models\Menu::whereIn('id', $copyrightPageIds)
                ->where('is_active', true)
                ->get();
        }

        $socialIcons = cms('social_icons', 'footer', [
            ['platform' => 'Facebook', 'url' => 'https://www.facebook.com', 'icon_svg' => '<path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>', 'is_active' => true],
            ['platform' => 'X', 'url' => 'https://x.com', 'icon_svg' => '<path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>', 'is_active' => true],
            ['platform' => 'GitHub', 'url' => 'https://github.com', 'icon_svg' => '<path d="M12 0C5.374 0 0 5.373 0 12c0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23A11.509 11.509 0 0112 5.803c1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576C20.566 21.797 24 17.3 24 12c0-6.627-5.373-12-12-12z"/>', 'is_active' => true],
            ['platform' => 'LinkedIn', 'url' => 'https://www.linkedin.com', 'icon_svg' => '<path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>', 'is_active' => true],
        ]);

        // Filter only active icons
        $socialIcons = array_filter($socialIcons, fn ($icon) => $icon['is_active'] ?? true);

        return [
            'description' => cms('description', 'footer', 'Build faster with Laravel & Filament. Generate production-ready CRUD operations in seconds.'),
            'copyright_text' => $copyrightText,
            'copyright_pages' => $copyrightPages,
            'social_icons' => array_values($socialIcons),
        ];
    }
}

if (!function_exists('authData')) {
    /**
     * Get all auth page settings in one call
     *
     * @return array
     */
    function authData()
    {
        return [
            'header_button_text' => cms('header_button_text', 'auth', 'Get Started'),
            'login_layout' => cms('login_layout', 'auth', 'simple'),

            // Login
            'login_heading' => cms('login_heading', 'auth', 'Sign In'),
            'login_description' => cms('login_description', 'auth', 'Enter your credentials to access your account'),
            'login_right_heading' => cms('login_right_heading', 'auth', 'Secure & Reliable'),
            'login_right_description' => cms('login_right_description', 'auth', 'Your data is protected with enterprise-grade security. Access your dashboard anytime, anywhere.'),

            // Register
            'register_heading' => cms('register_heading', 'auth', 'Create Account'),
            'register_description' => cms('register_description', 'auth', 'Fill in your details to create a new account'),
            'register_right_heading' => cms('register_right_heading', 'auth', 'Join Us Today'),
            'register_right_description' => cms('register_right_description', 'auth', 'Create your account and start building faster with powerful tools designed for modern developers.'),

            // Forgot Password
            'forgot_heading' => cms('forgot_heading', 'auth', 'Reset Password'),
            'forgot_description' => cms('forgot_description', 'auth', 'Enter your email to receive a password reset link'),
            'forgot_right_heading' => cms('forgot_right_heading', 'auth', 'Recover Access'),
            'forgot_right_description' => cms('forgot_right_description', 'auth', 'No worries. Enter your email and we will send you a secure link to reset your password.'),

            // Reset Password
            'reset_heading' => cms('reset_heading', 'auth', 'Reset Password'),
            'reset_description' => cms('reset_description', 'auth', 'Enter your new password below'),
            'reset_right_heading' => cms('reset_right_heading', 'auth', 'Set New Password'),
            'reset_right_description' => cms('reset_right_description', 'auth', 'Choose a strong password to keep your account secure. Make sure it is something you can remember.'),
        ];
    }
}

if (!function_exists('legalData')) {
    /**
     * Get legal page data (Privacy Policy, Terms, etc.)
     *
     * @param string $page 'privacy' or 'terms'
     * @return array
     */
    function legalData($page)
    {
        $defaults = [
            'privacy' => [
                'title' => 'Privacy Policy',
                'subtitle' => 'Your privacy is important to us',
                'content' => '<p>At CRUD Generator, we take your privacy seriously. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our service.</p>',
            ],
            'terms' => [
                'title' => 'Terms & Conditions',
                'subtitle' => 'Please read these terms carefully before using our service',
                'content' => '<p>By accessing or using CRUD Generator, you agree to be bound by these Terms and Conditions. If you disagree with any part of the terms, you may not access the service.</p>',
            ],
        ];

        return [
            'title' => cms('title', $page, $defaults[$page]['title'] ?? ''),
            'subtitle' => cms('subtitle', $page, $defaults[$page]['subtitle'] ?? ''),
            'content' => cms('content', $page, $defaults[$page]['content'] ?? ''),
        ];
    }
}


// ============================================
// Common Upload Functions with Validation
// ============================================

if (!function_exists('uploadFileFromPath')) {
    /**
     * Upload a file from an existing local path with validation
     *
     * @param string $localPath Full local file path
     * @param string $folder Folder inside uploads
     * @param string|null $disk Storage disk
     * @param array $allowedExtensions Allowed file extensions (default: common images)
     * @param int $maxSizeBytes Maximum file size in bytes (default: 5MB)
     * @return string|null Stored file path or null
     */
    function uploadFileFromPath(string $localPath, string $folder = 'general', ?string $disk = 'public', array $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'], int $maxSizeBytes = 5242880): ?string
    {
        if (!file_exists($localPath)) {
            return null;
        }

        // Validate file size
        $fileSize = filesize($localPath);
        if ($fileSize > $maxSizeBytes) {
            \Log::warning('File upload failed: File size exceeds limit. Max: ' . $maxSizeBytes . ' bytes');
            return null;
        }

        $extension = strtolower(pathinfo($localPath, PATHINFO_EXTENSION));

        // Validate extension
        if (!in_array($extension, $allowedExtensions)) {
            \Log::warning('File upload failed: Invalid extension "' . $extension . '"');
            return null;
        }

        // Validate MIME type
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($localPath);
        $validMimes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml',
            'application/pdf', 'text/plain', 'application/json',
        ];
        if (!in_array($mimeType, $validMimes) && !str_starts_with($mimeType, 'image/')) {
            \Log::warning('File upload failed: Invalid MIME type "' . $mimeType . '"');
            return null;
        }

        try {
            $filename = time() . '_' . uniqid() . '.' . $extension;
            $storedPath = "{$folder}/{$filename}";

            \Storage::disk($disk)->put($storedPath, file_get_contents($localPath));

            return $storedPath;
        } catch (\Exception $e) {
            \Log::error('File upload from path failed: ' . $e->getMessage());
            return null;
        }
    }
}

if (!function_exists('uploadImage')) {
    /**
     * Common function to upload images with validation
     *
     * @param \Illuminate\Http\UploadedFile $file The uploaded file
     * @param string $folder The folder name inside uploads (e.g., 'hero', 'features')
     * @param string|null $disk Storage disk (default: 'public')
     * @param array $allowedMimes Allowed MIME types
     * @param int $maxSizeKb Maximum file size in KB (default: 5120 = 5MB)
     * @return string|null The stored file path or null on failure
     */
    function uploadImage($file, string $folder = 'general', ?string $disk = 'public', array $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'], int $maxSizeKb = 5120): ?string
    {
        if (!$file || !$file->isValid()) {
            return null;
        }

        // Validate file size
        if ($file->getSize() > ($maxSizeKb * 1024)) {
            \Log::warning('Image upload failed: File size exceeds ' . $maxSizeKb . 'KB');
            return null;
        }

        // Validate MIME type
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            \Log::warning('Image upload failed: Invalid MIME type "' . $file->getMimeType() . '"');
            return null;
        }

        // Validate extension
        $extension = strtolower($file->getClientOriginalExtension());
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        if (!in_array($extension, $allowedExtensions)) {
            \Log::warning('Image upload failed: Invalid extension "' . $extension . '"');
            return null;
        }

        try {
            $filename = time() . '_' . uniqid() . '.' . $extension;
            $path = $file->storeAs("{$folder}", $filename, $disk);

            return $path;
        } catch (\Exception $e) {
            \Log::error('Image upload failed: ' . $e->getMessage());
            return null;
        }
    }
}

if (!function_exists('getImageUrl')) {
    /**
     * Get full URL for uploaded image
     *
     * @param string|null $path The stored image path
     * @param string|null $disk Storage disk (default: 'public')
     * @return string|null The full URL or null
     */
    function getImageUrl(?string $path, ?string $disk = 'public'): ?string
    {
        if (empty($path)) {
            return null;
        }

        $diskInstance = \Storage::disk($disk);

        if (! $diskInstance->exists($path)) {
            return null;
        }

        return $diskInstance->url($path);
    }
}

if (!function_exists('deleteImage')) {
    /**
     * Delete an uploaded image
     *
     * @param string|null $path The stored image path
     * @param string|null $disk Storage disk (default: 'public')
     * @return bool True if deleted or not exists, false on error
     */
    function deleteImage(?string $path, ?string $disk = 'public'): bool
    {
        if (empty($path)) {
            return true;
        }

        try {
            if (\Storage::disk($disk)->exists($path)) {
                return \Storage::disk($disk)->delete($path);
            }
            return true;
        } catch (\Exception $e) {
            \Log::error('Image delete failed: ' . $e->getMessage());
            return false;
        }
    }
}
