<?php

namespace App\Helpers;

class SecurityHelper
{
    public static function generateHash($string): string
    {
        return hash_hmac('sha256', $string, config('app.key'));
    }
}
