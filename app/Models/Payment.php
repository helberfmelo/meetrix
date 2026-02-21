<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'booking_id',
        'subscription_id',
        'provider',
        'provider_payment_id',
        'provider_intent_id',
        'amount',
        'currency',
        'platform_fee_percent',
        'platform_fee_amount',
        'net_amount',
        'status',
        'paid_at',
        'failed_at',
        'refunded_at',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'platform_fee_percent' => 'decimal:2',
        'platform_fee_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'failed_at' => 'datetime',
        'refunded_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}

