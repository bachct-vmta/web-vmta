<?php

namespace Packages\Content\Src\Enums;

enum AboutSectionPosition: string
{
    case Hero = 'hero';
    case WhoAre = 'who_are';
    case CoreValues = 'core_values';
    case HowWorks = 'how_works';
    case Difference = 'difference';
    case WhyChoose = 'why_choose';
    case StartWithUs = 'start_with_us';

    /** @return array{int,int}|null */
    public function expectedItemsRange(): ?array
    {
        return match ($this) {
            self::Hero,
            self::StartWithUs => null,
            // Mission cards + How-it-works steps: at least 1, unbounded upper (admin adds/removes dynamically).
            self::WhoAre,
            self::HowWorks => [1, PHP_INT_MAX],
            // Value cards: fully dynamic — admin can add rows or remove every row (0 allowed).
            self::CoreValues => [0, PHP_INT_MAX],
            self::Difference => [1, PHP_INT_MAX],
            // Reason cards: fully dynamic — admin can add rows or remove every row (0 allowed).
            self::WhyChoose => [0, PHP_INT_MAX],
        };
    }

    public function defaultSortOrder(): int
    {
        return match ($this) {
            self::Hero        => 10,
            self::WhoAre      => 20,
            self::CoreValues  => 30,
            self::HowWorks    => 40,
            self::Difference  => 50,
            self::WhyChoose   => 60,
            self::StartWithUs => 70,
        };
    }
}
