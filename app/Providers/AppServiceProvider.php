<?php

namespace App\Providers;

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

		//\Schema::defaultStringLength(191);
	}
}
