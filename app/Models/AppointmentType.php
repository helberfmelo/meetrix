<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppointmentType extends Model
{
    use HasFactory;

    protected $fillable = [
        'scheduling_page_id',
        'name',
        'duration_minutes',
        'price',
        'currency',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function schedulingPage()
    {
        return $this->belongsTo(SchedulingPage::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
