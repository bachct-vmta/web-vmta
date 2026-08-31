<?php

namespace Packages\Inquiry\Src\Models\Translations;

use Illuminate\Database\Eloquent\Model;

class ContactSectionTranslation extends Model
{
    public $timestamps = true;

    protected $fillable = ['title', 'subtitle', 'body', 'extra', 'items'];

    protected $casts = [
        'items' => 'array',
    ];
}
