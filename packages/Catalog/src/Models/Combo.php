<?php

namespace Packages\Catalog\Src\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;
use Packages\Catalog\Src\Models\Translations\ComboTranslation;
use Packages\Core\Src\Models\BaseModel;

class Combo extends BaseModel implements TranslatableContract
{
    use Searchable;
    use SoftDeletes;
    use Translatable;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    protected $fillable = [
        'cover_media_id',
        'gallery_media_ids',
        'price_from',
        'discount_percent',
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
        'discount_percent' => 'decimal:2',
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

    public $translationModel = ComboTranslation::class;

    protected array $searchable = ['translations.title', 'translations.slug'];

    protected array $filterable = ['status', 'is_featured'];

    public function searchableAs(): string
    {
        return 'combos_index';
    }

    public function toSearchableArray(): array
    {
        $this->loadMissing('translations', 'services.translations', 'tourPackages.translations');

        $titles = $this->translations->pluck('title')->filter()->values()->all();
        $excerpts = $this->translations->pluck('excerpt')->filter()->values()->all();
        $bodies = $this->translations->pluck('body')->filter()->map(fn ($b) => strip_tags((string) $b))->values()->all();
        $serviceTitles = $this->services->flatMap(fn ($s) => $s->translations->pluck('title'))->filter()->values()->all();
        $tourTitles = $this->tourPackages->flatMap(fn ($t) => $t->translations->pluck('title'))->filter()->values()->all();

        $titleJoined = implode(' | ', $titles);
        $excerptJoined = implode(' | ', $excerpts);
        $bodyJoined = implode(' ', $bodies);
        $servicesJoined = implode(' ', $serviceTitles);
        $toursJoined = implode(' ', $tourTitles);

        return [
            'id' => $this->id,
            'type' => 'combo',
            'title' => $titleJoined.' '.Str::ascii($titleJoined),
            'excerpt' => $excerptJoined.' '.Str::ascii($excerptJoined),
            'body' => $bodyJoined.' '.Str::ascii($bodyJoined),
            'services' => $servicesJoined.' '.Str::ascii($servicesJoined),
            'tours' => $toursJoined.' '.Str::ascii($toursJoined),
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
        return $query->with(['translations', 'services.translations', 'tourPackages.translations']);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'combo_service');
    }

    public function tourPackages(): BelongsToMany
    {
        return $this->belongsToMany(TourPackage::class, 'combo_tour');
    }
}
