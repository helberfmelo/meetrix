<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
        'customer_data' => 'array',
        'is_paid' => 'boolean',
        'amount_paid' => 'decimal:2',
    ];

    protected function startAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value, 'UTC') : null,
            set: fn ($value) => $value ? Carbon::parse($value)->utc()->format('Y-m-d H:i:s') : null,
        );
    }

    protected function endAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value, 'UTC') : null,
            set: fn ($value) => $value ? Carbon::parse($value)->utc()->format('Y-m-d H:i:s') : null,
        );
    }

    public function schedulingPage()
    {
        return $this->belongsTo(SchedulingPage::class);
    }

    public function appointmentType()
    {
        return $this->belongsTo(AppointmentType::class);
    }

    public function billingTransactions()
    {
        return $this->hasMany(BillingTransaction::class);
    }
}
