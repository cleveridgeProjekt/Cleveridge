<?php

namespace App\Providers;

use App\Models\Fridge;
use App\Policies\FridgePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Fridge::class => FridgePolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
