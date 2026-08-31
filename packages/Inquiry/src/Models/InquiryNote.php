<?php

namespace Packages\Inquiry\Src\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Packages\Core\Src\Models\BaseModel;
use Packages\Core\Src\Models\User;

class InquiryNote extends BaseModel
{
    protected $fillable = [
        'inquiry_id',
        'user_id',
        'body',
        'status_from',
        'status_to',
    ];

    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(Inquiry::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
