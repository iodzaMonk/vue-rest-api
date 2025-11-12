<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CaptchaController extends Controller
{
    public function generate()
    {
        $first = random_int(1, 9);
        $second = random_int(1, 9);
        $operators = ['+', '-'];
        $operator = $operators[array_rand($operators)];

        $answer = $operator === '+' ? $first + $second : $first - $second;

        $token = (string) Str::uuid();
        Cache::put('captcha_' . $token, (string) $answer, now()->addMinutes(5));

        return response()->json([
            'token' => $token,
            'question' => sprintf('What is %d %s %d?', $first, $operator, $second),
        ]);
    }
}