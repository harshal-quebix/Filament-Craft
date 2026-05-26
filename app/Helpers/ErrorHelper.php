<?php

namespace App\Helpers;

use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class ErrorHelper
{
    /**
     * Handle exceptions with optional user notification and logging.
     *
     * @param \Exception $e The caught exception
     * @param string $context Context/location of the error (e.g., 'UserController@store')
     * @param string|null $userMessage Message to show to user via toast/notification. Null = silent (log only)
     * @param string $logLevel Log level: 'error', 'warning', 'info', 'debug'
     * @param bool $shouldThrow Whether to re-throw the exception after handling
     * @throws \Exception If $shouldThrow is true
     */
    public static function handle(
        \Exception $e,
        string $context,
        ?string $userMessage = null,
        string $logLevel = 'error',
        bool $shouldThrow = false
    ): void {
        // Build log message with context
        $logMessage = "[$context] " . $e->getMessage();
        
        // Log based on level
        match ($logLevel) {
            'error' => Log::error($logMessage, ['exception' => $e]),
            'warning' => Log::warning($logMessage, ['exception' => $e]),
            'info' => Log::info($logMessage, ['exception' => $e]),
            'debug' => Log::debug($logMessage, ['exception' => $e]),
            default => Log::error($logMessage, ['exception' => $e]),
        };

        // Show notification to user if message provided and user is authenticated
        if ($userMessage) {
            if (class_exists(Notification::class)) {
                try {
                    Notification::make()
                        ->danger()
                        ->title(__('Error'))
                        ->body($userMessage)
                        ->send();
                } catch (\Exception $notificationException) {
                    // If notification fails, at least log it
                    Log::error("[ErrorHelper] Failed to show notification: " . $notificationException->getMessage());
                }
            }
        }

        // Re-throw if requested
        if ($shouldThrow) {
            throw $e;
        }
    }

    /**
     * Handle with success notification (for warnings that should inform user)
     */
    public static function handleWarning(
        \Exception $e,
        string $context,
        ?string $userMessage = null
    ): void {
        $logMessage = "[$context] " . $e->getMessage();
        Log::warning($logMessage, ['exception' => $e]);

        if ($userMessage) {
            try {
                Notification::make()
                    ->warning()
                    ->title(__('Warning'))
                    ->body($userMessage)
                    ->send();
            } catch (\Exception $notificationException) {
                Log::error("[ErrorHelper] Failed to show warning notification: " . $notificationException->getMessage());
            }
        }
    }

    /**
     * Handle silently - log only, no user notification
     */
    public static function handleSilent(
        \Exception $e,
        string $context,
        string $logLevel = 'warning'
    ): void {
        $logMessage = "[$context] " . $e->getMessage();
        
        match ($logLevel) {
            'error' => Log::error($logMessage, ['exception' => $e]),
            'warning' => Log::warning($logMessage, ['exception' => $e]),
            'info' => Log::info($logMessage, ['exception' => $e]),
            default => Log::warning($logMessage, ['exception' => $e]),
        };
    }
}
