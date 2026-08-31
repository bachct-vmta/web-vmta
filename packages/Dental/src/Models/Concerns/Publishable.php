<?php

namespace Packages\Dental\Src\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Packages\Dental\Src\Enums\PublishStatus;

trait Publishable
{
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', PublishStatus::Published->value)
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    public function scopeSorted(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function isPublished(): bool
    {
        return $this->status === PublishStatus::Published->value
            && ($this->published_at === null || $this->published_at->lte(now()));
    }
}
