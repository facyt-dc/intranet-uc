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

class LocationController extends Controller
{
    public function index()
    {
        return Inertia::render('Inventory/Location/Index', [
        'locations' => Location::orderBy('id')->paginate(10)
    ]);
    }

    public function create()
    {
        return Inertia::render('Inventory/Location/Create');
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
        return Inertia::render('Inventory/Location/Edit', [
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