<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Support\RecaptchaVerifier;
use Illuminate\Http\Request;

trait ValidatesCaptcha
{
    protected function validateCaptcha(Request $request): bool
    {
        $token = (string) $request->input('recaptcha_token', $request->input('g-recaptcha-response'));

        if ($token === '') {
            return false;
        }

        return app(RecaptchaVerifier::class)->verify($token, $request->ip());
    }
}