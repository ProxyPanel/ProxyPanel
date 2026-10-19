<?php

namespace App\Providers;

use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Carbon\Carbon;
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
        if ($this->app->isLocal() && config('app.debug')) {
            $this->app->register(IdeHelperServiceProvider::class);
            $this->app->register(\Barryvdh\Debugbar\ServiceProvider::class);

            if (class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
                $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
                $this->app->register(TelescopeServiceProvider::class);
            }
        }

        if (Schema::hasTable('config') && File::exists(base_path().'/.env') && DB::table('config')->exists()) {
            $this->app->register(SettingServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFour();

        // 相对时间（diffForHumans）跟界面语言，localized_date 内部再按实例设置
        Carbon::setLocale(app()->getLocale());

        // 检测是否强制跳转https
        if (config('session.secure')) {
            URL::forceScheme('https');
        }
    }
}
