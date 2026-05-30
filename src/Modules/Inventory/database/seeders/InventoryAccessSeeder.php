<?php

namespace Modules\Inventory\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class InventoryAccessSeeder extends Seeder
{
    public function run(): void
    {
        // Crear el permiso (description es NOT NULL en la tabla)
        Permission::firstOrCreate(
            ['name' => 'inventory.access', 'guard_name' => 'web'],
            ['description' => 'Acceso a las funciones del módulo de inventario']
        );

        // Limpiar caché de Spatie para que el cambio sea visible inmediatamente
        app()['cache']->forget('spatie.permission.cache');
    }
}
