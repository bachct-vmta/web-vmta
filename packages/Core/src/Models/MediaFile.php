<?php

namespace Packages\Core\Src\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * MediaFile Model
 *
 * Represents a media file in the system.
 * Supports multiple storage drivers (local, google).
 */
class MediaFile extends BaseModel
{
    use SoftDeletes;

    protected $table = 'media_files';

    protected $fillable = [
        'name',
        'alt',
        'permalink',
        'size',
        'mine_type',
        'user_id',
        'folder_id',
        'storage_driver',
        'external_id',
        // Trash flow sets this via mass-update (SoftDeletes column kept fillable on purpose).
        'deleted_at',
    ];

    protected array $filterable = ['user_id', 'folder_id', 'mine_type', 'storage_driver'];

    protected array $searchable = ['name', 'permalink'];

    /**
     * Default storage driver
     */
    protected $attributes = [
        'storage_driver' => 'local',
    ];

    /**
     * Get the folder that owns the file.
     */
    public function folder()
    {
        return $this->belongsTo(MediaFolder::class, 'folder_id');
    }

    /**
     * Get the user that uploaded the file.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Check if file is stored locally
     */
    public function isLocal(): bool
    {
        return $this->storage_driver === 'local';
    }

    /**
     * Check if file is stored on Google Drive
     */
    public function isGoogleDrive(): bool
    {
        return $this->storage_driver === 'google';
    }

    /**
     * Scope for local files
     */
    public function scopeLocal($query)
    {
        return $query->where('storage_driver', 'local');
    }

    /**
     * Scope for Google Drive files
     */
    public function scopeGoogleDrive($query)
    {
        return $query->where('storage_driver', 'google');
    }
}
