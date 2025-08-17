<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\MaintenanceStage;
use App\Models\MaintenanceRequest;

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

        MaintenanceRequest::create([
            'title' => 'Proyector del Aula 101 no enciende',
            'description' => 'El proyector no da señal de video. Se ha revisado el cable HDMI pero el problema persiste.',
            'user_id' => $users->random()->id,
            'technician_id' => $tecnicos->isNotEmpty() ? $tecnicos->random()->id : null,
            'stage_id' => $nuevaSolicitudStage->id,
        ]);

        MaintenanceRequest::create([
            'title' => 'Fallo de red en el laboratorio de computación',
            'description' => 'Varios equipos del laboratorio no tienen conexión a internet desde esta mañana.',
            'user_id' => $users->random()->id,
            'technician_id' => $tecnicos->isNotEmpty() ? $tecnicos->random()->id : null,
            'stage_id' => $enProgresoStage->id,
        ]);
        
        MaintenanceRequest::create([
            'title' => 'Aire acondicionado de la biblioteca hace ruido',
            'description' => 'El equipo de aire acondicionado central de la biblioteca emite un ruido mecánico fuerte y constante.',
            'user_id' => $users->random()->id,
            'technician_id' => null, // Aún sin asignar
            'stage_id' => $nuevaSolicitudStage->id,
        ]);
    }
}
