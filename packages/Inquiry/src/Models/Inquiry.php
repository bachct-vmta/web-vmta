<?php

namespace Packages\Inquiry\Src\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Packages\Core\Src\Models\BaseModel;
use Packages\Core\Src\Models\User;
use Packages\Inquiry\Src\Enums\InquiryPriority;
use Packages\Inquiry\Src\Enums\InquirySource;
use Packages\Inquiry\Src\Enums\InquiryStatus;

class Inquiry extends BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'industry',
        'company_name',
        'message',
        'locale',
        'source',
        'source_ref_type',
        'source_ref_id',
        'priority',
        'status',
        'assigned_to',
        'consent_given',
        'ip_address',
        'user_agent',
        'contacted_at',
        'closed_at',
    ];

    protected $casts = [
        'source' => InquirySource::class,
        'priority' => InquiryPriority::class,
        'status' => InquiryStatus::class,
        'consent_given' => 'boolean',
        'contacted_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    protected array $filterable = ['status', 'source', 'priority', 'assigned_to'];

    protected array $searchable = ['name', 'email', 'phone'];

    public function sourceRef(): MorphTo
    {
        return $this->morphTo('source_ref');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(InquiryNote::class)->latest();
    }

    public function scopeForCoordinator(Builder $query, int $userId): Builder
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeUrgentFirst(Builder $query): Builder
    {
        // Surface urgent leads before normal ones, then newest first.
        return $query->orderByRaw("CASE priority WHEN 'urgent' THEN 0 ELSE 1 END")
            ->orderByDesc('created_at');
    }
}
