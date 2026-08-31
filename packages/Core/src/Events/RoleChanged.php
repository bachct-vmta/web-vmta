<?php

namespace Packages\Core\Src\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Packages\Core\Src\Models\Role;

class RoleChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Role $role,
        public string $action = 'updated' // 'created', 'updated', 'deleted'
    ) {}
}
