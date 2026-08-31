<?php

namespace Packages\Content\Src\Models\Translations;

use Illuminate\Database\Eloquent\Model;

class CategoryTranslation extends Model
{
    public $timestamps = true;

    protected $fillable = ['name', 'slug', 'description'];
}
