<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use App\Services\DoctorApiService;
use App\Services\FeedbackService;
use App\Services\HospitalApiService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        $this->app->singleton(DoctorApiService::class, function ($app) {
            return new DoctorApiService();
        });
        $this->app->singleton(FeedbackService::class, function ($app) {
            return new FeedbackService();
        });
        $this->app->singleton(HospitalApiService::class, function ($app) {
            return new HospitalApiService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Rate limiter 'api' — membatasi jumlah request per menit per IP.
        // Dipakai oleh middleware throttle:api yang aktif pada seluruh route /api/*,
        // termasuk endpoint internal webhook cache invalidation.
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });
    }
}
