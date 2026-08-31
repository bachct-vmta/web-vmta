<?php

namespace Packages\Core\Src\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Packages\Core\Src\Traits\Filterable;
use Packages\Core\Src\Traits\HasPermission;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use Filterable, HasFactory, HasPermission, Notifiable;

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'balance',
        'role_id',
        'permissions',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'permissions' => 'array',
        'is_super_user' => 'boolean',
        'is_active' => 'boolean',
        'balance' => 'decimal:2',
    ];

    protected array $searchable = ['name', 'email', 'phone'];

    protected array $filterable = ['role_id', 'is_active'];

    /**
     * Get formatted balance
     */
    public function getFormattedBalanceAttribute(): string
    {
        return number_format($this->balance, 0, ',', '.').' đ';
    }

    /**
     * Add balance to user
     */
    public function addBalance(float $amount): self
    {
        $this->increment('balance', $amount);
        $this->refresh();

        return $this;
    }

    /**
     * Deduct balance from user
     */
    public function deductBalance(float $amount): self
    {
        $this->decrement('balance', $amount);
        $this->refresh();

        return $this;
    }

    /**
     * Check if user has sufficient balance
     */
    public function hasSufficientBalance(float $amount): bool
    {
        return $this->balance >= $amount;
    }

    /**
     * Get user's role
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Get user's roles (collection wrapper for compatibility)
     */
    public function roles()
    {
        return $this->role();
    }
}
