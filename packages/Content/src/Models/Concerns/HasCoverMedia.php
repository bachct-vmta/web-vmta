<?php

namespace Packages\Content\Src\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Packages\Core\Src\Models\MediaFile;

/**
 * Resolves a model's cover_media_id into a public image URL, mirroring how the
 * media manager stores permalinks: an absolute URL passes through unchanged,
 * otherwise it is served via asset() under the file-manager base path.
 *
 * Shared by Post and Page so both detail layouts render the same hero cover.
 */
trait HasCoverMedia
{
    public function coverMedia(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'cover_media_id');
    }

    public function getFeaturedImageAttribute(): ?string
    {
        $media = $this->relationLoaded('coverMedia')
            ? $this->getRelation('coverMedia')
            : $this->coverMedia;

        if (! $media?->permalink) {
            return null;
        }

        if (str_starts_with($media->permalink, 'http://') || str_starts_with($media->permalink, 'https://')) {
            return $media->permalink;
        }

        $basePath = trim((string) config('file-manager.base_path', '/uploads'), '/');
        $mediaPath = ltrim($media->permalink, '/');

        return asset($basePath.'/'.$mediaPath);
    }
}
