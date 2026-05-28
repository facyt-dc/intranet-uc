<?php

namespace Modules\Thesis\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class ThesisServiceProvider extends ServiceProvider
{
    protected string $name = 'Thesis';

    protected string $nameLower = 'thesis';

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
