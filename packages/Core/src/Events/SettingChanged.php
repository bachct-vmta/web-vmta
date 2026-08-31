<?php

namespace Packages\Core\Src\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SettingChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public string $key,
        public mixed $value,
        public mixed $oldValue = null
    ) {}
}
