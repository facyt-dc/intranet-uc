<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Equipment;
use App\Models\MaintenanceRequest;
use App\Models\MaintenanceStage;
use App\Models\User;
use Illuminate\Support\Facades\DB; // <-- Importar DB para la transacción
use Illuminate\Support\Facades\Log;
use Throwable; // <-- Importar Throwable para capturar cualquier error

class CreateScheduledMaintenances extends Command
{
    protected $signature = 'maintenance:create-scheduled';
    protected $description = 'Crea solicitudes de mantenimiento preventivo para equipos cuya fecha programada ha llegado.';

    public function handle()
    {
        $this->info('Buscando mantenimientos programados...');
        Log::info('Iniciando la tarea de creación de mantenimientos programados.');

        // 1. Encontrar la primera etapa y el usuario del sistema
        $firstStage = MaintenanceStage::orderBy('sequence', 'asc')->first();
        // --- MEJORA 1: Buscar un usuario específico y robusto ---
        $systemUser = User::where('email', 'admini@example.com')->first(); // <-- Cambia a tu email

        if (!$firstStage || !$systemUser) {
            $this.error('No se encontró una etapa inicial o el usuario del sistema. Abortando.');
            Log::error('Error de configuración: falta la etapa inicial o el usuario del sistema.');
            return 1;
        }

        // 2. Encontrar equipos... (sin cambios)
        $equipmentsToMaintain = Equipment::whereNotNull('next_maintenance_at')
                                        ->whereDate('next_maintenance_at', '<=', now())
                                        ->get();

        if ($equipmentsToMaintain->isEmpty()) {
            $this->info('No hay mantenimientos programados para hoy.');
            Log::info('No se encontraron equipos que requieran mantenimiento programado.');
            return 0;
        }

        $this->info("Se encontraron {$equipmentsToMaintain->count()} equipos que requieren mantenimiento.");
        $createdCount = 0;

        // 3. Crear una solicitud para cada equipo
        foreach ($equipmentsToMaintain as $equipment) {
            try {
                // --- MEJORA 2: Usar una transacción por cada equipo ---
                DB::transaction(function () use ($equipment, $firstStage, $systemUser) {
                    MaintenanceRequest::create([
                        'title' => 'Mantenimiento Preventivo Programado - ' . $equipment->name,
                        'description' => "Esta es una solicitud de mantenimiento preventivo generada automáticamente para el equipo '{$equipment->name}' (Modelo: {$equipment->model}) basado en su fecha de próximo mantenimiento programada.",
                        'type' => 'preventive',
                        'user_id' => $systemUser->id, // <-- Asignado al usuario del sistema
                        'technician_id' => null,
                        'stage_id' => $firstStage->id,
                        'equipment_id' => $equipment->id,
                    ]);

                    if ($equipment->maintenance_frequency && $equipment->maintenance_interval) {
                        $nextDate = now(); // Empezamos desde hoy
                        
                        switch ($equipment->maintenance_interval) {
                            case 'days':
                                $nextDate->addDays($equipment->maintenance_frequency);
                                break;
                            case 'months':
                                $nextDate->addMonths($equipment->maintenance_frequency);
                                break;
                            case 'years':
                                $nextDate->addYears($equipment->maintenance_frequency);
                                break;
                        }
                        
                        // Actualizamos con la nueva fecha calculada
                        $equipment->update(['next_maintenance_at' => $nextDate]);
                    } else {
                        // Si no hay configuración de recurrencia, limpiamos la fecha
                        $equipment->update(['next_maintenance_at' => null]);
                    }
                });

                $this->info("Solicitud creada para el equipo: {$equipment->name}");
                Log::info("Solicitud de mantenimiento preventivo creada para el equipo #{$equipment->id}: {$equipment->name}");
                $createdCount++;

            } catch (Throwable $e) {
                $this->error("Falló la creación para el equipo {$equipment->name}: " . $e->getMessage());
                Log::error("Falló la creación de la solicitud para el equipo #{$equipment->id}:", ['exception' => $e]);
            }
        }

        $this->info("Tarea completada. Se crearon {$createdCount} solicitudes.");
        return 0;
    }
}