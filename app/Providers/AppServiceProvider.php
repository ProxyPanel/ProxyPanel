<?php

namespace App\Providers;

use App\Models\Config;
use App\Models\Node;
use App\Models\Order;
use App\Models\User;
use App\Models\UserGroup;
use App\Observers\ConfigObserver;
use App\Observers\NodeObserver;
use App\Observers\OrderObserver;
use App\Observers\UserGroupObserver;
use App\Observers\UserObserver;
use DB;
use File;
use Illuminate\Support\ServiceProvider;
use Schema;
use URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
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
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        // 检测是否强制跳转https
        if (env('REDIRECT_HTTPS', false)) {
            URL::forceScheme('https');
        }

        Config::observe(ConfigObserver::class);
        Node::observe(NodeObserver::class);
        Order::observe(OrderObserver::class);
        UserGroup::observe(UserGroupObserver::class);
        User::observe(UserObserver::class);
    }
}
