<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceStage;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;
use Inertia\Response; // Importar la clase Response de Inertia

class MaintenanceStageController extends Controller
{
    /**
     * Muestra la página de gestión de etapas (Vista de React).
     */
    public function index(): Response // Se especifica el tipo de retorno
    {   
        return Inertia::render('Maintenance/StageManager', [
            'stages' => MaintenanceStage::orderBy('sequence')->get(),
        ]);
    }

    /**
     * Guarda una nueva etapa.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:maintenance_stages',
            'sequence' => 'required|integer',
            'is_final_stage' => 'boolean',
        ]);

        MaintenanceStage::create($validated);
        // Se redirige a la página de gestión de etapas
        return Redirect::route('mantenimiento.stages.index')->with('success', 'Etapa creada con éxito.');
    }

    /**
     * Actualiza una etapa existente.
     */
    public function update(Request $request, MaintenanceStage $stage) // El parámetro debe llamarse $stage para coincidir con el resource
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:maintenance_stages,name,' . $stage->id,
            'sequence' => 'required|integer',
            'is_final_stage' => 'boolean',
        ]);

        $stage->update($validated);
        $stage->save();
        return back()->with('success', 'Etapa actualizada.');
    }

    /**
     * Elimina una etapa.
     */
    public function destroy(MaintenanceStage $stage) // El parámetro debe llamarse $stage para coincidir con el resource
    {
        if ($stage->maintenanceRequests()->count() > 0) {
            return back()->with('error', 'No se puede eliminar la etapa porque tiene solicitudes asignadas.');
        }

        $stage->delete();

        // Se redirige a la página de gestión de etapas
        return Redirect::route('mantenimiento.stages.page')->with('success', 'Etapa eliminada.');
    }
}