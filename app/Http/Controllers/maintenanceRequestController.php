<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\MaintenanceStage;
use App\Models\MaintenanceRequest;
use App\Models\EquipmentCategory;
use Illuminate\Support\Facades\DB;
use App\Models\Equipment;
use App\Models\User;
use Illuminate\Support\Facades\Redirect;

class MaintenanceRequestController extends Controller
{
    /**
     * Lógica centralizada para manejar la finalización de un mantenimiento.
     *
     * @param MaintenanceRequest $maintenanceRequest La solicitud que se está procesando.
     * @param MaintenanceStage $stage La etapa a la que se está moviendo la solicitud.
     * @return void
     */
    private function handleCompletionLogic(MaintenanceRequest $maintenanceRequest, MaintenanceStage $stage): void
    {   
        
        // Salir si no es una etapa final o si ya tiene fecha de finalización
        if (!$stage->is_final_stage || $maintenanceRequest->completion_date) {
            return;
        }

        $maintenanceRequest->completion_date = now();
        $maintenanceRequest->save();

        if (!$maintenanceRequest->equipment_id) {
            return;
        }
        
        $equipment = Equipment::find($maintenanceRequest->equipment_id);
        if (!$equipment) {
            return;
        }
        $equipment->last_maintained_at = $maintenanceRequest->completion_date;

        if ($maintenanceRequest->type === 'corrective') {
            
            $completedCorrectiveRequests = MaintenanceRequest::where('equipment_id', $equipment->id)
                ->where('type', 'corrective')
                ->whereNotNull('completion_date')
                ->orderBy('completion_date', 'asc')
                ->get();
                
            if ($completedCorrectiveRequests->count() > 0) {
                $averageDurationInHours = $completedCorrectiveRequests->avg('duration');
                $equipment->mttr = $averageDurationInHours;
            }

            if ($completedCorrectiveRequests->count() >= 2) {
                $totalTimeBetweenFailures = 0;
                $intervals = 0;

                for ($i = 1; $i < $completedCorrectiveRequests->count(); $i++) {
                    $previousCompletion = $completedCorrectiveRequests[$i - 1]->completion_date;
                    $currentCompletion = $completedCorrectiveRequests[$i]->completion_date;
                    
                    $totalTimeBetweenFailures += $previousCompletion->diffInHours($currentCompletion);
                    $intervals++;
                }
                
                if ($intervals > 0) {
                    $equipment->mtbf = $totalTimeBetweenFailures / $intervals;
                }
            }
        }
        
        $equipment->save();
    }

    public function index(Request $request)
    {
        $stages = MaintenanceStage::orderBy('sequence')->get();

        // Iniciar la consulta
        $query = MaintenanceRequest::query();

        // --- FILTROS EXISTENTES ---
        $query->when($request->input('search'), function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        });

        $query->when($request->input('technician'), function ($query, $technicianId) {
            $query->where('technician_id', $technicianId);
        });

        // --- NUEVOS FILTROS ---
        // 1. Filtro por equipo específico
        $query->when($request->input('equipment'), function ($query, $equipmentId) {
            $query->where('equipment_id', $equipmentId);
        });

        // 2. Filtro por categoría de equipo (usando una subconsulta en la relación)
        $query->when($request->input('category'), function ($query, $categoryId) {
            $query->whereHas('equipment', function ($equipmentQuery) use ($categoryId) {
                $equipmentQuery->where('equipment_category_id', $categoryId);
            });
        });

        // Cargar relaciones y obtener resultados
        $requests = $query->with('user', 'equipment')->get();
        
        // Obtener datos para los dropdowns de los filtros
        $technicians = User::role('technician')->get(['id', 'name']);
        $equipments = Equipment::orderBy('name')->get(['id', 'name']);
        $equipmentCategories = EquipmentCategory::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Maintenance/KanbanBoard', [
            'initialStages' => $stages,
            'initialRequests' => $requests,
            'technicians' => $technicians,
            'equipments' => $equipments, // <-- Pasar lista de equipos
            'equipmentCategories' => $equipmentCategories, // <-- Pasar lista de categorías
            // Asegurarse de que los nuevos filtros se devuelven a la vista
            'filters' => $request->only(['search', 'technician', 'equipment', 'category']),
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
            'equipment_id' => 'nullable|exists:equipment,id', // Puede ser opcional
            'attachments.*' => 'nullable|file|max:10240',
            'duration' => 'nullable|numeric|min:0',
        ]);
        
        try {
            DB::transaction(function () use ($request, $validated) {

                $maintenanceRequest = MaintenanceRequest::create($validated);
                
                // 2. Lógica específica al crear una solicitud CORRECTIVA
                if ($maintenanceRequest->type === 'corrective' && $maintenanceRequest->equipment_id) {
                    $equipment = Equipment::find($maintenanceRequest->equipment_id);
                    // Actualizamos la fecha del último fallo con la fecha de creación de la solicitud.
                    // Esto "registra" el momento en que ocurrió el fallo.
                    $equipment->update(['last_failure_at' => $maintenanceRequest->created_at]);
                }

                // 3. Manejar archivos adjuntos
                if ($request->hasFile('attachments')) {
                    foreach ($request->file('attachments') as $file) {
                        $path = $file->store('attachments', 'public');
                        $maintenanceRequest->attachments()->create([
                            'path' => $path,
                            'original_name' => $file->getClientOriginalName(),
                        ]);
                    }
                }

                // 4. Comprobar si la solicitud se creó directamente en una etapa final
                $initialStage = MaintenanceStage::find($validated['stage_id']);
                if ($initialStage) {
                    $this->handleCompletionLogic($maintenanceRequest, $initialStage);
                }
            });
        } catch (\Exception $e) {
            return Redirect::back()->with('error', 'Ocurrió un error al crear la solicitud: ' . $e->getMessage());
        }
        
        return Redirect::route('mantenimiento.index')->with('success', 'Solicitud creada con éxito.');
    }

    public function show(MaintenanceRequest $maintenanceRequest)
    {
        $maintenanceRequest->load(['stage', 'user', 'technician', 'attachments', 'equipment']);
        
        return Inertia::render('Maintenance/RequestShow', [
            'maintenanceRequest' => $maintenanceRequest,
            'users' => User::all(['id', 'name']), // Pasamos todos los usuarios
            'technician' => User::role('technician')->get(['id', 'name']),
            'stages' => MaintenanceStage::orderBy('sequence')->get(['id', 'name','is_final_stage']),
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
            'stage_id' => 'required|exists:maintenance_stages,id',
            'equipment_id' => 'nullable|exists:equipment,id',
            'attachments.*' => 'nullable|file|max:10240',
            'duration' => 'nullable|numeric|min:0',
        ]);
        
        try {
            DB::transaction(function () use ($request, $validated, $maintenanceRequest) {
                // 1. Actualizar los datos de la solicitud
                $maintenanceRequest->update($validated);

                // 2. Manejar archivos adjuntos
                if ($request->hasFile('attachments')) {
                    // (Lógica de adjuntos aquí, como en tu código original)
                }
                
                // 3. Comprobar si la solicitud se movió a una etapa final
                $newStage = MaintenanceStage::find($validated['stage_id']);
                if ($newStage) {
                    $this->handleCompletionLogic($maintenanceRequest, $newStage);
                }
            });

        } catch (\Exception $e) {
            return Redirect::back()->with('error', 'Ocurrió un error al actualizar la solicitud: ' . $e->getMessage());
        }

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
                // Buscamos la información de la nueva etapa
                $newStage = MaintenanceStage::find($request->stage);

                // Verificamos si la nueva etapa es una etapa final Y si hay un equipo asociado
                if ($newStage) {
                    $this->handleCompletionLogic($maintenanceRequest, $newStage);
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
