<?php

namespace Modules\Maintenance\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Modules\Maintenance\Console\Commands\CreateScheduledMaintenances;

class MaintenanceServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Maintenance';

    protected string $nameLower = 'maintenance';

    protected array $commands = [
        CreateScheduledMaintenances::class,
    ];

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function boot(): void
    {
        parent::boot();

        $this->injectUserRelations();
    }

    protected function configureSchedules(Schedule $schedule): void
    {
        $schedule->command('maintenance:create-scheduled')->dailyAt('00:00')->onOneServer();
    }

    private function injectUserRelations(): void
    {
        \App\Models\User::resolveRelationUsing('maintenanceRequests', function ($user) {
            return $user->hasMany(\Modules\Maintenance\Models\MaintenanceRequest::class, 'user_id');
        });

        \App\Models\User::resolveRelationUsing('assignedMaintenanceRequests', function ($user) {
            return $user->hasMany(\Modules\Maintenance\Models\MaintenanceRequest::class, 'technician_id');
        });
    }
}
