<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('Test Email') }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0;">{{ __('Test Email') }}</h1>
    </div>

    <div style="background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px;">
        <h2 style="color: #667eea;">{{ __('Email Configuration Test') }}</h2>

        <p>{{ __('Hello!') }}</p>

        <p>{{ __('This is a test email to verify that your email configuration is working correctly.') }}</p>

        <div style="background: white; padding: 20px; border-left: 4px solid #667eea; margin: 20px 0;">
            <p style="margin: 0;"><strong>✓ {{ __('Email sent successfully!') }}</strong></p>
            <p style="margin: 10px 0 0 0; color: #666;">{{ __('Your SMTP settings are configured properly.') }}</p>
        </div>

        <p>{{ __('If you received this email, it means your email settings are working perfectly.') }}</p>

        <hr style="border: none; border-top: 1px solid #ddd; margin: 30px 0;">

        <p style="color: #999; font-size: 12px; text-align: center;">
            {{ __('This is an automated test email. Please do not reply to this message.') }}
        </p>
    </div>
</body>
</html>
