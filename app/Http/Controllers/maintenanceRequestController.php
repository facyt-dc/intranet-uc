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
use Illuminate\Database\Eloquent\Builder;

class MaintenanceRequestController extends Controller
{
    private function scheduleNextPreventiveMaintenance(MaintenanceRequest $request): void
    {
        $equipment = $request->equipment;

        if (!$equipment || !$equipment->maintenance_frequency || !$equipment->maintenance_interval) {
            return;
        }

        $nextDate = now();
        switch ($equipment->maintenance_interval) {
            case 'days': $nextDate->addDays($equipment->maintenance_frequency); break;
            case 'months': $nextDate->addMonths($equipment->maintenance_frequency); break;
            case 'years': $nextDate->addYears($equipment->maintenance_frequency); break;
        }

        $equipment->update(['next_maintenance_at' => $nextDate]);
    }
    /**
     * Lógica centralizada para manejar la finalización de un mantenimiento.
     *
     * @param MaintenanceRequest $maintenanceRequest La solicitud que se está procesando.
     * @param MaintenanceStage $stage La etapa a la que se está moviendo la solicitud.
     * @return void
     */
    private function recalculateEquipmentMetrics(Equipment $equipment): void
    {
        // Obtener todas las solicitudes correctivas completadas y ordenarlas
        $completedCorrective = MaintenanceRequest::where('equipment_id', $equipment->id)
            ->where('type', 'corrective')
            ->whereNotNull('completion_date')
            ->orderBy('completion_date', 'asc')
            ->get();

        // --- Cálculo de MTTR (Tiempo Medio de Reparación) ---
        // Se basa en el promedio de duración de TODAS las reparaciones completadas.
        if ($completedCorrective->count() > 0) {
            $averageDuration = $completedCorrective->avg('duration');
            $equipment->mttr = $averageDuration > 0 ? $averageDuration : null;
        }

        // --- Cálculo de MTBF (Tiempo Medio Entre Fallos) ---
        // Necesita al menos dos fallos para calcular el tiempo *entre* ellos.
        if ($completedCorrective->count() >= 2) {
            $totalTimeBetweenFailures = 0;
            // Iteramos desde el segundo elemento para compararlo con el anterior
            for ($i = 1; $i < $completedCorrective->count(); $i++) {
                $previousCompletion = $completedCorrective[$i - 1]->completion_date;
                $currentCompletion = $completedCorrective[$i]->completion_date;
                $totalTimeBetweenFailures += $previousCompletion->diffInDays($currentCompletion);
            }
            $equipment->mtbf = $totalTimeBetweenFailures / ($completedCorrective->count() - 1);
        }

        $equipment->save();
    }
    /**
     * Aplica los filtros comunes de la request a una consulta de solicitudes de mantenimiento.
     *
     * @param Builder $query La consulta de Eloquent a modificar.
     * @param Request $request La request HTTP actual.
     * @return Builder La consulta con los filtros aplicados.
     */
    private function applyRequestFilters(Builder $query, Request $request): Builder
    {
        // Filtro de búsqueda por título o descripción
        $query->when($request->input('search'), function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
            });
        });

        // Filtro por técnico asignado
        $query->when($request->input('technician'), function ($query, $technicianId) {
            $query->where('technician_id', $technicianId);
        });

        // Filtro por equipo específico
        $query->when($request->input('equipment'), function ($query, $equipmentId) {
            $query->where('equipment_id', $equipmentId);
        });

        // Filtro por categoría de equipo
        $query->when($request->input('category'), function ($query, $categoryId) {
            $query->whereHas('equipment', function ($equipmentQuery) use ($categoryId) {
                $equipmentQuery->where('equipment_category_id', $categoryId);
            });
        });

        return $query;
    }
    public function index(Request $request)
    {
        $stages = MaintenanceStage::orderBy('sequence')->get();

        // Iniciar la consulta
        $query = MaintenanceRequest::query();
        $query->where('is_archived', false);
        $query = $this->applyRequestFilters($query, $request);

        $requests = $query->with('user', 'equipment')->get();
        
        $technicians = User::role('technician')->get(['id', 'name']);
        $equipments = Equipment::orderBy('name')->get(['id', 'name']);
        $equipmentCategories = EquipmentCategory::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Maintenance/KanbanBoard', [
            'initialStages' => $stages,
            'initialRequests' => $requests,
            'technicians' => $technicians,
            'equipments' => $equipments, // <-- Pasar lista de equipos
            'equipmentCategories' => $equipmentCategories, // <-- Pasar lista de categorías
            'filters' => $request->only(['search', 'technician', 'equipment', 'category']),
        ]);
    }

    public function toggleArchive(MaintenanceRequest $maintenanceRequest)
    {
        $maintenanceRequest->is_archived = !$maintenanceRequest->is_archived;
        $maintenanceRequest->save();

        $message = $maintenanceRequest->is_archived ? 'Solicitud archivada con éxito.' : 'Solicitud desarchivada con éxito.';

        return redirect()->back()->with('success', $message);
    }

    public function archivedIndex(Request $request)
    {
        $query = MaintenanceRequest::where('is_archived', true);

        $query = $this->applyRequestFilters($query, $request);

        $requests = $query->with(['user', 'equipment', 'stage'])->orderBy('updated_at', 'desc')->get();

        // Obtener datos para los filtros
        $technicians = User::role('technician')->get(['id', 'name']);
        $equipments = Equipment::orderBy('name')->get(['id', 'name']);
        $equipmentCategories = EquipmentCategory::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Maintenance/ArchivedIndex', [
            'requests' => $requests,
            'technicians' => $technicians,
            'equipments' => $equipments,
            'equipmentCategories' => $equipmentCategories,
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
            'completion_date' => 'nullable|date',
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
                if ($initialStage && $initialStage->is_final_stage) {
                    // Si no se proveyó una fecha, se usa la actual
                    if (!$maintenanceRequest->completion_date) {
                        $maintenanceRequest->completion_date = now();
                        $maintenanceRequest->save();
                    }

                    // Si hay un equipo, recalculamos sus métricas
                    if ($maintenanceRequest->equipment_id) {
                        $this->recalculateEquipmentMetrics($maintenanceRequest->equipment);
                    }
                }
            });
        } catch (\Exception $e) {
            return Redirect::back()->with('error', 'Ocurrió un error al crear la solicitud: ' . $e->getMessage());
        }
        
        return Redirect::route('mantenimiento.index')->with('success', 'Solicitud creada con éxito.');
    }

    public function show(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        $maintenanceRequest->load(['stage', 'user', 'technician', 'attachments', 'equipment']);
        
        return Inertia::render('Maintenance/RequestShow', [
            'maintenanceRequest' => $maintenanceRequest,
            'users' => User::all(['id', 'name']), // Pasamos todos los usuarios
            'technician' => User::role('technician')->get(['id', 'name']),
            'stages' => MaintenanceStage::orderBy('sequence')->get(['id', 'name','is_final_stage']),
            'equipments' => Equipment::all(['id', 'name']),
            'came_from' => $request->query('from', 'kanban'),
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
            'completion_date' => 'nullable|date',
        ]);
        
        try {
            DB::transaction(function () use ($request, $validated, $maintenanceRequest) {
                // 1. Actualizar los datos de la solicitud
                $oldStageIsFinal = $maintenanceRequest->stage?->is_final_stage ?? false;
                $maintenanceRequest->update($validated);

                // 2. Manejar archivos adjuntos
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
                if ($maintenanceRequest->type === 'preventive') {
                    $this->scheduleNextPreventiveMaintenance($maintenanceRequest);
                }
                // 3. Comprobar si la solicitud se movió a una etapa final
                $newStage = MaintenanceStage::find($validated['stage_id']);
                if ($newStage && $newStage->is_final_stage && !$oldStageIsFinal) {
                    // Si no se proveyó una fecha, se usa la actual. Solo se setea la primera vez.
                    if (!$maintenanceRequest->completion_date) {
                        $maintenanceRequest->completion_date = now();
                        $maintenanceRequest->save();
                    }
                }
                if ($newStage && $newStage->is_final_stage && $maintenanceRequest->equipment_id) {
                    $this->recalculateEquipmentMetrics($maintenanceRequest->equipment);
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
    public function forceDestroy(MaintenanceRequest $maintenanceRequest)
    {
        $maintenanceRequest->delete();
        return Redirect::back()->with('success', 'Solicitud eliminada permanentemente.');
    }
    protected static function booted()
    {
        static::deleting(function ($request) {
            // Iterar sobre cada archivo adjunto y eliminarlo del disco.
            $request->attachments()->each(function ($attachment) {
                Storage::disk('public')->delete($attachment->path);
                // La fila de la base de datos se eliminará por cascada si está configurada,
                // o podemos eliminarla aquí si es necesario.
                $attachment->delete(); 
            });
        });
    }
}
