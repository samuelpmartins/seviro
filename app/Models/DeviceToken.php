<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceToken extends Model
{
    protected $fillable = [
        'token',
        'user_id',
        'session_id',
        'notifiable_type',
        'notifiable_id',
    ];
}
