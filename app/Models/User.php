<?php

namespace App\Models;

use App\Models\Concerns\NormalizesSupabaseMediaUrls;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, NormalizesSupabaseMediaUrls, Notifiable;

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
    /**
     * Scope users who are currently eligible to appear in Messages.
     * Excludes inactive/deactivated accounts and owners of soft-deleted establishments.
     */
    public function scopeMessageable($query)
    {
        return $query
            ->where('status', 'active')
            ->where(function ($query) {
                // Farm/Cafe owners must still have a live establishment.
                // This also handles permanent establishment deletion, where
                // no deleted establishment row remains to inspect.
                $query->whereNotIn('role', ['farm_owner', 'cafe_owner'])
                    ->orWhereHas('establishment', function ($establishmentQuery) {
                        $establishmentQuery->whereNull('establishments.deleted_at');
                    });
            });
    }

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

    public function unreadMessagesCount(): int
    {
        return (int) Message::query()
            ->join('conversation_participants as recipient_participants', 'recipient_participants.conversation_id', '=', 'messages.conversation_id')
            ->where('recipient_participants.user_id', $this->id)
            ->where('messages.sender_id', '!=', $this->id)
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('users as message_senders')
                    ->whereColumn('message_senders.id', 'messages.sender_id')
                    ->where('message_senders.status', 'active')
                    ->where(function ($query) {
                        $query->whereNotIn('message_senders.role', ['farm_owner', 'cafe_owner'])
                            ->orWhereExists(function ($establishmentQuery) {
                                $establishmentQuery->selectRaw('1')
                                    ->from('establishments')
                                    ->whereColumn('establishments.owner_id', 'message_senders.id')
                                    ->whereNull('establishments.deleted_at');
                            });
                    });
            })
            ->where(function ($query) {
                $query->where(function ($query) {
                    $query->whereNotNull('recipient_participants.last_read_at')
                        ->whereColumn('messages.created_at', '>', 'recipient_participants.last_read_at');
                })->orWhere(function ($query) {
                    $query->whereNull('recipient_participants.last_read_at')
                        ->whereNull('messages.read_at');
                });
            })
            ->count();
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

    public function resellerProducts()
    {
        return $this->hasMany(ResellerProduct::class, 'reseller_id');
    }

    public function getImageUrlAttribute($value): ?string
    {
        return static::normalizeMediaUrl($value);
    }
}
