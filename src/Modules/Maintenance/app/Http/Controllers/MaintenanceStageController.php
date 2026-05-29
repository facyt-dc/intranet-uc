<?php

namespace Modules\Maintenance\Http\Controllers;

use Modules\Maintenance\Models\MaintenanceStage;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;
use Inertia\Response;
use Illuminate\Validation\ValidationException;

class MaintenanceStageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('maintenance::StageManager', [
            'stages' => MaintenanceStage::orderBy('sequence')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:maintenance_stages',
            'sequence' => 'required|integer',
            'is_final_stage' => 'nullable|boolean',
        ]);

        $validated['is_final_stage'] = $request->input('is_final_stage', false);

        MaintenanceStage::create($validated);

        return Redirect::route('mantenimiento.stages.index')->with('success', 'Etapa creada con éxito.');
    }

    public function update(Request $request, MaintenanceStage $stage)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:maintenance_stages,name,' . $stage->id,
            'sequence' => 'required|integer',
            'is_final_stage' => 'nullable|boolean',
        ]);

        $stage->update($validated);
        $stage->save();
        return back()->with('success', 'Etapa actualizada.');
    }

    public function destroy(MaintenanceStage $stage)
    {
        if ($stage->maintenanceRequests()->count() > 0) {
            throw ValidationException::withMessages([
                'error' => 'No se puede eliminar la etapa porque está asignada a uno o más equipos.',
            ]);
        }

        $stage->delete();

        return Redirect::route('mantenimiento.stages.index')->with('success', 'Etapa eliminada.');
    }
}
