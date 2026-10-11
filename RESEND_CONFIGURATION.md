# Resend Email Configuration Guide

This guide explains how to configure your Laravel application to use Resend for email delivery.

## Configuration Steps Completed

### 1. Mail Configuration Updated
The `config/mail.php` file has been updated to use Resend as the default mailer:
- Changed `'default' => env('MAIL_MAILER', 'mailgun')` to `'default' => env('MAIL_MAILER', 'resend')`

### 2. Services Configuration
The `config/services.php` file has been updated with the Resend configuration:
```php
'resend' => [
    'key' => env('RESEND_API_KEY'),
],
```

### 3. Package Installation
The `resend/resend-laravel` package has been installed to provide the necessary Resend functionality.

## Manual Environment Configuration Required

Since the `.env` file is protected, you need to manually update it with the following Resend configuration:

### Update your `.env` file:

Replace the current mail configuration with:

```env
MAIL_MAILER=resend
MAIL_HOST=smtp.resend.com
MAIL_PORT=587
MAIL_USERNAME=resend
MAIL_PASSWORD=your_actual_resend_api_key_here
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="info@nbaunitybar.org"
MAIL_FROM_NAME="${APP_NAME}"

# Resend Configuration
RESEND_API_KEY=your_actual_resend_api_key_here
```

## Getting Your Resend API Key

1. **Sign up for Resend**: Go to [resend.com](https://resend.com) and create an account
2. **Create API Key**: 
   - Navigate to the API Keys section in your Resend dashboard
   - Click "Create API Key"
   - Give it a descriptive name (e.g., "Laravel NBA App")
   - Set permissions to "Full Access"
   - Copy the generated API key
3. **Verify Domain**: 
   - Add your domain (`nbaunitybar.org`) to Resend
   - Follow the DNS verification instructions to set up SPF and DKIM records

## Alternative SMTP Configuration

If you prefer to use Resend via SMTP instead of the API, you can use these settings:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.resend.com
MAIL_PORT=587
MAIL_USERNAME=resend
MAIL_PASSWORD=your_actual_resend_api_key_here
MAIL_ENCRYPTION=tls
```

## Testing the Configuration

After updating your `.env` file with the actual Resend API key:

1. **Clear Configuration Cache**:
   ```bash
   php artisan config:clear
   php artisan config:cache
   ```

2. **Test Email Sending**:
   ```bash
   php artisan tinker
   ```
   Then in tinker:
   ```php
   Mail::raw('Test email from Resend', function ($message) {
       $message->to('your-email@example.com')->subject('Test Email');
   });
   ```

## Security Notes

- Keep your Resend API key secure and never commit it to version control
- Consider using different API keys for different environments (development, staging, production)
- Monitor your Resend usage and set up appropriate rate limits

## Troubleshooting

- **"Class 'Resend' not found" error**: This has been fixed by installing the `resend/resend-laravel` package
- **Authentication errors**: Verify your API key is correct and has proper permissions
- **Domain verification issues**: Ensure DNS records are properly set up
- **Rate limiting**: Check Resend dashboard for usage limits and quotas

## Migration from Mailgun

The existing Mailgun configuration has been preserved in the `.env` file for reference. You can remove it once you've confirmed Resend is working correctly.

