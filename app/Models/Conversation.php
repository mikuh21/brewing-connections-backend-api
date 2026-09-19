<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = ['title'];

    /**
     * Scope conversations whose participants are all currently messageable.
     * This keeps deleted/deactivated users out of existing chats and new-chat lists.
     */
    public function scopeMessageableParticipants($query)
    {
        return $query->whereDoesntHave('users', function ($userQuery) {
            $userQuery->where(function ($query) {
                $query->where('status', '!=', 'active')
                    ->orWhereNull('status')
                    ->orWhereHas('establishment', function ($establishmentQuery) {
                        $establishmentQuery->withTrashed()->whereNotNull('establishments.deleted_at');
                    });
            });
        });
    }

    public function participants()
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'conversation_participants')
            ->withPivot('last_read_at')
            ->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function unreadCount($userId)
    {
        $participant = $this->participants()
            ->where('user_id', $userId)
            ->first();

        if (! $participant) {
            return 0;
        }

        $query = $this->messages()
            ->where('sender_id', '!=', $userId);

        // Prefer participant last_read_at for unread tracking.
        if (!empty($participant->last_read_at)) {
            $query->where('created_at', '>', $participant->last_read_at);
        } else {
            $query->whereNull('read_at');
        }

        return $query->count();
    }
}
