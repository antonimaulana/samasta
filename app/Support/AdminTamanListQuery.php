<?php

namespace App\Support;

use App\Models\Taman;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminTamanListQuery
{
    /**
     * Query dasar Kelola Taman (filter scope, pencarian, alert, status).
     * Sort tabel diterapkan terpisah via {@see TamanTableSort::apply()}.
     *
     * @return Builder<Taman>
     */
    public static function build(Request $request, ?User $user = null): Builder
    {
        $user ??= $request->user();
        $scope = app(OperatorWilayahScope::class);

        return TableSearch::apply(
            $scope->scopeTamans(Taman::query(), $user),
            $request,
            ['nama_taman', 'alamat', 'kategori', 'deskripsi', 'kontraktor', 'konsultan_perencana'],
        )
            ->when(
                $request->input('alert') === 'incomplete',
                fn (Builder $q) => app(OperationalAlertService::class)->filterIncompleteTamans($q),
            )
            ->when(
                in_array($request->input('status_data'), [Taman::STATUS_DATA_LENGKAP, Taman::STATUS_DATA_BELUM_LENGKAP], true),
                fn (Builder $q) => $q->where('status_data', $request->input('status_data')),
            );
    }

    public static function count(Request $request, ?User $user = null): int
    {
        return count(self::orderedIds($request, $user));
    }

    /**
     * ID taman sesuai filter + urutan kolom yang sama dengan tabel Kelola Taman.
     *
     * @return list<int>
     */
    public static function orderedIds(Request $request, ?User $user = null): array
    {
        $query = self::build($request, $user);
        TamanTableSort::apply($query, $request);

        return $query
            ->pluck('tamans.id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    public static function paginated(Request $request, int $perPage = 10): LengthAwarePaginator
    {
        $page = max(1, (int) $request->input('page', 1));
        $ids = self::orderedIds($request);
        $total = count($ids);
        $pageIds = array_slice($ids, ($page - 1) * $perPage, $perPage);

        $items = collect();

        if ($pageIds !== []) {
            $order = array_flip($pageIds);
            $items = Taman::query()
                ->with(['images', 'kelurahan.kecamatan'])
                ->whereIn('id', $pageIds)
                ->get()
                ->sortBy(fn (Taman $taman) => $order[$taman->id] ?? PHP_INT_MAX)
                ->values();
        }

        return new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ],
        );
    }

    /**
     * @return list<string>
     */
    public static function exportQueryKeys(): array
    {
        return ['status_data', 'search', 'alert', 'sort', 'direction'];
    }
}
