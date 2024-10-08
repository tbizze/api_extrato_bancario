<?php

namespace App\Providers;

use App\Models\{BankAccount, Company, Transaction, User};
use App\Policies\{BankAccountPolicy, CompanyPolicy, TransactionPolicy, UserPolicy};
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
        User::class        => UserPolicy::class,
        Company::class     => CompanyPolicy::class,
        BankAccount::class => BankAccountPolicy::class,
        Transaction::class => TransactionPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('manage-users', function (User $user) {
            return $user->is_superuser;
        });

        Gate::define('manage-companies', function (User $user) {
            return $user->is_superuser;
        });
    }
}
