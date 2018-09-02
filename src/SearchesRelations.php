<?php

namespace Titasgailius\SearchRelations;

use Closure;
use Illuminate\Database\Eloquent\Builder;

trait SearchesRelations
{
    /**
     * Determine if this resource is searchable.
     *
     * @return bool
     */
    public static function searchable()
    {
        return parent::searchable() || ! empty(static::$searchRelations);
    }

    /**
     * Apply the search query to the query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected static function applySearch($query, $search)
    {
        return $query->where(function ($query) use ($search) {
            return static::applyRelationSearch(parent::applySearch($query, $search), $search);
        });
    }

    /**
     * Apply the relationship search query to the given query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function applyRelationSearch(Builder $query, string $search): Builder
    {
        foreach ($searchableRelations = static::searchableRelations() as $relation => $columns) {
            $query->orWhereHas($relation, function ($query) use ($columns, $search) {
                $query->where(static::searchQueryApplier($columns, $search));
            });
        }

        return $query;
    }

    /**
     * Get the searchable columns for the resource.
     *
     * @return array
     */
    public static function searchableRelations(): array
    {
        return static::$searchRelations ?? [];
    }

    /**
     * Returns a Closure that applies a search query for a given columns.
     *
     * @param  array $columns
     * @param  string $search
     * @return \Closure
     */
    public static function searchQueryApplier(array $columns, string $search): Closure
    {
        return function ($query) use ($columns, $search) {
            foreach ($columns as $column) {
                $query->orWhere($column, 'LIKE', '%'.$search.'%');
            }
        };
    }
}