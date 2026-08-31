<?php

namespace Packages\Content\Src\Enums;

enum AchievementSectionPosition: string
{
    case Hero = 'hero';
    case Capabilities = 'capabilities';
    case Assurance = 'assurance';

    /** @return array{int,int}|null */
    public function expectedItemsRange(): ?array
    {
        return match ($this) {
            self::Hero         => [3, 3],
            self::Capabilities => [4, 4],
            self::Assurance    => [4, 4],
        };
    }

    public function defaultSortOrder(): int
    {
        return match ($this) {
            self::Hero         => 10,
            self::Capabilities => 20,
            self::Assurance    => 30,
        };
    }
}
