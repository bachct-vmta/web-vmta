<?php

namespace Packages\Catalog\Src\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Packages\Catalog\Src\Enums\SpecialtyLeadStatus;
use Packages\Core\Src\Models\BaseModel;

class SpecialtyLead extends BaseModel
{
    protected $fillable = [
        'specialty_id',
        'name',
        'email',
        'phone',
        'demand',
        'message',
        'status',
        'locale',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'status' => SpecialtyLeadStatus::class,
    ];

    public function specialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class);
    }
}
