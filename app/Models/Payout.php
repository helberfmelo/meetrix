<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payout extends Model
{
    protected $fillable = [
        'user_id',
        'connected_account_id',
        'provider',
        'provider_payout_id',
        'amount',
        'currency',
        'status',
        'scheduled_at',
        'paid_out_at',
        'failed_at',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'scheduled_at' => 'datetime',
        'paid_out_at' => 'datetime',
        'failed_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function connectedAccount()
    {
        return $this->belongsTo(ConnectedAccount::class);
    }
}

