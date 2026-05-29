<?php

namespace Modules\Maintenance\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Modules\Maintenance\Models\MaintenanceStage;
use Modules\Maintenance\Models\MaintenanceRequest;
use Modules\Maintenance\Models\EquipmentCategory;
use Modules\Maintenance\Models\Equipment;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Modules\Maintenance\Events\MaintenanceRequesetStageUpdated;

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

    private function recalculateEquipmentMetrics(Equipment $equipment): void
    {
        $completedCorrective = MaintenanceRequest::where('equipment_id', $equipment->id)
            ->where('type', 'corrective')
            ->whereNotNull('completion_date')
            ->orderBy('completion_date', 'asc')
            ->get();
        $allMaintenance = MaintenanceRequest::where('equipment_id', $equipment->id)
            ->whereNotNull('completion_date')
            ->orderBy('completion_date', 'asc')
            ->get();

        if ($allMaintenance->count() > 0) {
            $averageDuration = $allMaintenance->avg('duration');
            $equipment->mttr = $averageDuration > 0 ? $averageDuration : null;
        }

        if ($completedCorrective->count() >= 2) {
            $totalTimeBetweenFailures = 0;
            for ($i = 1; $i < $completedCorrective->count(); $i++) {
                $previousCompletion = $completedCorrective[$i - 1]->completion_date;
                $currentCompletion = $completedCorrective[$i]->completion_date;
                $totalTimeBetweenFailures += $previousCompletion->diffInDays($currentCompletion);
            }
            $equipment->mtbf = $totalTimeBetweenFailures / ($completedCorrective->count() - 1);
        }
        $equipment->last_failure_at = $completedCorrective->last()->completion_date ?? null;
        $equipment->last_maintained_at = $allMaintenance->last()->completion_date ?? null;
        $equipment->save();
    }

    private function applyRequestFilters(Builder $query, Request $request): Builder
    {
        $query->when($request->input('search'), function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
            });
        });

        $query->when($request->input('technician'), function ($query, $technicianId) {
            $query->where('technician_id', $technicianId);
        });

        $query->when($request->input('equipment'), function ($query, $equipmentId) {
            $query->where('equipment_id', $equipmentId);
        });

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

        $query = MaintenanceRequest::query();
        $query->where('is_archived', false);
        $query = $this->applyRequestFilters($query, $request);

        $requests = $query->with('user', 'equipment')->get();

        $technicians = User::role('technician')->get(['id', 'name']);
        $equipments = Equipment::orderBy('name')->get(['id', 'name']);
        $equipmentCategories = EquipmentCategory::orderBy('name')->get(['id', 'name']);

        return Inertia::render('maintenance::KanbanBoard', [
            'initialStages' => $stages,
            'initialRequests' => $requests,
            'technicians' => $technicians,
            'equipments' => $equipments,
            'equipmentCategories' => $equipmentCategories,
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

        $technicians = User::role('technician')->get(['id', 'name']);
        $equipments = Equipment::orderBy('name')->get(['id', 'name']);
        $equipmentCategories = EquipmentCategory::orderBy('name')->get(['id', 'name']);

        return Inertia::render('maintenance::ArchivedIndex', [
            'requests' => $requests,
            'technicians' => $technicians,
            'equipments' => $equipments,
            'equipmentCategories' => $equipmentCategories,
            'filters' => $request->only(['search', 'technician', 'equipment', 'category']),
        ]);
    }

    public function create()
    {
        return Inertia::render('maintenance::RequestShow', [
            'maintenanceRequest' => null,
            'users' => User::all(['id', 'name']),
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
            'stage_id' => [
                'required',
                'exists:maintenance_stages,id',
                function ($attribute, $value, $fail) {
                    $stage = MaintenanceStage::find($value);
                    if ($stage && $stage->is_final_stage) {
                        $fail('No se pueden crear solicitudes directamente en una etapa de finalización.');
                    }
                },
            ],
            'equipment_id' => 'required|exists:equipment,id',
            'attachments.*' => 'nullable|file|max:10240',
            'duration' => 'nullable|numeric|min:0',
            'completion_date' => 'nullable|date',
        ]);
        try {
            DB::transaction(function () use ($request, $validated) {
                $maintenanceRequest = MaintenanceRequest::create($validated);

                if ($maintenanceRequest->type === 'corrective' && $maintenanceRequest->equipment_id) {
                    $equipment = Equipment::find($maintenanceRequest->equipment_id);
                    $equipment->update(['last_failure_at' => $maintenanceRequest->created_at]);
                }

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
            });
        } catch (\Exception $e) {
            return Redirect::back()->with('error', 'Ocurrió un error al crear la solicitud: ' . $e->getMessage());
        }

        return Redirect::route('mantenimiento.index')->with('success', 'Solicitud creada con éxito.');
    }

    public function show(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        $maintenanceRequest->load(['stage', 'user', 'technician', 'attachments', 'equipment']);

        return Inertia::render('maintenance::RequestShow', [
            'maintenanceRequest' => $maintenanceRequest,
            'users' => User::all(['id', 'name']),
            'technician' => User::role('technician')->get(['id', 'name']),
            'stages' => MaintenanceStage::orderBy('sequence')->get(['id', 'name', 'is_final_stage']),
            'equipments' => Equipment::all(['id', 'name']),
            'came_from' => $request->query('from', 'kanban'),
        ]);
    }

    public function edit(MaintenanceRequest $maintenanceRequest)
    {
        return Inertia::render('maintenance::RequestShow', [
            'maintenanceRequest' => $maintenanceRequest,
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
            'equipment_id' => 'required|exists:equipment,id',
            'attachments.*' => 'nullable|file|max:10240',
            'duration' => 'nullable|numeric|min:0',
            'completion_date' => 'nullable|date',
        ]);
        try {
            DB::transaction(function () use ($request, $validated, $maintenanceRequest) {
                $maintenanceRequest->update($validated);

                if ($request->hasFile('attachments')) {
                    foreach ($request->file('attachments') as $file) {
                        $path = $file->store('attachments', 'public');
                        $maintenanceRequest->attachments()->create([
                            'path' => $path,
                            'original_name' => $file->getClientOriginalName(),
                        ]);
                    }
                }
                if ($maintenanceRequest->type === 'preventive') {
                    $this->scheduleNextPreventiveMaintenance($maintenanceRequest);
                }

                $newStage = MaintenanceStage::find($validated['stage_id']);
                if ($newStage && $newStage->is_final_stage) {
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
            DB::transaction(function () use ($request, $maintenanceRequest) {
                $maintenanceRequest->update(['stage_id' => $request->stage]);
                $newStage = MaintenanceStage::find($request->stage);

                if ($newStage && $newStage->is_final_stage) {
                    if (!$maintenanceRequest->completion_date) {
                        $maintenanceRequest->completion_date = now();
                        $maintenanceRequest->save();
                    }
                    if ($maintenanceRequest->equipment_id) {
                        $this->recalculateEquipmentMetrics($maintenanceRequest->equipment);
                    }
                }

                event(new MaintenanceRequesetStageUpdated($maintenanceRequest));
            });
        } catch (\Exception $e) {
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
}
