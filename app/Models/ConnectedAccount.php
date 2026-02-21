<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConnectedAccount extends Model
{
    protected $fillable = [
        'user_id',
        'provider',
        'provider_account_id',
        'status',
        'charges_enabled',
        'payouts_enabled',
        'details_submitted',
        'capabilities',
        'metadata',
    ];

    protected $casts = [
        'charges_enabled' => 'boolean',
        'payouts_enabled' => 'boolean',
        'details_submitted' => 'boolean',
        'capabilities' => 'array',
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

