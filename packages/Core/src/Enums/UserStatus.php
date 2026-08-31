<?php

namespace Packages\Core\Src\Enums;

/**
 * User account status.
 */
enum UserStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Active => 'Hoạt động',
            self::Inactive => 'Vô hiệu hóa',
        };
    }

    /**
     * Convert from boolean is_active field.
     */
    public static function fromBoolean(bool $isActive): self
    {
        return $isActive ? self::Active : self::Inactive;
    }
}
