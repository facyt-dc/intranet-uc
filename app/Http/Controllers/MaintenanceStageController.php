<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceStage;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;
use Inertia\Response;
use Illuminate\Validation\ValidationException;

class MaintenanceStageController extends Controller
{
    public function index(): Response
    {   
        return Inertia::render('Maintenance/StageManager', [
            'stages' => MaintenanceStage::orderBy('sequence')->get(),
        ]);
    }

    public function store(Request $request)
    {
        // El validador de 'boolean' maneja correctamente los valores del checkbox
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:maintenance_stages',
            'sequence' => 'required|integer',
            'is_final_stage' => 'nullable|boolean',
        ]);

        // Si el checkbox no se marca, no se enviará. Nos aseguramos de que sea false.
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