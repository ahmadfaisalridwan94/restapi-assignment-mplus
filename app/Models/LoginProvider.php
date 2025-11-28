<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginProvider extends Model
{
    protected $table = 'login_providers';

    protected $fillable = [
        'user_id',
        'provider_name',
        'provider_id',
        'nick_name',
        'email',
        'avatar',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
