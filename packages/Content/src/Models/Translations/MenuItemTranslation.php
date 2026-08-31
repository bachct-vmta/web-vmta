<?php

namespace Packages\Content\Src\Models\Translations;

use Illuminate\Database\Eloquent\Model;

class MenuItemTranslation extends Model
{
    public $timestamps = true;

    protected $fillable = ['label', 'url'];
}
