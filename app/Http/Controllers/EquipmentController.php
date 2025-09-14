<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\EquipmentCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EquipmentController extends Controller
{   
     public function index(Request $request) // <-- Inyectar Request
    {
        // Iniciar la consulta
        $query = Equipment::query();

        // Aplicar filtro de búsqueda si existe
        $query->when($request->input('search'), function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%");
            });
        });

        // Aplicar filtro de categoría si existe
        $query->when($request->input('category'), function ($query, $category) {
            $query->where('equipment_category_id', $category);
        });

        // Eager load la relación y obtener los resultados
        $equipments = $query->with('category')->orderBy('name')->get();

        return Inertia::render('Maintenance/Equipment/Index', [
            'equipments' => $equipments,
            // Pasar las categorías para el dropdown del filtro
            'categories' => EquipmentCategory::orderBy('name')->get(['id', 'name']),
            // Pasar los filtros actuales para que los inputs los recuerden
            'filters' => $request->only(['search', 'category']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Maintenance/Equipment/Form', [
            'equipment' => null,
            'categories' => EquipmentCategory::orderBy('name')->get(['id', 'name']),
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
            'equipment_category_id' => 'nullable|exists:equipment_categories,id',
            'last_maintained_at' => 'nullable|date',
            'next_maintenance_at' => 'nullable|date',
            'maintenance_frequency' => 'nullable|integer|min:1',
            'maintenance_interval' => 'nullable|string|in:days,months,years',
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
        $equipment->load('maintenanceRequests.user', 'maintenanceRequests.stage', 'category');
        return Inertia::render('Maintenance/Equipment/Form', [
            'equipment' => $equipment,
            'categories' => EquipmentCategory::orderBy('name')->get(['id', 'name']),
        ]);
    }

    // Muestra el formulario directamente en modo EDICIÓN
    public function edit(Equipment $equipment)
    {
        $equipment->load('maintenanceRequests.user', 'maintenanceRequests.stage');
        return Inertia::render('Maintenance/Equipment/Form', [
            'equipment' => $equipment,
            'categories' => EquipmentCategory::orderBy('name')->get(['id', 'name']),
            'isEditingDefault' => true
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
        if ($equipment->maintenanceRequests()->count() > 0) {
            throw ValidationException::withMessages([
                'error' => 'No se puede eliminar el equipo porque tiene solicitudes de mantenimiento asignadas.',
            ]);
        }

        $equipment->delete();
        return Redirect::route('mantenimiento.equipment.index')->with('success', 'Equipo eliminado con éxito.');
    }
}