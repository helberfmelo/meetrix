<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'onboarding_completed_at',
        'subscription_tier',
        'billing_cycle',
        'stripe_id',
        'trial_ends_at',
        'subscription_ends_at',
        'country_code',
        'is_super_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'onboarding_completed_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->hasOne(Tenant::class);
    }
    
    /**
     * Get all scheduling pages for the user.
     */
    public function schedulingPages()
    {
        return $this->hasMany(SchedulingPage::class);
    }

    /**
     * The teams that the user belongs to.
     */
    public function teams()
    {
        return $this->belongsToMany(Team::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get the user's integrations (Google, Outlook, etc).
     */
    public function integrations()
    {
        return $this->hasMany(Integration::class);
    }

    /**
     * Get the user's meeting polls.
     */
    public function polls()
    {
        return $this->hasMany(Poll::class);
    }
}
