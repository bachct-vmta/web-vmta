<?php

namespace Packages\Core\Src\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Packages\Core\Src\Models\MediaFile;

class MediaDeleted
{
    use Dispatchable, SerializesModels;

    public function __construct(public MediaFile $file) {}
}
