<?php

namespace Packages\Core\Src\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Packages\Core\Src\Models\User;

class UserDeleted
{
    use Dispatchable, SerializesModels;

    public function __construct(public User $user) {}
}
