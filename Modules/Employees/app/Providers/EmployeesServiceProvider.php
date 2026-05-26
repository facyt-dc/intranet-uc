<?php

namespace Modules\Employees\Providers;

use Illuminate\Support\ServiceProvider;

class EmployeesServiceProvider extends ServiceProvider
{
    protected string $name = 'Employees';

    protected string $nameLower = 'employees';

    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path($this->name, 'database/migrations'));
    }

    public function register(): void
    {
        $this->app->register(EventServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);
    }
}
