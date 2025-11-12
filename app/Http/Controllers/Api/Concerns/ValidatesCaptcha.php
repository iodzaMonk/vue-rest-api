<?php

namespace App\Http\Controllers\Api\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

trait ValidatesCaptcha
{
    protected function validateCaptcha(Request $request): bool
    {
        $token = (string) $request->input('captcha_token');
        $answer = trim((string) $request->input('captcha_answer'));

        if ($token === '' || $answer === '') {
            return false;
        }

        $cacheKey = 'captcha_' . $token;
        $expected = Cache::pull($cacheKey);

        if ($expected === null) {
            return false;
        }

        return strcmp((string) $expected, $answer) === 0;
    }
}
