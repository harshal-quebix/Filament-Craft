# Filament Craft - Laravel CRUD Generator

<p align="center">
  <img src="public/default-img/light_logo.png" alt="Filament Craft Logo" width="300">
</p>

<p align="center">
  <strong>A powerful Laravel 12 + Filament 4 CRUD Generator with Admin Panel</strong>
</p>

<p align="center">
  <a href="#features">Features</a> •
  <a href="#requirements">Requirements</a> •
  <a href="#installation">Installation</a> •
  <a href="#usage">Usage</a> •
  <a href="#screenshots">Screenshots</a> •
  <a href="#support">Support</a>
</p>

-

## 🚀 Features

### Core Features
- ✅ **Dynamic CRUD Generator** - Generate complete CRUD modules with a few clicks
- ✅ **Modern Admin Panel** - Built with Filament 4.x (SPA mode)
- ✅ **Role-Based Access Control** - Spatie Permission integration
- ✅ **User Management** - Full user lifecycle with roles & permissions
- ✅ **Multi-Language Support** - English, Arabic, French + 15+ installer languages
- ✅ **2FA Authentication** - Google Authenticator integration
- ✅ **Email Verification** - Secure email verification with signed URLs
- ✅ **Soft Deletes** - Recover deleted records
- ✅ **Database Notifications** - Real-time notification system

### Customization
- 🎨 **Theme Customization** - Colors, fonts, logos
- 🖼️ **Brand Settings** - Logo, favicon, dark/light modes
- 📄 **Landing Page Builder** - Customizable hero, features, footer
- 🍪 **Cookie Consent Banner** - GDPR compliant
- 🔍 **SEO Settings** - Meta tags, Open Graph, Twitter Cards
- 📧 **Email Configuration** - SMTP settings per user

### Technical
- ⚡ **Laravel 12.x** - Latest Laravel framework
- 🎨 **Filament 4.x** - Modern admin panel
- 💾 **Multiple Storage** - Local, S3, Wasabi support
- 🐳 **Auto Installer** - Web-based installation wizard
- 📱 **Responsive Design** - Mobile-friendly interface
- 🔒 **Security** - CSRF protection, input validation, password hashing

---

## 📋 Requirements

- PHP >= 8.2
- MySQL 5.7+ / MariaDB 10.3+ / SQLite 3.8.8+
- Composer 2.x
- Node.js 18+ & NPM
- BCMath PHP Extension
- Ctype PHP Extension
- cURL PHP Extension
- DOM PHP Extension
- Fileinfo PHP Extension
- JSON PHP Extension
- Mbstring PHP Extension
- OpenSSL PHP Extension
- PDO PHP Extension
- Tokenizer PHP Extension
- XML PHP Extension

---

## 🛠️ Installation

### Method 1: Auto Installer (Recommended)

1. Upload files to your server
2. Create a database
3. Visit `http://your-domain.com/install`
4. Follow the wizard steps
5. Done! 🎉

### Method 2: Manual Installation

```bash
# Clone or extract files
cd filament-craft

# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Install Node dependencies
npm install
npm run build

# Create environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database in .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=your_database
# DB_USERNAME=your_username
# DB_PASSWORD=your_password

# Run migrations and seeders
php artisan migrate --force
php artisan db:seed --force

# Create storage link
php artisan storage:link

# Set permissions (Linux/Mac)
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Default Credentials

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@example.com | admin@1232 |
| User | user@gmail.com | admin@1232 |

> ⚠️ **Important:** Change default passwords immediately after installation!

---

## 📖 Usage

### Creating a CRUD Module

1. Login to Admin Panel
2. Go to **CRUD Generator**
3. Click **Create**
4. Fill in the details:
   - Module Name
   - Model Name
   - Fields (with types, validation)
   - Relationships (optional)
5. Click **Generate**
6. Your new module appears in the sidebar!

### Managing Users & Roles

1. Go to **Users** or **Roles**
2. Create roles with specific permissions
3. Assign roles to users
4. Control access to generated modules

### Customizing Settings

1. Go to **Settings** cluster
2. Configure:
   - **System Settings** - App name, timezone, date format
   - **Brand Settings** - Logo, favicon, theme color
   - **Email Settings** - SMTP configuration
   - **Landing Page** - Hero, features, footer content
   - **SEO Settings** - Meta tags, social images
   - **Cookie Settings** - GDPR compliance

---

## 🏗️ Project Structure

```
filament-craft/
├── app/
│   ├── Filament/           # Admin panel resources
│   ├── Http/Controllers/   # Web controllers
│   ├── Models/             # Eloquent models
│   ├── Services/           # Business logic
│   └── helpers.php         # Helper functions
├── config/                 # Configuration files
├── database/
│   ├── migrations/         # Database migrations
│   └── seeders/            # Database seeders
├── resources/
│   ├── views/              # Blade templates
│   ├── css/                # Stylesheets
│   └── js/                 # JavaScript
├── routes/
│   └── web.php             # Web routes
├── storage/
│   └── app/uploads/        # Uploaded files
└── tests/                  # PHPUnit tests
```

---

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit
```

### Test Coverage

- ✅ Authentication (Login, Register, Logout)
- ✅ Model CRUD operations
- ✅ Helper functions
- ✅ Routing
- ✅ File upload validation

---

## 🔐 Security

- Passwords hashed with Bcrypt
- CSRF protection on all forms
- Input validation on all endpoints
- Role-based access control
- Email verification with signed URLs
- 2FA with Google Authenticator
- Secure file upload with MIME type validation
- 5MB file size limit by default

---

## 🌐 Multi-Language

Supported Languages:
- 🇺🇸 English (en)
- 🇸🇦 Arabic (ar)
- 🇫🇷 French (fr)

Installer supports 15+ languages.

To add a new language:
1. Create `lang/{locale}/app.php`
2. Add translations
3. Update `config/app.php` available_locales

---

## ☁️ Cloud Storage

Configure S3 or Wasabi in `.env`:

```env
FILESYSTEM_DISK=s3

AWS_ACCESS_KEY_ID=your-key
AWS_SECRET_ACCESS_KEY=your-secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket

# Or Wasabi
WAS_ACCESS_KEY_ID=your-key
WAS_SECRET_ACCESS_KEY=your-secret
WAS_DEFAULT_REGION=us-east-1
WAS_BUCKET=your-bucket
WAS_URL=https://s3.wasabisys.com
```

---

## 🐛 Troubleshooting

### Common Issues

**Issue:** 500 Error after installation
- Check `storage/logs/laravel.log`
- Ensure `storage/` and `bootstrap/cache/` are writable
- Run `php artisan config:cache`

**Issue:** File uploads not working
- Ensure `public/storage` symlink exists
- Run `php artisan storage:link`
- Check disk permissions

**Issue:** Email not sending
- Configure SMTP in Settings → Email
- Or use `MAIL_MAILER=log` for testing

**Issue:** CSS/JS not loading
- Run `npm run build`
- Check `public/build/` exists

---

## 📄 License

This project is licensed under the MIT License.

---

## 🙏 Credits

- [Laravel](https://laravel.com) - The PHP Framework
- [Filament](https://filamentphp.com) - Admin Panel Framework
- [Spatie Laravel Permission](https://github.com/spatie/laravel-permission) - RBAC
- [PragmaRX Google2FA](https://github.com/antonioribeiro/google2fa) - 2FA
- [Laravel Installer](https://github.com/rashidlaasri/LaravelInstaller) - Auto Installer

---

## 📞 Support

For support, please:
1. Check the documentation
2. Search existing issues
3. Create a new support ticket

---

<p align="center">
  Made with ❤️ using Laravel & Filament
</p>
