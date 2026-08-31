<?php

namespace Packages\Content\Src\Enums;

enum HomeSectionPosition: string
{
    case Hero = 'hero';
    case Values = 'values';
    case About = 'about';
    case Solutions = 'solutions';
    case VisionMission = 'vision_mission';
    case Benefits = 'benefits';
    case Technology = 'technology';
    case WhyVN = 'why_vn';

    /**
     * Expected items[] cardinality range [min, max].
     * Returns null when the position does not use items[].
     *
     * @return array{int,int}|null
     */
    public function expectedItemsRange(): ?array
    {
        return match ($this) {
            self::Hero => [4, 10], // marquee — variable count
            self::Values,
            self::About,
            self::Solutions,
            self::VisionMission,
            self::WhyVN => [3, 3],
            self::Benefits,
            self::Technology => [2, 2],
        };
    }

    /**
     * Backwards-compat shim — returns the minimum of the range.
     *
     * @deprecated Use expectedItemsRange() for range-aware logic.
     */
    public function expectedItemsCount(): ?int
    {
        $range = $this->expectedItemsRange();

        return $range !== null ? $range[0] : null;
    }

    /**
     * Default sort_order per position (admin readonly).
     */
    public function defaultSortOrder(): int
    {
        return match ($this) {
            self::Hero => 10,
            self::Values => 20,
            self::About => 30,
            self::Solutions => 40,
            self::VisionMission => 50,
            self::Benefits => 60,
            self::Technology => 70,
            self::WhyVN => 80,
        };
    }
}
