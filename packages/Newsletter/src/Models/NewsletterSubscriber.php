<?php

namespace Packages\Newsletter\Src\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\Newsletter\Src\Enums\SubscriberStatus;

class NewsletterSubscriber extends Model
{
    protected $table = 'newsletter_subscribers';

    protected $fillable = [
        'email',
        'locale',
        'status',
        'opt_in_token',
        'subscribed_at',
        'confirmed_at',
        'unsubscribed_at',
        'ip_address',
        'consent_at',
    ];

    protected $casts = [
        'status' => SubscriberStatus::class,
        'subscribed_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
        'consent_at' => 'datetime',
    ];

    public function isConfirmed(): bool
    {
        return $this->status === SubscriberStatus::Confirmed;
    }

    public function isUnsubscribed(): bool
    {
        return $this->status === SubscriberStatus::Unsubscribed;
    }
}
