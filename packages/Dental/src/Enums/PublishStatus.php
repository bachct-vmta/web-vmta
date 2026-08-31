<?php

namespace Packages\Dental\Src\Enums;

enum PublishStatus: string
{
    case Draft = 'draft';
    case Published = 'published';

    public function label(): string
    {
        return __('dental::dental.status.'.$this->value);
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::Draft->value => self::Draft->label(),
            self::Published->value => self::Published->label(),
        ];
    }
}
