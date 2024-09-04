<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SantanderToken extends Model
{
    use HasFactory;

    protected $fillable = ['type_token', 'access_token', 'expires_in', 'expires_at', 'not-before-policy', 'session_state'];

    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
