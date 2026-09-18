<?php

namespace App\Providers;

use App\Models\AduanMasyarakat;
use App\Models\EnsiklopediaArtikel;
use App\Models\EnsiklopediaKategori;
use App\Models\Pemangkasan;
use App\Models\Taman;
use App\Models\User;
use App\Policies\DashboardPolicy;
use App\Policies\NotificationPolicy;
use App\Support\AdminLayoutData;
use App\Support\HomePageData;
use App\Support\JadwalLayananQuery;
use App\Support\MasukanPanelIndicator;
use App\Support\OperationalAlertService;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        App::setLocale(config('app.locale'));
        Carbon::setLocale(config('app.locale'));

        Gate::define('dashboard.export', fn (User $user) => (new DashboardPolicy)->export($user));
        Gate::define('notifications.viewAny', fn (User $user) => (new NotificationPolicy)->viewAny($user));
        Gate::define('notifications.update', fn (User $user) => (new NotificationPolicy)->update($user));

        foreach ([Taman::class, Pemangkasan::class, AduanMasyarakat::class, EnsiklopediaArtikel::class] as $model) {
            $model::saved(fn () => HomePageData::forgetStatsCache());
            $model::deleted(fn () => HomePageData::forgetStatsCache());
        }

        Pemangkasan::saved(fn () => AdminLayoutData::forgetJadwalTerlambatCount());
        Pemangkasan::deleted(fn () => AdminLayoutData::forgetJadwalTerlambatCount());

        EnsiklopediaKategori::saved(fn () => HomePageData::forgetEnsiklopediaKategorisCache());
        EnsiklopediaKategori::deleted(fn () => HomePageData::forgetEnsiklopediaKategorisCache());

        View::composer('layouts.admin', function ($view) {
            if (auth()->check()) {
                $user = auth()->user();
                $alertData = Cache::remember('admin_operational_alerts', 60, function () {
                    $alertService = app(OperationalAlertService::class);

                    return [
                        'aduanBaruCount' => MasukanPanelIndicator::aduanBaruCount(),
                        'operationalAlerts' => $alertService->active(),
                        'operationalAlertsTotal' => $alertService->totalCount(),
                    ];
                });

                $surveyBaruCount = MasukanPanelIndicator::surveyBaruCount($user->id);
                $masukanBaruCount = ($alertData['aduanBaruCount'] ?? 0) + $surveyBaruCount;

                $notificationsEnabled = Schema::hasTable('notifications');

                $view->with([
                    ...$alertData,
                    'surveyBaruCount' => $surveyBaruCount,
                    'masukanBaruCount' => $masukanBaruCount,
                    'canWrite' => $user->canWrite(),
                    'canDelete' => $user->canDelete(),
                    'canManageUsers' => $user->canManageUsers(),
                    'canImportBulk' => $user->canImportBulk(),
                    'isPengawas' => $user->isPengawas(),
                    'isViewer' => $user->isViewer(),
                    'authUserRole' => $user->roleLabel(),
                    'unreadNotificationsCount' => $notificationsEnabled
                        ? $user->unreadNotifications()->count()
                        : 0,
                    'recentNotifications' => $notificationsEnabled
                        ? $user->unreadNotifications()->latest()->take(5)->get()
                        : collect(),
                    'jadwalTerlambatCount' => AdminLayoutData::jadwalTerlambatCount(),
                ]);
            }
        });
    }
}
