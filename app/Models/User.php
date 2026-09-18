<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';

    /** Peran operasional: input & pemutakhiran data lapangan (slug tetap `operator`). */
    public const ROLE_OPERATOR = 'operator';

    /** Pengawas tim wilayah / armada / nursery — monitoring read-only per tim. */
    public const ROLE_PENGAWAS = 'pengawas';

    /** Pimpinan dinas — ringkasan & evaluasi read-only seluruh kota. */
    public const ROLE_VIEWER = 'viewer';

    public const ROLES = [
        self::ROLE_ADMIN => 'Administrator',
        self::ROLE_OPERATOR => 'Admin',
        self::ROLE_PENGAWAS => 'Pengawas',
        self::ROLE_VIEWER => 'Pimpinan',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'akses_semua_wilayah',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'akses_semua_wilayah' => 'boolean',
        ];
    }

    public function resolvedRole(): string
    {
        $role = $this->role;

        if ($role === null || $role === '' || ! array_key_exists($role, self::ROLES)) {
            return self::ROLE_VIEWER;
        }

        return $role;
    }

    public function isAdmin(): bool
    {
        return $this->resolvedRole() === self::ROLE_ADMIN;
    }

    public function isOperator(): bool
    {
        return $this->resolvedRole() === self::ROLE_OPERATOR;
    }

    public function isPengawas(): bool
    {
        return $this->resolvedRole() === self::ROLE_PENGAWAS;
    }

    public function isViewer(): bool
    {
        return $this->resolvedRole() === self::ROLE_VIEWER;
    }

    /** Admin operasional atau pengawas yang dibatasi per tim pelaksana. */
    public function requiresWilayahScope(): bool
    {
        return $this->isOperator() || $this->isPengawas();
    }

    public function isPimpinan(): bool
    {
        return $this->isViewer();
    }

    public function canWrite(): bool
    {
        return $this->isAdmin() || $this->isOperator();
    }

    public function canDelete(): bool
    {
        return $this->isAdmin();
    }

    public function canManageUsers(): bool
    {
        return $this->isAdmin();
    }

    public function roleLabel(): string
    {
        return self::ROLES[$this->resolvedRole()] ?? ucfirst($this->resolvedRole());
    }

    public function timPelaksanas(): BelongsToMany
    {
        return $this->belongsToMany(TimPelaksana::class, 'tim_pelaksana_user')
            ->withTimestamps()
            ->orderBy('urutan')
            ->orderBy('nama');
    }

    public function wilayahLabel(): ?string
    {
        if ($this->akses_semua_wilayah) {
            return 'Semua wilayah';
        }

        $teams = $this->relationLoaded('timPelaksanas')
            ? $this->timPelaksanas->pluck('nama')
            : $this->timPelaksanas()->pluck('nama');

        if ($teams->isEmpty()) {
            return null;
        }

        return $teams->implode(', ');
    }
}
