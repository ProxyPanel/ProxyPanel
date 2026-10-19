<?php

namespace App\Providers;

use App\Models\Node;
use App\Models\Order;
use App\Models\Ticket;
use App\Policies\NodePolicy;
use App\Policies\OrderPolicy;
use App\Policies\TicketPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Node::class => NodePolicy::class,
        Order::class => OrderPolicy::class,
        Ticket::class => TicketPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // 超管短路只给不带模型参数的权限名（后台按路由名判权限）；带模型的归属判定必须按 Policy 实判
        Gate::before(static function ($user, string $ability, array $arguments) {
            if ($arguments !== []) {
                return null;
            }

            return $user->hasRole('Super Admin') ? true : null;
        });
    }
}
