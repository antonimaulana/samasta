<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TableSort
{
    /**
     * @param  array<string, string|callable(Builder, string): void>  $columns
     * @return array{sort: string, direction: string}
     */
    public static function apply(
        Builder $query,
        Request $request,
        array $columns,
        string $defaultColumn,
        string $defaultDirection = 'asc',
    ): array {
        $sort = (string) $request->input('sort', $defaultColumn);
        $direction = strtolower((string) $request->input('direction', $defaultDirection)) === 'desc' ? 'desc' : 'asc';

        if (! array_key_exists($sort, $columns)) {
            $sort = $defaultColumn;
        }

        $handler = $columns[$sort];

        if (is_callable($handler)) {
            $handler($query, $direction);
        } else {
            $query->orderBy($handler, $direction);
        }

        return [
            'sort' => $sort,
            'direction' => $direction,
        ];
    }
}
