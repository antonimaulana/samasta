<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TableSearch
{
    /**
     * @param  list<string>  $columns  Column names or "relation.field" for whereHas
     */
    public static function apply(Builder $query, Request $request, array $columns, string $param = 'search'): Builder
    {
        $search = trim((string) $request->input($param, ''));

        if ($search === '') {
            return $query;
        }

        $like = '%'.$search.'%';

        return $query->where(function (Builder $q) use ($columns, $like) {
            foreach ($columns as $column) {
                if (str_contains($column, '.')) {
                    [$relation, $field] = explode('.', $column, 2);
                    $q->orWhereHas($relation, fn (Builder $r) => $r->where($field, 'like', $like));
                } else {
                    $q->orWhere($column, 'like', $like);
                }
            }
        });
    }

    public static function matches(string $haystack, string $search): bool
    {
        $search = mb_strtolower(trim($search));

        if ($search === '') {
            return true;
        }

        return str_contains(mb_strtolower($haystack), $search);
    }
}
