<?php

namespace Packages\Content\Src\Enums;

enum AllianceSectionPosition: string
{
    case Hero = 'hero';
    case Overview = 'overview';
    case Standards = 'standards';
    case Map = 'map';
    case JoinForm = 'join_form';

    /** @return array{int,int}|null */
    public function expectedItemsRange(): ?array
    {
        // Standards now stores content as `body` (CKEditor HTML), no longer uses items[].
        return match ($this) {
            self::Hero,
            self::Overview,
            self::Standards,
            self::Map,
            self::JoinForm => null,
        };
    }

    public function defaultSortOrder(): int
    {
        return match ($this) {
            self::Hero      => 10,
            self::Overview  => 20,
            self::Standards => 30,
            self::Map       => 40,
            self::JoinForm  => 50,
        };
    }
}
