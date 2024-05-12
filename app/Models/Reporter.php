<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Reporter extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    protected $fillable = [
        'username', 
        'profile_pic', 
        'email', 
        'role', 
        'password'
    ];
}
