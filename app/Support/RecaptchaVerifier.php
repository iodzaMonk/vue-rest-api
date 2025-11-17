<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;

class RecaptchaVerifier
{
    public function verify(string $token, ?string $ip = null): bool
    {
        $secret = (string) config('services.recaptcha.secret');

        if ($secret === '') {
            return false;
        }

        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $secret,
            'response' => $token,
            'remoteip' => $ip,
        ]);

        if (!$response->successful()) {
            return false;
        }

        $body = $response->json();

        if (($body['success'] ?? false) !== true) {
            return false;
        }

        $minScore = (float) config('services.recaptcha.score', 0.5);

        if (isset($body['score']) && $body['score'] < $minScore) {
            return false;
        }

        return true;
    }
}
