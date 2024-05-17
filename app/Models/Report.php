<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $fillable = [
        'reporter_id',
        'status',
        'location_id',
        'report_detail_id', 
        'typeOfCategory_id',
    ];

    public function reporter()
    {
        return $this->belongsTo(Reporter::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function reportDetail()
    {
        return $this->belongsTo(ReportDetail::class);
    }

    public function typeofcategory()
    {
        return $this->belongsTo(typeofcategory::class);
    }
}
