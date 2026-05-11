<?php

namespace App\Helpers;

use App\Models\Setting;

class Helper
{
    public static function configureMailSettings(): void
    {
        try {
            $userId = auth()->id();
            if ($userId) {
                $settings = Setting::whereIn('key', [
                    'mail_driver', 'smtp_host', 'smtp_port', 'smtp_username', 'smtp_password',
                    'mail_encryption', 'from_address', 'from_name'
                ])->where('created_by', $userId)->pluck('value', 'key');
            } else {
                // Fallback to global settings if no user is authenticated
                $settings = Setting::whereIn('key', [
                    'mail_driver', 'smtp_host', 'smtp_port', 'smtp_username', 'smtp_password',
                    'mail_encryption', 'from_address', 'from_name'
                ])->pluck('value', 'key');
            }

            // Check if required mail settings are configured
            $requiredSettings = ['smtp_host', 'smtp_port', 'smtp_username', 'smtp_password', 'from_address'];
            $missingSettings = [];

            foreach ($requiredSettings as $setting) {
                $value = $settings[$setting] ?? env(strtoupper('MAIL_' . str_replace('smtp_', '', $setting)));
                if (empty($value)) {
                    $missingSettings[] = $setting;
                }
            }

            if (!empty($missingSettings)) {
                // Use array driver to prevent actual email sending
                config(['mail.default' => 'array']);
                return;
            }
            config([
                'mail.default' => $settings['mail_driver'] ?? 'smtp',
                'mail.mailers.smtp.host' => $settings['smtp_host'] ?? env('MAIL_HOST'),
                'mail.mailers.smtp.port' => $settings['smtp_port'] ?? env('MAIL_PORT'),
                'mail.mailers.smtp.username' => $settings['smtp_username'] ?? env('MAIL_USERNAME'),
                'mail.mailers.smtp.password' => $settings['smtp_password'] ?? env('MAIL_PASSWORD'),
                'mail.mailers.smtp.encryption' => $settings['mail_encryption'] ?? env('MAIL_ENCRYPTION', 'tls'),
                'mail.from.address' => $settings['from_address'] ?? env('MAIL_FROM_ADDRESS'),
                'mail.from.name' => $settings['from_name'] ?? env('MAIL_FROM_NAME', 'Laravel'),
            ]);
        } catch (\Exception $e) {
            \Log::error('Mail settings configuration failed: ' . $e->getMessage());
            // Fallback to array driver to prevent errors
            config(['mail.default' => 'array']);
        }
    }

    public static function validateMailSettings(): void
    {
        $userId = auth()->id();
        if ($userId) {
            $settings = Setting::whereIn('key', [
                'smtp_host', 'smtp_port', 'smtp_username', 'smtp_password',
                'mail_encryption', 'from_address', 'from_name'
            ])->where('created_by', $userId)->pluck('value', 'key');
        } else {
            $settings = Setting::whereIn('key', [
                'smtp_host', 'smtp_port', 'smtp_username', 'smtp_password',
                'mail_encryption', 'from_address', 'from_name'
            ])->pluck('value', 'key');
        }

        $requiredSettings = ['smtp_host', 'smtp_port', 'smtp_username', 'smtp_password', 'from_address'];

        foreach ($requiredSettings as $setting) {
            $value = $settings[$setting] ?? env(strtoupper('MAIL_' . str_replace('smtp_', '', $setting)));
            if (empty($value)) {
                throw new \Exception(__('Please add first your mail credentials in Settings to email verify.'));
            }
        }
    }

    public static function getDateFormat(): string
    {
        try {
            $userId = auth()->id();
            if ($userId) {
                $setting = Setting::where('key', 'date_format')
                                 ->where('created_by', $userId)
                                 ->first();
                return $setting?->value ?? 'Y-m-d';
            }
        } catch (\Exception $e) {
            // Handle any database errors gracefully
        }
        return 'Y-m-d';
    }

    public static function getTimeFormat(): string
    {
        try {
            $userId = auth()->id();
            if ($userId) {
                $setting = Setting::where('key', 'time_format')
                                 ->where('created_by', $userId)
                                 ->first();
                return $setting?->value ?? 'H:i';
            }
        } catch (\Exception $e) {
            // Handle any database errors gracefully
        }
        return 'H:i';
    }

    public static function getDateTimeFormat(): string
    {
        return self::getDateFormat() . ' ' . self::getTimeFormat();
    }

    public static function getTimezone(): string
    {
        try {
            $userId = auth()->id();
            if ($userId) {
                $setting = Setting::where('key', 'default_timezone')
                                 ->where('created_by', $userId)
                                 ->first();
                return $setting?->value ?? 'UTC';
            }
        } catch (\Exception $e) {
            // Handle any database errors gracefully
        }
        return 'UTC';
    }
}
