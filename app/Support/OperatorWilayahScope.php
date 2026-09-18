<?php



namespace App\Support;



use App\Models\AduanMasyarakat;

use App\Models\PemeliharaanTaman;

use App\Models\Pemangkasan;

use App\Models\Taman;

use App\Models\TimPelaksana;

use App\Models\User;

use Illuminate\Database\Eloquent\Builder;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Collection;

use Illuminate\Validation\ValidationException;



class OperatorWilayahScope

{

    public function __construct(

        private TimPelaksanaResolver $resolver,

    ) {}



    public function restrictsWilayah(?User $user): bool

    {

        if (! $user?->requiresWilayahScope()) {

            return false;

        }



        return ! $this->hasUnrestrictedWilayahAccess($user);

    }



    public function hasOperatorAssignment(?User $user): bool

    {

        if (! $user?->requiresWilayahScope()) {

            return true;

        }



        return $user->akses_semua_wilayah || $this->assignedTeams($user)->isNotEmpty();

    }



    public function hasUnrestrictedWilayahAccess(?User $user): bool

    {

        if (! $user?->requiresWilayahScope()) {

            return true;

        }



        if ($user->akses_semua_wilayah) {

            return true;

        }



        $teams = $this->assignedTeams($user);



        if ($teams->isEmpty()) {

            return false;

        }



        return $teams->every(fn (TimPelaksana $team) => ! $team->memiliki_wilayah_kerja);

    }



    /**

     * @return Collection<int, TimPelaksana>

     */

    public function assignedTeams(?User $user): Collection

    {

        if (! $user) {

            return collect();

        }



        if ($user->relationLoaded('timPelaksanas')) {

            return $user->timPelaksanas

                ->filter(fn (TimPelaksana $team) => $team->aktif)

                ->values();

        }



        return $user->timPelaksanas()

            ->where('aktif', true)

            ->orderBy('urutan')

            ->orderBy('nama')

            ->get();

    }



    /**

     * @return list<string>

     */

    public function teamNames(?User $user): array

    {

        return $this->assignedTeams($user)->pluck('nama')->all();

    }



    /**

     * @return list<string>|null null = semua tim tersedia di form

     */

    public function allowedTimNamesForForm(?User $user): ?array

    {

        if (! $user?->requiresWilayahScope() || $this->hasUnrestrictedWilayahAccess($user)) {

            return null;

        }



        return $this->teamNames($user);

    }



    /**

     * @return list<int>|null null = tidak dibatasi kelurahan

     */

    public function kelurahanIds(?User $user): ?array

    {

        if (! $user?->requiresWilayahScope()) {

            return null;

        }



        if ($user->akses_semua_wilayah) {

            return null;

        }



        $teams = $this->assignedTeams($user);



        if ($teams->isEmpty()) {

            return [];

        }



        if ($teams->every(fn (TimPelaksana $team) => ! $team->memiliki_wilayah_kerja)) {

            return null;

        }



        $ids = [];



        foreach ($teams as $team) {

            if (! $team->memiliki_wilayah_kerja) {

                continue;

            }



            $teamKelurahanIds = $this->resolver->kelurahanIdsForTeamName($team->nama) ?? [];

            $ids = array_merge($ids, $teamKelurahanIds);

        }



        return array_values(array_unique($ids));

    }



    /** @deprecated Use teamNames() */

    public function teamName(?User $user): ?string

    {

        $names = $this->teamNames($user);



        return $names[0] ?? null;

    }



    public function scopeTamans(Builder $query, ?User $user): Builder

    {

        $ids = $this->kelurahanIds($user);



        if ($ids === null) {

            return $query;

        }



        if ($ids === []) {

            return $query->whereRaw('1 = 0');

        }



        return $query->whereIn('kelurahan_id', $ids);

    }



    public function scopePemeliharaan(Builder $query, ?User $user): Builder

    {

        if (! $user?->requiresWilayahScope()) {

            return $query;

        }



        if ($this->hasUnrestrictedWilayahAccess($user)) {

            return $query;

        }



        $teamNames = $this->teamNames($user);



        if ($teamNames === []) {

            return $query->whereRaw('1 = 0');

        }



        return $query->whereIn('tim', $teamNames);

    }



    public function scopePemangkasan(Builder $query, ?User $user): Builder

    {

        if (! $user?->requiresWilayahScope()) {

            return $query;

        }



        if ($this->hasUnrestrictedWilayahAccess($user)) {

            return $this->hasOperatorAssignment($user)

                ? $query

                : $query->whereRaw('1 = 0');

        }



        $teamNames = $this->teamNames($user);

        $kelurahanIds = $this->kelurahanIds($user);



        if ($teamNames === [] || $kelurahanIds === []) {

            return $query->whereRaw('1 = 0');

        }



        return $query->where(function (Builder $q) use ($teamNames, $kelurahanIds) {

            foreach ($teamNames as $teamName) {

                $q->orWhereJsonContains('pelaksana', $teamName);

            }



            $q->orWhereHas('taman', fn (Builder $t) => $t->whereIn('kelurahan_id', $kelurahanIds));

        });

    }



    public function scopeAduan(Builder $query, ?User $user): Builder

    {

        $ids = $this->kelurahanIds($user);



        if ($ids === null) {

            return $query;

        }



        if ($ids === []) {

            return $query->whereRaw('1 = 0');

        }



        return $query->whereHas('taman', fn (Builder $q) => $q->whereIn('kelurahan_id', $ids));

    }



    public function canAccess(?User $user, Model $model): bool

    {

        if (! $user?->requiresWilayahScope()) {

            return true;

        }



        return match (true) {

            $model instanceof Taman => $this->canAccessTaman($user, $model),

            $model instanceof PemeliharaanTaman => $this->canAccessPemeliharaan($user, $model),

            $model instanceof Pemangkasan => $this->canAccessPemangkasan($user, $model),

            $model instanceof AduanMasyarakat => $this->canAccessAduan($user, $model),

            default => true,

        };

    }



    public function canAccessTaman(?User $user, Taman $taman): bool

    {

        $ids = $this->kelurahanIds($user);



        if ($ids === null) {

            return true;

        }



        if ($ids === [] || ! $taman->kelurahan_id) {

            return false;

        }



        return in_array((int) $taman->kelurahan_id, $ids, true);

    }



    public function canAccessPemeliharaan(?User $user, PemeliharaanTaman $pemeliharaan): bool

    {

        if (! $user?->requiresWilayahScope()) {

            return true;

        }



        if ($this->hasUnrestrictedWilayahAccess($user)) {

            return $this->hasOperatorAssignment($user);

        }



        $teamNames = $this->teamNames($user);



        if ($teamNames === []) {

            return false;

        }



        return in_array($pemeliharaan->tim, $teamNames, true);

    }



    public function canAccessPemangkasan(?User $user, Pemangkasan $pemangkasan): bool

    {

        if (! $user?->requiresWilayahScope()) {

            return true;

        }



        if ($this->hasUnrestrictedWilayahAccess($user)) {

            return $this->hasOperatorAssignment($user);

        }



        $teamNames = $this->teamNames($user);

        $kelurahanIds = $this->kelurahanIds($user);



        if ($teamNames === [] || $kelurahanIds === []) {

            return false;

        }



        $pelaksana = $pemangkasan->pelaksana ?? [];



        if (array_intersect($teamNames, $pelaksana) !== []) {

            return true;

        }



        if ($pemangkasan->taman_id && $pemangkasan->relationLoaded('taman')) {

            return $this->canAccessTaman($user, $pemangkasan->taman);

        }



        if ($pemangkasan->taman_id) {

            $kelurahanId = Taman::query()->whereKey($pemangkasan->taman_id)->value('kelurahan_id');



            return $kelurahanId && in_array((int) $kelurahanId, $kelurahanIds, true);

        }



        return false;

    }



    public function canAccessAduan(?User $user, AduanMasyarakat $aduan): bool

    {

        $ids = $this->kelurahanIds($user);



        if ($ids === null) {

            return true;

        }



        if ($ids === [] || ! $aduan->taman_id) {

            return false;

        }



        if ($aduan->relationLoaded('taman') && $aduan->taman) {

            return $this->canAccessTaman($user, $aduan->taman);

        }



        $kelurahanId = Taman::query()->whereKey($aduan->taman_id)->value('kelurahan_id');



        return $kelurahanId && in_array((int) $kelurahanId, $ids, true);

    }



    public function assertKelurahanAllowed(?User $user, ?int $kelurahanId): void

    {

        $ids = $this->kelurahanIds($user);



        if ($ids === null) {

            return;

        }



        if ($ids === [] || ! $kelurahanId || ! in_array((int) $kelurahanId, $ids, true)) {

            throw ValidationException::withMessages([

                'kelurahan_id' => 'Kelurahan taman berada di luar wilayah kerja tim Anda.',

            ]);

        }

    }



    /**

     * @param  array<string, mixed>  $validated

     */

    public function enforceOperatorTim(?User $user, array &$validated): void

    {

        if (! $user?->requiresWilayahScope() || $this->hasUnrestrictedWilayahAccess($user)) {

            return;

        }



        $allowed = $this->teamNames($user);



        if ($allowed === []) {

            throw ValidationException::withMessages([

                'tim' => 'Akun operator belum ditetapkan ke tim pelaksana.',

            ]);

        }



        if (count($allowed) === 1) {

            $validated['tim'] = $allowed[0];



            return;

        }



        if (! in_array($validated['tim'] ?? '', $allowed, true)) {

            throw ValidationException::withMessages([

                'tim' => 'Tim pelaksana di luar tim yang ditetapkan untuk akun Anda.',

            ]);

        }

    }

}

