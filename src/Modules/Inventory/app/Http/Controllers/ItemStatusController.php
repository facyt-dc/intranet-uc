<?php

namespace Modules\Inventory\Http\Controllers;

use Modules\Inventory\Models\Item;
use Modules\Inventory\Models\ItemStatus;
use Modules\Inventory\Models\ItemCategory;
use Modules\Inventory\Models\Location;
use Modules\Inventory\Models\MovementType;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;

class ItemStatusController extends Controller
{
    public function index()
    {
        return Inertia::render('inventory::Status/Index', [
            'statuses' => ItemStatus::orderBy('id')->paginate(10)
        ]);
    }

    public function create()
    {
        return Inertia::render('inventory::Status/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:item_statuses,name',
            'description' => 'nullable|string',
            'is_operational' => 'required|boolean'
        ]);

        $status = ItemStatus::create($validated);

        return to_route('admin.item-status.index')->with('flash', [
            'alert' => [
                'id' => $status->id,
                'message' => 'Estado creado correctamente!',
                'severity' => 'success'
            ]
        ]);
    }

    public function edit(ItemStatus $item_status)
    {
        return Inertia::render('inventory::Status/Edit', [
            'status' => $item_status
        ]);
    }

    public function update(Request $request, ItemStatus $item_status)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:item_statuses,name,' . $item_status->id,
            'description' => 'nullable|string',
            'is_operational' => 'required|boolean'
        ]);

        $item_status->update($validated);

        return to_route('admin.item-status.index')->with('flash', [
            'alert' => [
                'id' => $item_status->id,
                'message' => 'Estado actualizado correctamente!',
                'severity' => 'success'
            ]
        ]);
    }

    public function destroy(ItemStatus $item_status)
    {
        $id = $item_status->id;
        $item_status->delete();

        return to_route('admin.item-status.index')->with('flash', [
            'alert' => [
                'id' => $id,
                'message' => 'Estado eliminado correctamente.',
                'severity' => 'error'
            ]
        ]);
    }
}
