<?php

namespace Packages\Catalog\Src\Enums;

enum SpecialtyLeadStatus: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::New => __('catalog::catalog.specialty_lead.status.new'),
            self::Contacted => __('catalog::catalog.specialty_lead.status.contacted'),
            self::Closed => __('catalog::catalog.specialty_lead.status.closed'),
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::New => 'bg-blue-100 text-blue-800',
            self::Contacted => 'bg-amber-100 text-amber-800',
            self::Closed => 'bg-slate-200 text-slate-700',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $c) => [$c->value => $c->label()])
            ->all();
    }
}
