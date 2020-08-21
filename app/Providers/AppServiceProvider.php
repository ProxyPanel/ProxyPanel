<?php

namespace App\Providers;

use App\Models\Node;
use App\Models\Order;
use App\Models\RuleGroup;
use App\Models\User;
use App\Models\UserGroup;
use App\Observers\NodeObserver;
use App\Observers\OrderObserver;
use App\Observers\RuleGroupObserver;
use App\Observers\UserGroupObserver;
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

		Node::observe(NodeObserver::class);
		Order::observe(OrderObserver::class);
		RuleGroup::observe(RuleGroupObserver::class);
		UserGroup::observe(UserGroupObserver::class);
		User::observe(UserObserver::class);
	}
}
