<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'user_type',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the global administrator record associated with the user.
     */
    public function globalAdministrator(): HasOne
    {
        return $this->hasOne(GlobalAdministrator::class);
    }

    /**
     * Get the administrator record associated with the user.
     */
    public function administrator(): HasOne
    {
        return $this->hasOne(Administrator::class);
    }

    /**
     * Get the reporter record associated with the user.
     */
    public function reporter(): HasOne
    {
        return $this->hasOne(Reporter::class);
    }

    /**
     * Get the notifications for the user.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get the student record associated with the user.
     */
    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    /**
     * Get the staff record associated with the user.
     */
    public function staff(): HasOne
    {
        return $this->hasOne(Staff::class);
    }

    /**
     * Get the security record associated with the user.
     */
    public function security(): HasOne
    {
        return $this->hasOne(Security::class);
    }

    /**
     * Get the stakeholder record associated with the user.
     */
    public function stakeholder(): HasOne
    {
        return $this->hasOne(Stakeholder::class);
    }

    /**
     * Get the payments for the user.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Check if the user is a Marketing administrator.
     */
    public function isMarketingAdmin(): bool
    {
        // Global administrators can access everything
        if ($this->user_type === 'global_administrator') {
            return true;
        }

        // Check if user is an administrator with Marketing role
        if ($this->user_type === 'administrator' && $this->administrator) {
            return $this->administrator->adminRole &&
                   $this->administrator->adminRole->name === 'Marketing';
        }

        return false;
    }

    /**
     * Get the vehicles for the user.
     */
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    /**
     * Get the reports submitted by the user.
     */
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'reported_by');
    }
}
