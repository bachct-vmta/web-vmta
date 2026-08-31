<?php

namespace Packages\Catalog\Src\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;
use Packages\Catalog\Src\Models\Translations\ServiceTranslation;
use Packages\Core\Src\Models\BaseModel;

class Service extends BaseModel implements TranslatableContract
{
    use Searchable;
    use SoftDeletes;
    use Translatable;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    protected $fillable = [
        'partner_id',
        'cover_media_id',
        'gallery_media_ids',
        'status',
        'is_featured',
        'price_from',
        'currency',
        'cta_app_url',
        'sort_order',
        'published_at',
    ];

    protected $casts = [
        'gallery_media_ids' => 'array',
        'is_featured' => 'boolean',
        'price_from' => 'decimal:2',
        'sort_order' => 'integer',
        'published_at' => 'datetime',
    ];

    public array $translatedAttributes = [
        'title',
        'slug',
        'excerpt',
        'body',
        'seo_title',
        'seo_description',
        'seo_og_image',
    ];

    public $translationModel = ServiceTranslation::class;

    protected array $searchable = ['translations.title', 'translations.slug'];

    protected array $filterable = ['status', 'is_featured', 'partner_id'];

    public function searchableAs(): string
    {
        return 'services_index';
    }

    public function toSearchableArray(): array
    {
        $this->loadMissing('translations', 'specialties.translations', 'destinations.translations');

        $titles = $this->translations->pluck('title')->filter()->values()->all();
        $excerpts = $this->translations->pluck('excerpt')->filter()->values()->all();
        $bodies = $this->translations->pluck('body')->filter()->map(fn ($b) => strip_tags((string) $b))->values()->all();

        $specialtyNames = $this->specialties->flatMap(fn ($s) => $s->translations->pluck('name'))->filter()->values()->all();
        $destinationNames = $this->destinations->flatMap(fn ($d) => $d->translations->pluck('name'))->filter()->values()->all();

        $titleJoined = implode(' | ', $titles);
        $excerptJoined = implode(' | ', $excerpts);
        $bodyJoined = implode(' ', $bodies);
        $specialtyJoined = implode(' ', $specialtyNames);
        $destinationJoined = implode(' ', $destinationNames);

        // Append ASCII-folded variants so "da nang" matches "Đà Nẵng" with TNTSearch.
        return [
            'id' => $this->id,
            'type' => 'service',
            'title' => $titleJoined.' '.Str::ascii($titleJoined),
            'excerpt' => $excerptJoined.' '.Str::ascii($excerptJoined),
            'body' => $bodyJoined.' '.Str::ascii($bodyJoined),
            'specialties' => $specialtyJoined.' '.Str::ascii($specialtyJoined),
            'destinations' => $destinationJoined.' '.Str::ascii($destinationJoined),
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', self::STATUS_PUBLISHED)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Eager-load relations during scout:import to avoid N+1 in queue jobs.
     */
    protected function makeAllSearchableUsing($query)
    {
        return $query->with(['translations', 'specialties.translations', 'destinations.translations']);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function specialties(): BelongsToMany
    {
        return $this->belongsToMany(Specialty::class, 'service_specialty');
    }

    public function destinations(): BelongsToMany
    {
        return $this->belongsToMany(Destination::class, 'service_destination');
    }

    public function combos(): BelongsToMany
    {
        return $this->belongsToMany(Combo::class, 'combo_service');
    }
}
