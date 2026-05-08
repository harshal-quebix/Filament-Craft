<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\EmailVerification\EmailVerificationPrompt;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use App\Helpers\Helper;

class CustomEmailVerificationPrompt extends EmailVerificationPrompt
{
    public function resendNotificationAction(): Action
    {
        return Action::make('resendNotification')
            ->label(__('Resend verification email'))
            ->color('gray')
            ->action(function () {
                try {
                    Helper::configureMailSettings();
                    auth()->user()->sendEmailVerificationNotification();

                    Notification::make()
                        ->success()
                        ->title(__('Verification email sent!'))
                        ->body(__('A new verification email has been sent to your email address.'))
                        ->send();
                } catch (\Exception $e) {
                    Notification::make()
                        ->danger()
                        ->title(__('Error!'))
                        ->body(__('Failed to send verification email: ' . $e->getMessage()))
                        ->send();
                }
            });
    }

    protected function getResendNotificationAction(): Action
    {
        return $this->resendNotificationAction();
    }
}