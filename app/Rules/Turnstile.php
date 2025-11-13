<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;

class Turnstile implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Bypass para desarrollo/local
        if (config('services.turnstile.bypass')) return;

        $secret = config('services.turnstile.secret_key');
        if (!$secret) {
            $fail('Falta configurar Turnstile.');
            return;
        }

        $resp = Http::asForm()->post(
            'https://challenges.cloudflare.com/turnstile/v0/siteverify',
            [
                'secret'   => $secret,
                'response' => $value,
                'remoteip' => request()->ip(),
            ]
        );

        if (!$resp->ok() || !($resp->json('success') === true)) {
            $fail('Validación anti-bot falló. Por favor inténtalo de nuevo.');
        }
    }
}
