<?php

namespace Modules\Maintenance\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Modules\Maintenance\Models\MaintenanceStage;
use Modules\Maintenance\Models\MaintenanceRequest;

class MaintenanceRequestSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $stages = MaintenanceStage::all();
        $tecnicos = User::role('technician')->get();

        if ($users->isEmpty() || $stages->isEmpty()) {
            $this->command->info('No se pueden crear solicitudes de mantenimiento. Por favor, asegúrese de que existan usuarios y etapas (stages) primero.');
            return;
        }

        $nuevaSolicitudStage = $stages->where('name', 'Nueva Solicitud')->first();
        $enProgresoStage = $stages->where('name', 'En Progreso')->first();

        if (!$nuevaSolicitudStage || !$enProgresoStage) {
            $this->command->info('No se encontraron las etapas requeridas. Saltando seeder de solicitudes.');
            return;
        }

        MaintenanceRequest::firstOrCreate(
            ['title' => 'Proyector del Aula 101 no enciende'],
            [
                'description' => 'El proyector no da señal de video. Se ha revisado el cable HDMI pero el problema persiste.',
                'type' => 'corrective',
                'user_id' => $users->random()->id,
                'technician_id' => $tecnicos->isNotEmpty() ? $tecnicos->random()->id : null,
                'stage_id' => $nuevaSolicitudStage->id,
            ]
        );

        MaintenanceRequest::firstOrCreate(
            ['title' => 'Fallo de red en el laboratorio de computación'],
            [
                'description' => 'Varios equipos del laboratorio no tienen conexión a internet desde esta mañana.',
                'type' => 'corrective',
                'user_id' => $users->random()->id,
                'technician_id' => $tecnicos->isNotEmpty() ? $tecnicos->random()->id : null,
                'stage_id' => $enProgresoStage->id,
            ]
        );

        MaintenanceRequest::firstOrCreate(
            ['title' => 'Aire acondicionado de la biblioteca hace ruido'],
            [
                'description' => 'El equipo de aire acondicionado central de la biblioteca emite un ruido mecánico fuerte y constante.',
                'type' => 'corrective',
                'user_id' => $users->random()->id,
                'technician_id' => null,
                'stage_id' => $nuevaSolicitudStage->id,
            ]
        );
    }
}
