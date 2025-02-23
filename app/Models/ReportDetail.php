<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'image',
        'anonymous', 
        'feedback',
    ];
}
