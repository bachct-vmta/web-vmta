<?php

namespace Packages\Inquiry\Src\Enums;

enum InquiryStatus: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case Qualified = 'qualified';
    case Closed = 'closed';
    case Cancelled = 'cancelled';

    /**
     * Allowed forward transitions from current status.
     *
     * @return array<int, self>
     */
    public function allowedNext(): array
    {
        return match ($this) {
            self::New => [self::Contacted, self::Cancelled],
            self::Contacted => [self::Qualified, self::Closed, self::Cancelled],
            self::Qualified => [self::Closed, self::Cancelled],
            self::Closed, self::Cancelled => [],
        };
    }
}
