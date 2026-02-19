<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'scheduling_page_id',
        'appointment_type_id',
        'start_at',
        'end_at',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_timezone',
        'customer_data',
        'status',
        'stripe_session_id',
        'is_paid',
        'amount_paid',
        'cancellation_reason',
        'meeting_link',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'customer_data' => 'array',
        'is_paid' => 'boolean',
        'amount_paid' => 'decimal:2',
    ];

    public function schedulingPage()
    {
        return $this->belongsTo(SchedulingPage::class);
    }

    public function appointmentType()
    {
        return $this->belongsTo(AppointmentType::class);
    }
}
