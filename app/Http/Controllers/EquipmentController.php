<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;

class EquipmentController extends Controller
{
    public function index()
    {
        return Inertia::render('Maintenance/Equipment/Index', [
            'equipments' => Equipment::orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Maintenance/Equipment/Form', [
            'equipment' => null
        ]);
    }

    private function validateEquipment(Request $request, Equipment $equipment = null)
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'serial_number' => [
                'nullable',
                'string',
                'max:255',
                // Asegura que el número de serie sea único, ignorando el equipo actual al actualizar
                Rule::unique('equipment')->ignore($equipment?->id),
            ],
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'last_maintained_at' => 'nullable|date',
            'next_maintenance_at' => 'nullable|date',
            'last_failure_at' => 'nullable|date',
            'mtbf' => 'nullable|integer|min:0',
            'mttr' => 'nullable|integer|min:0',
        ]);
    }

    public function store(Request $request)
    {
        Equipment::create($this->validateEquipment($request));
        return Redirect::route('mantenimiento.equipment.index')->with('success', 'Equipo creado con éxito.');
    }

    // Muestra el formulario en modo VISTA
    public function show(Equipment $equipment)
    {
        $equipment->load('maintenanceRequests.user', 'maintenanceRequests.stage');
        return Inertia::render('Maintenance/Equipment/Form', [
            'equipment' => $equipment
        ]);
    }

    // Muestra el formulario directamente en modo EDICIÓN
    public function edit(Equipment $equipment)
    {
        $equipment->load('maintenanceRequests.user', 'maintenanceRequests.stage');
        return Inertia::render('Maintenance/Equipment/Form', [
            'equipment' => $equipment,
            'isEditingDefault' => true // Propiedad clave para iniciar en modo edición
        ]);
    }

    public function update(Request $request, Equipment $equipment)
    {
        $equipment->update($this->validateEquipment($request, $equipment));
        // Redirigimos a la vista 'show' para ver los cambios guardados.
        return Redirect::route('mantenimiento.equipment.show', $equipment->id)->with('success', 'Equipo actualizado con éxito.');
    }

    public function destroy(Equipment $equipment)
    {
        $equipment->delete();
        return Redirect::route('mantenimiento.equipment.index')->with('success', 'Equipo eliminado con éxito.');
    }
}