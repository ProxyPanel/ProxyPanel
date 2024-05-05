<?php

namespace App\Providers;

use DB;
use File;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Schema;
use URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->isLocal() && \config('app.debug')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }
        if (File::exists(base_path().'/.env') && Schema::hasTable('config') && DB::table('config')->exists()) {
            $this->app->register(SettingServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();

        // 检测是否强制跳转https
        if (config('session.secure')) {
            URL::forceScheme('https');
        }
    }
}
