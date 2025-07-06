<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Config;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [];

    public function boot(): void
    {
        $this->registerPolicies();

        VerifyEmail::createUrlUsing(function ($notifiable) {
            $frontendUrl = Config::get('app.frontend_url', 'http://localhost:3000');

            $temporarySignedURL = URL::temporarySignedRoute(
                'verification.verify', now()->addMinutes(60), [
                    'id' => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                ]
            );

            $query = parse_url($temporarySignedURL, PHP_URL_QUERY);

            return "{$frontendUrl}/email-verification?id={$notifiable->getKey()}&hash=" . sha1($notifiable->getEmailForVerification()) . "&{$query}";
        });
    }
}
