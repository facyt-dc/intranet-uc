<?php

namespace App\Http\Controllers;

use App\Models\ItemCategory;
use App\Models\ItemStatus;
use App\Models\Location;
use App\Models\MovementType;
use App\Models\Item;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class MovementTypeController extends Controller
{
    public function index()
    {
        return Inertia::render('Inventory/MovementType/Index', [
            'movementTypes' => MovementType::orderBy('id')->paginate(10)
        ]);

    }

    public function create()
    {
        return Inertia::render('Inventory/MovementType/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:movement_types,name',
            'description' => 'nullable|string'
        ]);

        $type = MovementType::create($validated);

        return to_route('admin.item-movement-type.index')->with('flash', [
            'alert' => [
                'id' => $type->id,
                'message' => 'Tipo de movimiento creado correctamente!',
                'severity' => 'success'
            ]
        ]);
    }

    public function edit(MovementType $movementType)
    {
        return Inertia::render('Inventory/MovementType/Edit', [
            'movementType' => $movementType
        ]);
    }

    public function update(Request $request, MovementType $movementType)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:movement_types,name,' . $movementType->id,
            'description' => 'nullable|string'
        ]);

        $movementType->update($validated);

        return to_route('admin.item-movement-type.index')->with('flash', [
            'alert' => [
                'id' => $movementType->id,
                'message' => 'Tipo de movimiento actualizado correctamente!',
                'severity' => 'success'
            ]
        ]);
    }

    public function destroy(MovementType $movementType)
    {
        $id = $movementType->id;
        $movementType->delete();

        return to_route('admin.item-movement-type.index')->with('flash', [
            'alert' => [
                'id' => $id,
                'message' => 'Tipo de movimiento eliminado correctamente.',
                'severity' => 'error'
            ]
        ]);
    }
}