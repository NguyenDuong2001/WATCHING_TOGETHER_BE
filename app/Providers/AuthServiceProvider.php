<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Models\Actor;
use App\Models\Category;
use App\Models\Director;
use App\Models\Movie;
use App\Models\User;
use App\Policies\ActorPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\DirectorPolicy;
use App\Policies\MoviePolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        Category::class => CategoryPolicy::class,
        Movie::class => MoviePolicy::class,
        User::class => UserPolicy::class,
        Director::class => DirectorPolicy::class,
        Actor::class => ActorPolicy::class
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
