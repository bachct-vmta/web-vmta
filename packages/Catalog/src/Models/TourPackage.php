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
use Packages\Catalog\Src\Models\Translations\TourPackageTranslation;
use Packages\Core\Src\Models\BaseModel;

class TourPackage extends BaseModel implements TranslatableContract
{
    use Searchable;
    use SoftDeletes;
    use Translatable;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    protected $table = 'tour_packages';

    protected $fillable = [
        'partner_id',
        'cover_media_id',
        'gallery_media_ids',
        'duration_days',
        'price_from',
        'currency',
        'cta_app_url',
        'status',
        'is_featured',
        'sort_order',
        'published_at',
    ];

    protected $casts = [
        'gallery_media_ids' => 'array',
        'is_featured' => 'boolean',
        'price_from' => 'decimal:2',
        'duration_days' => 'integer',
        'sort_order' => 'integer',
        'published_at' => 'datetime',
    ];

    public array $translatedAttributes = [
        'title',
        'slug',
        'excerpt',
        'body',
        'itinerary',
        'seo_title',
        'seo_description',
        'seo_og_image',
    ];

    public $translationModel = TourPackageTranslation::class;

    protected array $searchable = ['translations.title', 'translations.slug'];

    protected array $filterable = ['status', 'is_featured', 'partner_id'];

    public function searchableAs(): string
    {
        return 'tour_packages_index';
    }

    public function toSearchableArray(): array
    {
        $this->loadMissing('translations', 'destinations.translations');

        $titles = $this->translations->pluck('title')->filter()->values()->all();
        $excerpts = $this->translations->pluck('excerpt')->filter()->values()->all();
        $bodies = $this->translations->pluck('body')->filter()->map(fn ($b) => strip_tags((string) $b))->values()->all();
        $destinationNames = $this->destinations->flatMap(fn ($d) => $d->translations->pluck('name'))->filter()->values()->all();

        $titleJoined = implode(' | ', $titles);
        $excerptJoined = implode(' | ', $excerpts);
        $bodyJoined = implode(' ', $bodies);
        $destinationJoined = implode(' ', $destinationNames);

        return [
            'id' => $this->id,
            'type' => 'tour_package',
            'title' => $titleJoined.' '.Str::ascii($titleJoined),
            'excerpt' => $excerptJoined.' '.Str::ascii($excerptJoined),
            'body' => $bodyJoined.' '.Str::ascii($bodyJoined),
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

    protected function makeAllSearchableUsing($query)
    {
        return $query->with(['translations', 'destinations.translations']);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function destinations(): BelongsToMany
    {
        return $this->belongsToMany(Destination::class, 'tour_destination');
    }

    public function combos(): BelongsToMany
    {
        return $this->belongsToMany(Combo::class, 'combo_tour');
    }
}
