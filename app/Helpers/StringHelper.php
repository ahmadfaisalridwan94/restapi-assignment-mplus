<?php

namespace App\Helpers;

use App\Models\User;
use Illuminate\Support\Str;

class StringHelper
{
    public static function generateUniqueUsername($name)
    {
        $base = Str::slug($name, '.'); // contoh: ahmad.faisal

        do {
            $random = Str::lower(Str::random(6)); // 6 karakter random
            $username = $base . '-' . $random;
        } while (User::where('username', $username)->exists());

        return $username;
    }
}
