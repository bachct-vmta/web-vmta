<?php

namespace Packages\Core\Src\Models;

/**
 * GoogleDriveCredential Model
 *
 * Stores encrypted OAuth credentials for Google Drive integration.
 */
class GoogleDriveCredential extends BaseModel
{
    protected $table = 'google_drive_credentials';

    protected $fillable = [
        'email',
        'access_token_enc',
        'refresh_token_enc',
        'expires_at',
        'folder_id',
        'is_active',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'access_token_enc',
        'refresh_token_enc',
    ];

    protected array $filterable = ['is_active'];

    protected array $searchable = ['email'];

    /**
     * Check if token is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if token expires soon (within 5 minutes)
     */
    public function expiresSoon(): bool
    {
        return $this->expires_at->subMinutes(5)->isPast();
    }

    /**
     * Scope for active credentials
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
