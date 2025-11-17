<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class AppUser extends Authenticatable
{
    use Notifiable;

    protected $table = 'app_users';

    protected $fillable = [
            
        'full_name',
        'email',
        'phone_number',
        'password',
        'otp',
        'is_verified',
        'otp_code',
        'otp_expires_at'
    ];

    protected $hidden = [
    'password',
    'otp_code',
    'created_at',
    'updated_at'
];
}
