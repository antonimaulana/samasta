<?php

namespace App\Support;

use App\Models\PemeliharaanTaman;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class LapanganPemeliharaanHistory
{
    public const SESSION_KEY = 'lapangan_recent_pemeliharaan_ids';

    public const LIMIT = 5;

    /**
     * @return Collection<int, PemeliharaanTaman>
     */
    public function recentForAccount(Request $request, ?string $tim = null): Collection
    {
        $user = $request->user();

        if ($user instanceof User) {
            return PemeliharaanTaman::query()
                ->with('taman')
                ->where('created_by_user_id', $user->id)
                ->when(filled($tim), fn ($query) => $query->where('tim', $tim))
                ->orderByDesc('created_at')
                ->limit(self::LIMIT)
                ->get();
        }

        $ids = collect($request->session()->get(self::SESSION_KEY, []))
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->take(self::LIMIT)
            ->values()
            ->all();

        if ($ids === []) {
            return new Collection;
        }

        $records = PemeliharaanTaman::query()
            ->with('taman')
            ->when(filled($tim), fn ($query) => $query->where('tim', $tim))
            ->whereIn('id', $ids)
            ->get();

        return $records
            ->sortBy(fn (PemeliharaanTaman $row) => array_search($row->id, $ids, true))
            ->values();
    }

    public function rememberSubmission(Request $request, PemeliharaanTaman $record): void
    {
        if ($request->user()) {
            return;
        }

        $ids = collect($request->session()->get(self::SESSION_KEY, []))
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->prepend($record->id)
            ->unique()
            ->take(self::LIMIT)
            ->values()
            ->all();

        $request->session()->put(self::SESSION_KEY, $ids);
    }
}
