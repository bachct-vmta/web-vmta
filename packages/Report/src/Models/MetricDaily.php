<?php

namespace Packages\Report\Src\Models;

use Illuminate\Database\Eloquent\Model;

class MetricDaily extends Model
{
    protected $table = 'metric_daily';

    public $incrementing = false;

    protected $primaryKey = null;

    protected $fillable = [
        'date',
        'metric_key',
        'count',
        'meta',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'count' => 'integer',
        'meta' => 'array',
    ];

    /**
     * Override default Y-m-d H:i:s serialization for storage. SQLite stores TEXT
     * and we need string-compare equality with bare 'YYYY-MM-DD' inputs in BETWEEN clauses.
     */
    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format('Y-m-d');
    }
}
