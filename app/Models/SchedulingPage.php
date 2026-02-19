<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SchedulingPage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'team_id',
        'slug',
        'title',
        'intro_text',
        'config',
        'is_active',
        'confirmation_message',
        'redirect_url',
    ];

    protected $casts = [
        'config' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the form fields configuration.
     */
    public function getFormFieldsAttribute()
    {
        return $this->config['form_fields'] ?? [
            ['name' => 'customer_name', 'label' => 'Full Name', 'type' => 'text', 'required' => true],
            ['name' => 'customer_email', 'label' => 'Email Address', 'type' => 'email', 'required' => true],
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function availabilityRules()
    {
        return $this->hasMany(AvailabilityRule::class);
    }

    public function appointmentTypes()
    {
        return $this->hasMany(AppointmentType::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
