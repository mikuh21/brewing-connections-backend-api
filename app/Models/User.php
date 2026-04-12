<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'image_url',
        'profile_focus_x',
        'profile_focus_y',
        'address',
        'barangay',
        'contact_number',
        'latitude',
        'longitude',
        'email_verified_at',
        'password',
        'remember_token',
        'role',
        'status',
        'deactivated_at',
        'deactivation_notice_seen_at',
        'is_verified_reseller',
    ];
    /**
     * Scope a query to only reseller users.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeResellers($query)
    {
        return $query->where('role', 'reseller');
    }

    /**
     * Scope a query to only verified resellers.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified_reseller', true);
    }

    /**
     * Scope a query to only unverified resellers.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnverified($query)
    {
        return $query->where('is_verified_reseller', false);
    }

    /**
     * Scope a query to only active users.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only deactivated users.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDeactivated($query)
    {
        return $query->where('status', 'deactivated');
    }

    /**
     * Scope a query to users created in the last 30 days.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLastThirtyDays($query)
    {
        return $query->where('created_at', '>=', now()->subDays(30));
    }

    /**
     * Determine if the user is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

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
            'deactivated_at' => 'datetime',
            'deactivation_notice_seen_at' => 'datetime',
            'password' => 'hashed',
            'is_verified_reseller' => 'boolean',
        ];
    }

    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'conversation_participants')
            ->withPivot('last_read_at')
            ->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function establishment()
    {
        return $this->hasOne(Establishment::class, 'owner_id');
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }

    public function coffeeVarieties()
    {
        return $this->belongsToMany(
            CoffeeVariety::class,
            'reseller_varieties',
            'reseller_id',
            'coffee_variety_id'
        )->withPivot('is_primary')->withTimestamps();
    }
}
