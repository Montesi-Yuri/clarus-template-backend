<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Filterable
{
    public function scopeFilter(Builder $query, array $filters)
    {
        foreach ($this->getFilterableFields() as $field => $columns) {
            $value = data_get($filters, is_numeric($field) ? $columns : $field);

            if ($value === null || $value === '') {
                continue;
            }

            if (is_numeric($field)) {
                $query->where($columns, $value);
                continue;
            }

            if ($field === 'search') {
                $query->where(function ($query) use ($columns, $value) {
                    foreach ($columns as $column) {
                        $query->orWhere($column, 'like', "%{$value}%");
                    }
                });
                continue;
            }

            if (is_array($columns)) {
                $query->whereIn($field, $value);
                continue;
            }

            $query->where($field, $value);
        }

        return $query;
    }

    protected function getFilterableFields(): array
    {
        return $this->filterable ?? [];
    }
} 