<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class report extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $fillable = [
        'reporter_id',
        'status',
        'location_id',
        'report_detail_id', 
        'category_id',
        'building',
        'floor',
        'room', 
        'anonymous',
        'image',
        'like',
        'category'
    ];

    public function reporter()
    {
        return $this->belongsTo(reporter::class);
    }

    public function location()
    {
        return $this->belongsTo(location::class);
    }

    public function reportDetail()
    {
        return $this->belongsTo(report_detail::class);
    }

    public function category()
    {
        return $this->belongsTo(category::class);
    }
}
