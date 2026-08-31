<?php

namespace Packages\Inquiry\Src\Enums;

enum ContactSectionPosition: string
{
    case Hero = 'hero';
    case Offices = 'offices';

    /**
     * Allowed item-count range for the position's repeatable items[].
     * null = items[] not accepted; [min, max] = required range.
     *
     * @return array{int,int}|null
     */
    public function expectedItemsRange(): ?array
    {
        return match ($this) {
            // Hero is fixed text only — no repeatable items.
            self::Hero => null,
            // At least one office card; admin adds/removes rows freely.
            self::Offices => [1, PHP_INT_MAX],
        };
    }

    public function defaultSortOrder(): int
    {
        return match ($this) {
            self::Hero    => 10,
            self::Offices => 20,
        };
    }
}
