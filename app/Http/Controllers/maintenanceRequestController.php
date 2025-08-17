<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\MaintenanceStage;
use App\Models\MaintenanceRequest;
use Illuminate\Support\Facades\DB;
use App\Models\Equipment;
use App\Models\User;
use Illuminate\Support\Facades\Redirect;

class MaintenanceRequestController extends Controller
{
    public function index()
    {
        $stages = MaintenanceStage::orderBy('sequence')->get();
        $requests = MaintenanceRequest::with('user')->get();

        return Inertia::render('Maintenance/KanbanBoard', [
            'initialStages' => $stages,
            'initialRequests' => $requests,
        ]);
    }

     public function create()
    {
        return Inertia::render('Maintenance/RequestShow', [
            'maintenanceRequest' => null,
            'users' => User::all(['id', 'name']), // Pasamos todos los usuarios
            'technician' => User::role('technician')->get(['id', 'name']),
            'stages' => MaintenanceStage::orderBy('sequence')->get(['id', 'name']),
            'equipments' => Equipment::all(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:preventive,corrective',
            'user_id' => 'required|exists:users,id',
            'technician_id' => 'nullable|exists:users,id',
            'stage_id' => 'required|exists:maintenance_stages,id',
            'attachments.*' => 'nullable|file|max:10240',
            'equipment_id' => 'required|exists:equipment,id',
        ]);

        $maintenanceRequest = MaintenanceRequest::create($validated);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('attachments', 'public');
                $maintenanceRequest->attachments()->create([
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                ]);
            }
        }
        $maintenanceRequest->save();
        return Redirect::route('mantenimiento.index')->with('success', 'Solicitud creada con éxito.');
    }

    public function show(MaintenanceRequest $maintenanceRequest)
    {
        $maintenanceRequest->load(['stage', 'user', 'technician', 'attachments', 'equipment']);
        
        return Inertia::render('Maintenance/RequestShow', [
            'maintenanceRequest' => $maintenanceRequest,
            'users' => User::all(['id', 'name']), // Pasamos todos los usuarios
            'technician' => User::role('technician')->get(['id', 'name']),
            'stages' => MaintenanceStage::orderBy('sequence')->get(['id', 'name']),
            'equipments' => Equipment::all(['id', 'name']),
        ]);
    }

    public function edit(MaintenanceRequest $maintenanceRequest)
    {
        return Inertia::render('Maintenance/RequestForm', [
            'maintenanceRequest' => $maintenanceRequest
        ]);
    }

    public function update(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:preventive,corrective',
            'user_id' => 'required|exists:users,id',
            'technician_id' => 'nullable|exists:users,id',
            'attachments.*' => 'nullable|file|max:10240',
            'stage_id' => 'required|exists:maintenance_stages,id',
            'equipment_id' => 'required|exists:equipment,id',
        ]);
        
        $maintenanceRequest -> update($validated);
        
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                // Guarda el archivo en 'storage/app/public/attachments'
                $path = $file->store('attachments', 'public');
                
                // Crea el registro en la base de datos
                $maintenanceRequest->attachments()->create([
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                ]);
            }
        }
        
        $maintenanceRequest->save();
        return Redirect::route('mantenimiento.show', $maintenanceRequest->id)
            ->with('success', 'Solicitud actualizada con éxito.');
    }

    public function updateStage(Request $request, MaintenanceRequest $maintenanceRequest)
    {   
        $request->validate([
            'stage' => 'required|exists:maintenance_stages,id'
        ]);

        try {
            // Usamos una transacción para garantizar la integridad de los datos
            DB::transaction(function () use ($request, $maintenanceRequest) {
                
                // Actualizamos la etapa de la solicitud
                $maintenanceRequest->update(['stage_id' => $request->stage]);
                
                // Cargamos el equipo asociado a la solicitud, si existe
                $equipment = $maintenanceRequest->equipment;
                
                // Buscamos la información de la nueva etapa
                $newStage = MaintenanceStage::find($request->stage);

                // Verificamos si la nueva etapa es una etapa final Y si hay un equipo asociado
                if ($equipment && $newStage && $newStage->is_final_stage) {
                    // Actualizamos la fecha del último mantenimiento del equipo a la fecha y hora actual
                    $equipment->update(['last_maintained_at' => now()]);
                }
            });

        } catch (\Exception $e) {
            // Si algo falla, redirigimos con un error
            return Redirect::back()->with('error', 'Ocurrió un error al actualizar la etapa.');
        }

        return Redirect::back()->with('success', 'Etapa actualizada correctamente.');
    }

    public function destroy(MaintenanceRequest $maintenanceRequest)
    {
        $maintenanceRequest->delete();

        return Redirect::route('mantenimiento.index')
            ->with('success', 'Solicitud eliminada con éxito.');
    }
}
