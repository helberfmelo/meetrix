<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookEvent extends Model
{
    protected $fillable = [
        'provider',
        'event_id',
        'event_type',
        'status',
        'processed_at',
        'payload',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
        'payload' => 'array',
    ];
}

