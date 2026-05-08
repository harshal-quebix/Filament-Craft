<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Setting;
use App\Helpers\Helper;
use Filament\Models\Contracts\HasAvatar;

class User extends Authenticatable implements MustVerifyEmail, HasAvatar
{
    use HasFactory, Notifiable, HasRoles, CanResetPassword;

    protected $fillable = [
        'name',
        'email',
        'password',
        'mobile_number',
        'profile_photo',
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_confirmed_at',
        'created_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_enabled' => 'boolean',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($user) {
            // Assign default user role
            $user->assignRole('user');

            // Copy default profile photo
            try {
                $defaultPath = public_path('default-img/profile.png');
                $storedPath = uploadFileFromPath($defaultPath, 'profile-photos');
                if ($storedPath) {
                    $user->update(['profile_photo' => $storedPath]);
                }
            } catch (\Exception $e) {
                // Skip if disk not ready
            }

            // Apply default language and timezone from system settings
            $superAdmin = User::role('admin')->first();
            if ($superAdmin) {
                // Get default language from super admin settings
                $defaultLanguage = Setting::where('key', 'default_language')
                                         ->where('created_by', $superAdmin->id)
                                         ->value('value') ?? 'en';

                // Get default timezone from super admin settings
                $defaultTimezone = Setting::where('key', 'default_timezone')
                                         ->where('created_by', $superAdmin->id)
                                         ->value('value') ?? 'UTC';

                // Create default settings for new user
                Setting::create([
                    'key' => 'default_language',
                    'value' => $defaultLanguage,
                    'created_by' => $user->id,
                ]);

                Setting::create([
                    'key' => 'default_timezone',
                    'value' => $defaultTimezone,
                    'created_by' => $user->id,
                ]);
            }

            // Send verification email when user is created via registration
            if (!$user->hasVerifiedEmail()) {
                $user->sendEmailVerificationNotification();
            }
        });
    }

    public function sendPasswordResetNotification($token)
    {
        try {
            Helper::validateMailSettings();
            Helper::configureMailSettings();
            $this->notify(new \App\Notifications\CustomResetPasswordNotification($token));
        } catch (\Exception $e) {
            // Show notification if email settings are not configured
            if (str_contains($e->getMessage(), 'Please add first your mail credentials')) {
                \Filament\Notifications\Notification::make()
                    ->warning()
                    ->title(__('Email Settings Required'))
                    ->body($e->getMessage())
                    ->persistent()
                    ->send();
            }
            \Log::error('Password reset notification failed: ' . $e->getMessage());
        }
    }

    public function hasVerifiedEmail()
    {
        $emailVerificationEnabled = Setting::where('key', 'email_verification')->value('value');

        if (!$emailVerificationEnabled) {
            return true; // Skip verification if disabled in settings
        }

        return !is_null($this->email_verified_at);
    }

    public function sendEmailVerificationNotification()
    {
        try {
            Helper::validateMailSettings();
            Helper::configureMailSettings();
            $this->notify(new \App\Notifications\CustomVerifyEmail);
        } catch (\Exception $e) {
            // Show notification if email settings are not configured
            if (str_contains($e->getMessage(), 'Please add first your mail credentials')) {
                \Filament\Notifications\Notification::make()
                    ->warning()
                    ->title(__('Email Settings Required'))
                    ->body($e->getMessage())
                    ->persistent()
                    ->send();
            }
            \Log::error('Email verification notification failed: ' . $e->getMessage());
        }
    }

    public function markEmailAsVerified()
    {
        return $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
        ])->save();
    }

    // 2FA Methods - Store secret as plain text for Google2FA compatibility
    public function setTwoFactorSecretAttribute($value)
    {
        $this->attributes['two_factor_secret'] = $value;
    }

    public function getTwoFactorSecretAttribute($value)
    {
        return $value;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        $rawPath = $this->getAttributes()['profile_photo'] ?? null;
        return $rawPath ? getImageUrl($rawPath) : null;
    }

    public function getProfilePhotoAttribute($value)
    {
        if ($value) {
            $url = getImageUrl($value);
            if ($url) {
                return $url;
            }
        }
        return asset('default-img/profile.png');
    }
}
