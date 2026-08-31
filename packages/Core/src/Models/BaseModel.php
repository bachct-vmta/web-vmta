<?php

namespace Packages\Core\Src\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\Core\Src\Traits\Filterable;

abstract class BaseModel extends Model
{
    use Filterable;

    /**
     * The attributes that aren't mass assignable.
     */
    protected $guarded = ['*'];

    /**
     * Fields that can be filtered
     */
    protected array $filterable = [];

    /**
     * Fields that can be searched
     */
    protected array $searchable = [];

    /**
     * Default sort field
     */
    protected string $defaultSortField = 'created_at';
}
