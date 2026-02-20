<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'booking_id',
        'source',
        'status',
        'external_reference',
        'amount',
        'currency',
        'coupon_code',
        'description',
        'metadata',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'paid_at' => 'datetime',
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
