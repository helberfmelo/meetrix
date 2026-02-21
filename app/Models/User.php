<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use App\Models\AdminActivityLog;
use App\Models\BillingTransaction;

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
        'account_mode',
        'region',
        'currency',
        'platform_fee_percent',
        'preferred_locale',
        'timezone',
        'last_login_at',
        'is_super_admin',
        'is_active',
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
        'last_login_at' => 'datetime',
        'platform_fee_percent' => 'decimal:2',
        'is_super_admin' => 'boolean',
        'is_active' => 'boolean',
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
     * Teams owned by the user.
     */
    public function ownedTeams()
    {
        return $this->hasMany(Team::class, 'user_id');
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

    /**
     * Billing transactions for subscriptions and paid/free bookings.
     */
    public function billingTransactions()
    {
        return $this->hasMany(BillingTransaction::class);
    }

    public function connectedAccounts()
    {
        return $this->hasMany(ConnectedAccount::class);
    }

    public function paymentIntents()
    {
        return $this->hasMany(PaymentIntent::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function transfers()
    {
        return $this->hasMany(Transfer::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function payouts()
    {
        return $this->hasMany(Payout::class);
    }

    /**
     * Admin actions performed by this user.
     */
    public function adminActivities()
    {
        return $this->hasMany(AdminActivityLog::class, 'actor_user_id');
    }

    /**
     * Admin actions that target this user.
     */
    public function adminTargetActivities()
    {
        return $this->hasMany(AdminActivityLog::class, 'target_user_id');
    }
}
