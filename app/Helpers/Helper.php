<?php

namespace App\Helpers;

use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use App\Helpers\ErrorHelper;

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
                $value = $settings[$setting] ?? config('mail.' . str_replace('smtp_', '', $setting), config('mail.mailers.smtp.' . str_replace('smtp_', '', $setting)));
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
                'mail.mailers.smtp.host' => $settings['smtp_host'] ?? config('mail.mailers.smtp.host'),
                'mail.mailers.smtp.port' => $settings['smtp_port'] ?? config('mail.mailers.smtp.port'),
                'mail.mailers.smtp.username' => $settings['smtp_username'] ?? config('mail.mailers.smtp.username'),
                'mail.mailers.smtp.password' => $settings['smtp_password'] ?? config('mail.mailers.smtp.password'),
                'mail.mailers.smtp.encryption' => $settings['mail_encryption'] ?? config('mail.mailers.smtp.encryption', 'tls'),
                'mail.from.address' => $settings['from_address'] ?? config('mail.from.address'),
                'mail.from.name' => $settings['from_name'] ?? config('mail.from.name', 'Laravel'),
            ]);
        } catch (\Exception $e) {
            ErrorHelper::handleSilent($e, 'Helper::configureMailSettings');
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
            $value = $settings[$setting] ?? config('mail.' . str_replace('smtp_', '', $setting), config('mail.mailers.smtp.' . str_replace('smtp_', '', $setting)));
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
            ErrorHelper::handleSilent($e, 'Helper::getDateFormat', 'warning');
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
            ErrorHelper::handleSilent($e, 'Helper::getTimeFormat', 'warning');
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
            ErrorHelper::handleSilent($e, 'Helper::getTimezone', 'warning');
        }
        return 'UTC';
    }
}
