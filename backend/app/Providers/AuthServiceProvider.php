<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Student;
use App\Policies\StudentPolicy;


class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    protected $policies = [
        // Aquí vinculamos modelos con sus policies
        //Student::class => StudentPolicy::class,
        // Ejemplo futuro:
        // School::class => SchoolPolicy::class,
        // Device::class => DevicePolicy::class,
    ];
    public function register(): void
    {
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
