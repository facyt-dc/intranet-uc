<?php

namespace Modules\Inventory\Http\Controllers;

use Modules\Inventory\Models\ItemCategory;
use Modules\Inventory\Models\ItemStatus;
use Modules\Inventory\Models\Location;
use Modules\Inventory\Models\MovementType;
use Modules\Inventory\Models\Item;
use Modules\Inventory\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class LocationController extends Controller
{
    public function index()
    {
        return Inertia::render('inventory::Location/Index', [
        'locations' => Location::orderBy('id')->paginate(10)
    ]);
    }

    public function create()
    {
        return Inertia::render('inventory::Location/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:locations,name',
            'description' => 'nullable|string'
        ]);

        $location = Location::create($validated);

        return to_route('admin.item-location.index')->with('flash', [
            'alert' => [
                'id' => $location->id,
                'message' => 'Ubicación creada correctamente!',
                'severity' => 'success'
            ]
        ]);
    }

    public function edit(Location $location)
    {
        return Inertia::render('inventory::Location/Edit', [
            'location' => $location
        ]);
    }

    public function update(Request $request, Location $location)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:locations,name,' . $location->id,
            'description' => 'nullable|string'
        ]);

        $location->update($validated);

        return to_route('admin.item-location.index')->with('flash', [
            'alert' => [
                'id' => $location->id,
                'message' => 'Ubicación actualizada correctamente!',
                'severity' => 'success'
            ]
        ]);
    }

    public function destroy(Location $location)
    {
        $id = $location->id;
        $location->delete();

        return to_route('admin.item-location.index')->with('flash', [
            'alert' => [
                'id' => $id,
                'message' => 'Ubicación eliminada correctamente.',
                'severity' => 'error'
            ]
        ]);
    }
}
