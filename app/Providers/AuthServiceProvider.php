<?php

namespace App\Providers;

use App\Models\Contract;
use App\Policies\ContractPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * سیاست‌های مرتبط با مدل‌های برنامه.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Contract::class => ContractPolicy::class,
    ];

    /**
     * ثبت هرگونه سرویس احراز هویت.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // مجوزهای سفارشی را اینجا تعریف کنید
        Gate::define('manage-contracts', function ($user) {
            return $user->hasRole('admin') || $user->hasPermission('manage contracts');
        });

        Gate::define('view-contracts', function ($user) {
            return $user->hasRole('admin') || 
                   $user->hasPermission('view contracts') || 
                   $user->medical_center_id !== null;
        });
    }
}
