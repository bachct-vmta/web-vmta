<?php

namespace Packages\Core\Src\Models;

/**
 * MediaFolder Model
 *
 * Represents a folder in the media library.
 */
class MediaFolder extends BaseModel
{
    protected $table = 'media_folders';

    protected $fillable = [
        'name',
        'permalink',
        'user_id',
        'parent_id',
        'deleted_at',
    ];

    protected array $filterable = ['user_id', 'parent_id'];

    protected array $searchable = ['name', 'permalink'];

    /**
     * Get the parent folder.
     */
    public function parent()
    {
        return $this->belongsTo(MediaFolder::class, 'parent_id');
    }

    /**
     * Get the child folders.
     */
    public function children()
    {
        return $this->hasMany(MediaFolder::class, 'parent_id');
    }

    /**
     * Get the files in this folder.
     */
    public function files()
    {
        return $this->hasMany(MediaFile::class, 'folder_id');
    }

    /**
     * Get the user that created the folder.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
