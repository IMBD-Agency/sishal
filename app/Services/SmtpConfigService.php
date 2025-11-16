<?php

namespace App\Services;

use App\Models\GeneralSetting;
use Illuminate\Support\Facades\Config;

class SmtpConfigService
{
    /**
     * Configure SMTP settings from database
     */
    public static function configureFromSettings()
    {
        $settings = GeneralSetting::first();
        
        if ($settings && $settings->smtp_host) {
            Config::set([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.transport' => 'smtp',
                'mail.mailers.smtp.host' => $settings->smtp_host,
                'mail.mailers.smtp.port' => $settings->smtp_port ?? 587,
                'mail.mailers.smtp.encryption' => $settings->smtp_encryption ?: 'tls',
                'mail.mailers.smtp.username' => $settings->smtp_username,
                'mail.mailers.smtp.password' => $settings->smtp_password,
                'mail.from.address' => $settings->smtp_from_address ?: $settings->smtp_username,
                'mail.from.name' => $settings->smtp_from_name ?: config('app.name'),
            ]);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Get contact email from settings (for owner notifications)
     * Priority: Contact email > SMTP username > fallback
     */
    public static function getContactEmail()
    {
        $settings = GeneralSetting::first();
        
        // First priority: Use contact email from Contact Info tab (for owner notifications)
        if ($settings && $settings->contact_email) {
            return $settings->contact_email;
        }
        
        // Second priority: Use SMTP username (if contact_email not set)
        if ($settings && $settings->smtp_username) {
            return $settings->smtp_username;
        }
        
        // Fallback
        return 'noreply@' . parse_url(config('app.url'), PHP_URL_HOST) ?: 'example.com';
    }
    
    /**
     * Check if SMTP is configured
     */
    public static function isConfigured()
    {
        $settings = GeneralSetting::first();
        return $settings && $settings->smtp_host && $settings->smtp_username && $settings->smtp_password;
    }
}
