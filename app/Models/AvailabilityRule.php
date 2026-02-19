<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvailabilityRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'scheduling_page_id',
        'days_of_week',
        'start_time',
        'end_time',
        'breaks',
        'timezone',
    ];

    protected $casts = [
        'days_of_week' => 'array',
        'breaks' => 'array',
    ];

    public function schedulingPage()
    {
        return $this->belongsTo(SchedulingPage::class);
    }
}
