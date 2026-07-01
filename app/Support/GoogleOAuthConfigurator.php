<?php

namespace App\Support;

use App\Models\SiteSetting;

class GoogleOAuthConfigurator
{
    public static function redirectUri(): string
    {
        return rtrim((string) config('app.url'), '/').'/professionnels/google/retour';
    }

    public static function apply(): void
    {
        config([
            'services.google.client_id' => SiteSetting::getValue('google_client_id'),
            'services.google.client_secret' => SiteSetting::getValue('google_client_secret'),
            'services.google.redirect' => self::redirectUri(),
        ]);
    }
}
