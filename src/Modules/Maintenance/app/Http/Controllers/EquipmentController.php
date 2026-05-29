<?php

namespace Modules\Maintenance\Http\Controllers;

use Modules\Maintenance\Models\Equipment;
use Modules\Maintenance\Models\EquipmentCategory;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EquipmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Equipment::query();

        $query->when($request->input('search'), function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%");
            });
        });

        $query->when($request->input('category'), function ($query, $category) {
            $query->where('equipment_category_id', $category);
        });

        $equipments = $query->with('category')->orderBy('name')->get();

        return Inertia::render('maintenance::Equipment/Index', [
            'equipments' => $equipments,
            'categories' => EquipmentCategory::orderBy('name')->get(['id', 'name']),
            'filters' => $request->only(['search', 'category']),
        ]);
    }

    public function create()
    {
        return Inertia::render('maintenance::Equipment/Form', [
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

    public function show(Equipment $equipment)
    {
        $equipment->load('maintenanceRequests.user', 'maintenanceRequests.stage', 'category');
        return Inertia::render('maintenance::Equipment/Form', [
            'equipment' => $equipment,
            'categories' => EquipmentCategory::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function edit(Equipment $equipment)
    {
        $equipment->load('maintenanceRequests.user', 'maintenanceRequests.stage');
        return Inertia::render('maintenance::Equipment/Form', [
            'equipment' => $equipment,
            'categories' => EquipmentCategory::orderBy('name')->get(['id', 'name']),
            'isEditingDefault' => true,
        ]);
    }

    public function update(Request $request, Equipment $equipment)
    {
        $equipment->update($this->validateEquipment($request, $equipment));
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
