<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class reporter extends Model
{
    use HasFactory;
    
    public function report() {

        return $this->hasMany(report::class);
    }

    protected $fillable = [
        'email',
        'password',
    ];
}
