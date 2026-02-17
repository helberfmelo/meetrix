<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_id',
        'customer_name',
        'customer_email',
        'start_time',
        'end_time',
        'status',
        'meta',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'meta' => 'array',
    ];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }
}
