<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class UltraMsg
{
    public function __construct(
        private ?string $instance = null,
        private ?string $token = null,
    ){
        $this->instance = $this->instance ?: config('services.ultramsg.instance');
        $this->token    = $this->token    ?: config('services.ultramsg.token');
    }

    public static function normalizePhone(?string $raw, ?string $defaultCc = null): ?string
    {
        if (!$raw) return null;
        $digits = preg_replace('/\D+/', '', $raw);
        if (!$digits) return null;

        // ya viene con país
        if (str_starts_with($digits, '502') && strlen($digits) === 11) return '+'.$digits;

        // si es 8 dígitos, anteponer país
        if (strlen($digits) === 8) {
            $cc = $defaultCc ?: (config('services.ultramsg.default_cc') ?: '502');
            return '+'.$cc.$digits;
        }

        // fallback
        if (!str_starts_with($digits, '+')) $digits = '+'.$digits;
        return $digits;
    }

    public function sendText(string $to, string $message): bool
    {
        $url = "https://api.ultramsg.com/{$this->instance}/messages/chat";
        $resp = Http::asForm()->post($url, [
            'token'   => $this->token,
            'to'      => $to,
            'body'    => $message,
            'priority'=> 1,
        ]);

        return $resp->ok() && ($resp->json('sent') || $resp->json('message') === 'sent');
    }
}
