<?php

namespace Packages\Catalog\Src\Models\Translations;

use Illuminate\Database\Eloquent\Model;

class PartnerTranslation extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'name',
        'slug',
        'short_description',
        'description',
        'address',
    ];
}
