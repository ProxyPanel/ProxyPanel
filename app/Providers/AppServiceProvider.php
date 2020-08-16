<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\User;
use App\Observers\OrderObserver;
use App\Observers\UserObserver;
use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Illuminate\Support\ServiceProvider;
use URL;

class AppServiceProvider extends ServiceProvider {

	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register() {
		if($this->app->environment() !== 'production'){
			$this->app->register(IdeHelperServiceProvider::class);
		}
	}

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot() {
		// 检测是否强制跳转https
		if(env('REDIRECT_HTTPS', false)){
			URL::forceScheme('https');
		}

		Order::observe(OrderObserver::class);
		User::observe(UserObserver::class);
	}
}
