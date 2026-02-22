<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricingPlatformCommission extends Model
{
    protected $fillable = [
        'plan_code',
        'currency',
        'payment_method',
        'commission_percent',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'commission_percent' => 'decimal:2',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];
}

