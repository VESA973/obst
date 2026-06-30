<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Config;

class SiteMailerConfigurator
{
    public static function apply(): void
    {
        $mailer = SiteSetting::getValue('smtp_mailer', config('mail.default', 'log')) ?: config('mail.default', 'log');
        $fromAddress = SiteSetting::getValue('smtp_from_address') ?: config('mail.from.address');
        $fromName = SiteSetting::getValue('smtp_from_name') ?: config('mail.from.name');

        Config::set('mail.default', $mailer);
        Config::set('mail.from.address', $fromAddress);
        Config::set('mail.from.name', $fromName);

        if ($mailer !== 'smtp') {
            return;
        }

        Config::set('mail.mailers.smtp.host', SiteSetting::getValue('smtp_host') ?: config('mail.mailers.smtp.host'));
        Config::set('mail.mailers.smtp.port', (int) (SiteSetting::getValue('smtp_port') ?: config('mail.mailers.smtp.port')));
        Config::set('mail.mailers.smtp.username', SiteSetting::getValue('smtp_username') ?: config('mail.mailers.smtp.username'));
        Config::set('mail.mailers.smtp.password', SiteSetting::getValue('smtp_password') ?: config('mail.mailers.smtp.password'));
        Config::set('mail.mailers.smtp.scheme', self::smtpScheme(SiteSetting::getValue('smtp_encryption')));
    }

    private static function smtpScheme(?string $encryption): ?string
    {
        return match ($encryption) {
            'ssl' => 'smtps',
            'tls' => 'smtp',
            default => null,
        };
    }
}
