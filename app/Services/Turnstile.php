<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class Turnstile
{
    public static function validate(?string $token, ?string $ip = null): bool
    {
        if (!config('services.turnstile.enabled')) {
            return true; // desactivado en .env
        }

        $secret = (string) config('services.turnstile.secret');
        if (!$secret || !$token) return false;

        $resp = Http::asForm()->post(
            'https://challenges.cloudflare.com/turnstile/v0/siteverify',
            [
                'secret'   => $secret,
                'response' => $token,
                'remoteip' => $ip,
            ]
        );

        return (bool)($resp->json('success') ?? false);
    }
}
