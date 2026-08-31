<?php

namespace Packages\Chatbot\Src\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ChatbotSession extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'upstream_conversation_id',
        'message_count',
        'max_messages',
        'ip_address',
        'user_agent',
        'locale',
        'handoff_reason',
        'started_at',
        'last_activity_at',
        'expires_at',
    ];

    protected $casts = [
        'message_count' => 'integer',
        'max_messages' => 'integer',
        'started_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $session) {
            if (empty($session->id)) {
                $session->id = (string) Str::uuid();
            }
        });
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function hasReachedLimit(): bool
    {
        return $this->message_count >= $this->max_messages;
    }

    public function remaining(): int
    {
        return max(0, $this->max_messages - $this->message_count);
    }

    public function isOverflow(): bool
    {
        return $this->handoff_reason === 'overflow';
    }
}
