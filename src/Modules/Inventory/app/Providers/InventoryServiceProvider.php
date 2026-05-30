<?php

namespace Modules\Inventory\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

class InventoryServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Inventory';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'inventory';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    // protected array $commands = [];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function boot(): void
    {
        parent::boot();

        $this->injectUserRelations();
    }

    /**
     * Inyecta las relaciones del módulo en el modelo User del core,
     * sin modificar App\Models\User directamente.
     */
    private function injectUserRelations(): void
    {
        \App\Models\User::resolveRelationUsing('inventoryMovements', function ($user) {
            return $user->hasMany(\Modules\Inventory\Models\InventoryMovement::class, 'user_id');
        });
    }

    /**
     * Define module schedules.
     *
     * @param $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }
}
