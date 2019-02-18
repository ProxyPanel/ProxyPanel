<?php

namespace App\Providers;

use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // 检测是否强制跳转https
        if (env('REDIRECT_HTTPS', false)) {
            \URL::forceScheme('https');
        }

        //\Schema::defaultStringLength(191);

        // 队列失败时抓取异常
        Queue::failing(function (JobFailed $event) {
            \Log::error('[出队列]邮件发送失败：' . json_encode(['connectionName' => $event->connectionName, 'job' => $event->job, 'exception' => $event->exception]));
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment() !== 'production') {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }
    }
}
