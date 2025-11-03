<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
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
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('view-admin-links', function ($user) {
            return $user->hasRoleAccess('ADMIN');
        });

        Gate::define('view-workshop-links', function ($user) {
            return $user->hasRoleAccess('WORKSHOP');
        });

        Gate::define('view-warehouse-links', function ($user) {
            return $user->hasRoleAccess('WAREHOUSE');
        });

        Gate::define('view-operator-links', function ($user) {
            return $user->hasRoleAccess('OPERATOR');
        });

        Gate::define('view-pe-links', function ($user) {
            return $user->hasRoleAccess('PE');
        });

        Gate::define('view-store-links', function ($user) {
            return $user->hasRoleAccess('STORE');
        });

        Gate::define('view-ppic-links', function ($user) {
            return $user->hasRoleAccess('PPIC');
        });

        Gate::define('view-maintenance-links', function($user) {
            return $user->hasRoleAccess('MAINTENANCE');
        });

        Gate::define('view-second-process-links', function($user){
            return $user->hasRoleAccess('SECONDPROCESS');
        });

        Gate::define('view-assembly-process-links', function($user){
            return $user->hasRoleAccess('ASSEMBLYPROCESS');
        });
    }
}
