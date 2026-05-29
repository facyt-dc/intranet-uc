<?php

namespace Modules\Maintenance\Console\Commands;

use Illuminate\Console\Command;
use Modules\Maintenance\Models\Equipment;
use Modules\Maintenance\Models\MaintenanceRequest;
use Modules\Maintenance\Models\MaintenanceStage;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class CreateScheduledMaintenances extends Command
{
    protected $signature = 'maintenance:create-scheduled';
    protected $description = 'Crea solicitudes de mantenimiento preventivo para equipos cuya fecha programada ha llegado.';

    public function handle()
    {
        $this->info('Buscando mantenimientos programados...');
        Log::info('Iniciando la tarea de creación de mantenimientos programados.');

        $firstStage = MaintenanceStage::orderBy('sequence', 'asc')->first();
        $systemUser = User::where('email', 'admini@example.com')->first();

        if (!$firstStage || !$systemUser) {
            $this->error('No se encontró una etapa inicial o el usuario del sistema. Abortando.');
            Log::error('Error de configuración: falta la etapa inicial o el usuario del sistema.');
            return 1;
        }

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

        foreach ($equipmentsToMaintain as $equipment) {
            try {
                DB::transaction(function () use ($equipment, $firstStage, $systemUser) {
                    MaintenanceRequest::create([
                        'title' => 'Mantenimiento Preventivo Programado - ' . $equipment->name,
                        'description' => "Esta es una solicitud de mantenimiento preventivo generada automáticamente para el equipo '{$equipment->name}' (Modelo: {$equipment->model}) basado en su fecha de próximo mantenimiento programada.",
                        'type' => 'preventive',
                        'user_id' => $systemUser->id,
                        'technician_id' => null,
                        'stage_id' => $firstStage->id,
                        'equipment_id' => $equipment->id,
                    ]);

                    if ($equipment->maintenance_frequency && $equipment->maintenance_interval) {
                        $nextDate = now();

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

                        $equipment->update(['next_maintenance_at' => $nextDate]);
                    } else {
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
