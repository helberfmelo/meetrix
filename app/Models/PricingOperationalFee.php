<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricingOperationalFee extends Model
{
    protected $fillable = [
        'currency',
        'payment_method',
        'fee_percent',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'fee_percent' => 'decimal:2',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];
}

