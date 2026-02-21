<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    protected $fillable = [
        'user_id',
        'payment_id',
        'connected_account_id',
        'provider',
        'provider_transfer_id',
        'amount',
        'currency',
        'status',
        'transferred_at',
        'failed_at',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transferred_at' => 'datetime',
        'failed_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function connectedAccount()
    {
        return $this->belongsTo(ConnectedAccount::class);
    }
}

