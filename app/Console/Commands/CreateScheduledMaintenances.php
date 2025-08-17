<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Equipment;
use App\Models\MaintenanceRequest;
use App\Models\MaintenanceStage;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class CreateScheduledMaintenances extends Command
{
    protected $signature = 'maintenance:create-scheduled';
    protected $description = 'Crea solicitudes de mantenimiento preventivo para equipos cuya fecha programada ha llegado.';

    public function handle()
    {
        $this->info('Buscando mantenimientos programados...');
        Log::info('Iniciando la tarea de creación de mantenimientos programados.');

        // 1. Encontrar la primera etapa del flujo de trabajo (la de secuencia más baja)
        $firstStage = MaintenanceStage::orderBy('sequence', 'asc')->first();

        if (!$firstStage) {
            $this->error('No se encontró ninguna etapa de mantenimiento. Abortando.');
            Log::error('No hay etapas de mantenimiento definidas para asignar las nuevas solicitudes.');
            return 1; // Terminar con error
        }

        // 2. Encontrar equipos cuya fecha de próximo mantenimiento es hoy o ya pasó
        $equipmentsToMaintain = Equipment::whereNotNull('next_maintenance_at')
                                        ->whereDate('next_maintenance_at', '<=', now())
                                        ->get();

        if ($equipmentsToMaintain->isEmpty()) {
            $this->info('No hay mantenimientos programados para hoy.');
            Log::info('No se encontraron equipos que requieran mantenimiento programado.');
            return 0; // Terminar con éxito
        }

        $this->info("Se encontraron {$equipmentsToMaintain->count()} equipos que requieren mantenimiento.");

        // 3. Crear una solicitud para cada equipo
        foreach ($equipmentsToMaintain as $equipment) {
            MaintenanceRequest::create([
                'title' => 'Mantenimiento Preventivo Programado - ' . $equipment->name,
                'description' => "Esta es una solicitud de mantenimiento preventivo generada automáticamente para el equipo '{$equipment->name}' (Modelo: {$equipment->model}) basado en su fecha de próximo mantenimiento programada.",
                'type' => 'preventive',
                'user_id' => User::whereHas('roles', fn($q) => $q->where('name', 'admin'))->first()->id, // Asigna al primer admin
                'technician_id' => null, // Sin asignar inicialmente
                'stage_id' => $firstStage->id,
                'equipment_id' => $equipment->id,
            ]);

            // 4. Limpiar la fecha de próximo mantenimiento para evitar duplicados
            $equipment->update(['next_maintenance_at' => null]);

            $this->info("Solicitud creada para el equipo: {$equipment->name}");
            Log::info("Solicitud de mantenimiento preventivo creada para el equipo #{$equipment->id}: {$equipment->name}");
        }

        $this->info('Tarea completada con éxito.');
        return 0;
    }
}