<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentIntent extends Model
{
    protected $fillable = [
        'user_id',
        'booking_id',
        'provider',
        'provider_intent_id',
        'idempotency_key',
        'amount',
        'currency',
        'status',
        'last_error_code',
        'last_error_message',
        'payload',
        'confirmed_at',
        'canceled_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payload' => 'array',
        'confirmed_at' => 'datetime',
        'canceled_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}

