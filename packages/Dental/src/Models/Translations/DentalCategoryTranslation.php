<?php

namespace Packages\Dental\Src\Models\Translations;

use Illuminate\Database\Eloquent\Model;

class DentalCategoryTranslation extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'name',
        'slug',
    ];
}
