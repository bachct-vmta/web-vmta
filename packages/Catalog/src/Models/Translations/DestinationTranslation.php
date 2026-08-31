<?php

namespace Packages\Catalog\Src\Models\Translations;

use Illuminate\Database\Eloquent\Model;

class DestinationTranslation extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];
}
