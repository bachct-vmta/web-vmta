<?php

namespace Packages\Core\Src\Traits;

trait Filterable
{
    /**
     * Apply filters to the query
     */
    public function scopeFilter($query, array $filters = [])
    {
        foreach ($filters as $field => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if (method_exists($this, 'filter'.ucfirst($field))) {
                $this->{'filter'.ucfirst($field)}($query, $value);
            } elseif (in_array($field, $this->filterable ?? [])) {
                $query->where($field, $value);
            }
        }

        return $query;
    }

    /**
     * Apply search to the query
     */
    public function scopeSearch($query, ?string $search, array $searchable = [])
    {
        if (empty($search)) {
            return $query;
        }

        $searchable = ! empty($searchable) ? $searchable : ($this->searchable ?? []);

        if (empty($searchable)) {
            return $query;
        }

        return $query->where(function ($q) use ($search, $searchable) {
            foreach ($searchable as $field) {
                $q->orWhere($field, 'LIKE', "%{$search}%");
            }
        });
    }

    /**
     * Apply sorting to the query
     */
    public function scopeSortBy($query, ?string $field = null, string $direction = 'asc')
    {
        $field = $field ?? ($this->defaultSortField ?? 'created_at');
        $direction = in_array(strtolower($direction), ['asc', 'desc']) ? $direction : 'asc';

        return $query->orderBy($field, $direction);
    }
}
