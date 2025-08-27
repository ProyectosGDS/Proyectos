<?php

namespace App\Models\Traits;

use App\Helpers\ParseParamsAdvancedFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

trait SearchableTrait
{

    /**
     * Apply advanced filtering to the query.
     *
     * @param Builder $query
     * @param array $params
     * @return Builder
     */
    public function scopeAdvancedFilter(Builder $query, Request $request): Builder {
        $filters = ParseParamsAdvancedFilter::parser($request);
        $validated = $this->validateFilterParams($filters);

        if (!empty($validated['search'])) {
            $query = $this->applySearch($query, $validated['search']);
        }

        if (!empty($validated['filters'])) {
            $query = $this->applyFilters($query, $validated['filters']);
        }

        if (!empty($validated['sort'])) {
            $query = $this->applySorting($query, $validated['sort']);
        }

        return $query;
    }
    /**
     * Apply search conditions to the query.
     *
     * @param Builder $query
     * @param array $search
     * @return Builder
     */

    protected function applySearch(Builder $query, array $search): Builder {
        $term = $search['q'] ?? '';
        $fields = $search['fields'] ?? [];

        if (empty($term) || empty($fields)) return $query;

        $terms = array_filter(array_map('trim', explode(';', $term)));

        return $query->where(function ($q) use ($fields, $terms) {
            foreach ($terms as $term) {
                $normalized = $this->normalizeTerm($term);

                $q->orWhere(function ($subQuery) use ($fields, $normalized) {
                    foreach ($fields as $field) {
                        $fieldWithAlias = $this->resolveFieldWithAlias($subQuery, $field);
                        Str::contains($field, '.')
                            ? $this->applyRelationSearch($subQuery, $field, $normalized)
                            : $subQuery->orWhere(DB::raw("LOWER({$fieldWithAlias})"), 'LIKE', "%{$normalized}%");
                    }
                });
            }
        });
    }
    /**
     * Apply search conditions for related models.
     *
     * @param Builder $query
     * @param string $fieldPath
     * @param string $term
     */


    protected function applyRelationSearch(Builder $query, string $fieldPath, string $term): void {
        $parts = explode('.', $fieldPath);
        $field = array_pop($parts);
        $relation = implode('.', $parts);

        $query->orWhereHas($relation, function ($q) use ($field, $term) {
            $q->where(DB::raw("LOWER($field)"), 'LIKE', "%{$term}%");
        });
    }

    /**
     * Apply sorting to the query.
     *
     * @param Builder $query
     * @param array $sort
     * @return Builder
     */

    protected function applySorting(Builder $query, array $sort): Builder {
        $field = $sort['field'] ?? null;
        $direction = strtolower($sort['direction'] ?? 'asc');

        if (!$field) return $query;

        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'asc';

        return Str::contains($field, '.')
            ? $this->applyRelationSort($query, $field, $direction)
            : $query->orderBy($field, $direction);
    }

    /**
     * Apply sorting for related models.
     *
     * @param Builder $query
     * @param string $fieldPath
     * @param string $direction
     * @return Builder
     */

    protected function applyRelationSort(Builder $query, string $fieldPath, string $direction): Builder {
        $parts = explode('.', $fieldPath);
        $field = array_pop($parts);
        $model = $query->getModel();
        $baseTable = $model->getTable();
        $select = ["{$baseTable}.*"];
        $previousAlias = $baseTable;

        foreach ($parts as $index => $relationName) {
            $relation = $model->$relationName();

            if (!$relation) {
                throw new \RuntimeException("Relación $relationName no existe.");
            }

            $related = $relation->getRelated();
            $relatedTable = $related->getTable();
            $alias = "{$relatedTable}_rel_{$index}";

            if ($relation instanceof BelongsTo) {
                $query->leftJoin("{$relatedTable} as {$alias}", "{$previousAlias}.{$relation->getForeignKeyName()}", '=', "{$alias}.{$relation->getOwnerKeyName()}");
            } else {
                $query->leftJoin("{$relatedTable} as {$alias}", "{$alias}.{$relation->getForeignKeyName()}", '=', "{$previousAlias}.{$relation->getLocalKeyName()}");
            }

            $model = $related;
            $previousAlias = $alias;
        }

        return $query->select($select)->orderBy("{$previousAlias}.{$field}", $direction);
    }

    /**
     * Apply filters to the query.
     *
     * @param Builder $query
     * @param array $filters
     * @return Builder
     */

    protected function applyFilters(Builder $query, array $filters): Builder {
        return $query->where(function ($q) use ($filters) {
            foreach ($filters as $filter) {
                $this->applySingleFilter($q, $filter);
            }
        });
    }

    /**
     * Apply a single filter to the query.
     *
     * @param Builder $query
     * @param array $filter
     */

    protected function applySingleFilter(Builder $query, array $filter): void {
        $field = $filter['field'] ?? null;

        if (!$field) return;

        $operator = strtolower($filter['operator'] ?? '=');
        $value = $filter['value'] ?? null;
        $boolean = $filter['boolean'] ?? 'and';

        Str::contains($field, '.')
            ? $this->applyRelationFilter($query, $field, $operator, $value, $boolean)
            : $this->applyStandardFilter($query, $field, $operator, $value, $boolean);
    }

    /**
     * Apply standard filter conditions to the query.
     *
     * @param Builder $query
     * @param string $field
     * @param string $operator
     * @param mixed $value
     * @param string $boolean
     */

    protected function applyStandardFilter(Builder $query, string $field, string $operator, $value, string $boolean): void {
        $value = $this->normalizeTerm($value);
        $fieldWithAlias = $this->resolveFieldWithAlias($query, $field);

        match ($operator) {
            'null' => $query->whereNull($field, $boolean),
            'not null' => $query->whereNotNull($field, $boolean),
            'between' => is_array($value) && count($value) === 2 ? $query->whereBetween($field, $value, $boolean) : null,
            'not between' => is_array($value) && count($value) === 2 ? $query->whereNotBetween($field, $value, $boolean) : null,
            'in' => $query->whereIn($field, (array)$value, $boolean),
            'not in' => $query->whereNotIn($field, (array)$value, $boolean),
            'like' => $query->where(DB::raw("LOWER({$fieldWithAlias})"), 'LIKE', "%{$value}%", $boolean),
            'not like' => $query->where(DB::raw("LOWER({$fieldWithAlias})"), 'NOT LIKE', "%{$value}%", $boolean),
            default => $query->where(DB::raw("LOWER($field)"), $operator, $value, $boolean),
        };
    }

    /**
     * Apply filter conditions for related models.
     *
     * @param Builder $query
     * @param string $fieldPath
     * @param string $operator
     * @param mixed $value
     * @param string $boolean
     */

    protected function applyRelationFilter(Builder $query, string $fieldPath, string $operator, $value, string $boolean): void {
        $parts = explode('.', $fieldPath);
        $field = array_pop($parts);
        $relation = implode('.', $parts);

        $query->whereHas($relation, function ($q) use ($field, $operator, $value) {
            $fieldWithAlias = $this->resolveFieldWithAlias($q, $field);
            $this->applyStandardFilter($q, $fieldWithAlias, $operator, $value, 'and');
        }, '=', count($parts), $boolean);
    }

    /**
     * Normalize the search term or filter value.
     *
     * @param mixed $term
     * @return mixed
     */


    protected function normalizeTerm($term) {
        if (is_array($term)) {
            return array_map(fn($t) => mb_strtolower(trim($t), 'UTF-8'), $term);
        }

        return is_string($term) ? mb_strtolower(trim($term), 'UTF-8') : $term;
    }

    /**
     * Validate and normalize filter parameters.
     *
     * @param array $params
     * @return array
     */

    protected function validateFilterParams(array $params): array {
        $validated = [];

        if (!empty($params['search']['q']) || !empty($params['search']['fields'])) {
            $validated['search'] = [
                'q' => $params['search']['q'] ?? '',
                'fields' => array_filter($params['search']['fields'] ?? [])
            ];
        }

        if (!empty($params['filters']) && is_array($params['filters'])) {
            $validated['filters'] = array_values(array_filter(array_map(function ($filter) {
                $field = $filter['field'] ?? null;
                $operator = strtolower($filter['operator'] ?? '');
                $value = $filter['value'] ?? null;
                $boolean = $filter['boolean'] ?? 'and';

                if (!$field || !$operator) return null;

                $validOperators = [
                    '=', '!=', '>', '<', '>=', '<=',
                    'between', 'not between', 'in', 'not in',
                    'null', 'not null', 'like', 'not like'
                ];

                return in_array($operator, $validOperators)
                    ? compact('field', 'operator', 'value', 'boolean')
                    : null;
            }, $params['filters'])));
        }

        if (!empty($params['sort']) && is_array($params['sort'])) {
            $validated['sort'] = [
                'field' => $params['sort']['field'] ?? 1,
                'direction' => $params['sort']['direction'] ?? 'asc',
                'field_first' => $params['sort']['field_first'] ?? 'id',
            ];
        }

        return $validated;
    }

    /**
     * Resolve the field with its alias if necessary.
     *
     * @param Builder $query
     * @param string $field
     * @return string
     */
    protected function resolveFieldWithAlias(Builder $query, string $field): string {
        if (Str::contains($field, '.')) {
            return $field;
        }

        $table = $query->getModel()->getTable();
        return "{$table}.{$field}";
    }
}