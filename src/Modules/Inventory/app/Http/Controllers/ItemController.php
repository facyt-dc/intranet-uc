<?php

namespace Modules\Inventory\Http\Controllers;

use Modules\Inventory\Models\Item;
use Modules\Inventory\Models\ItemCategory;
use Modules\Inventory\Models\ItemStatus;
use Modules\Inventory\Models\Location;
use Modules\Inventory\Models\InventoryMovement;
use Modules\Inventory\Models\MovementType;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ItemController extends Controller
{
    /**
     * Display a listing of the items.
     */
    public function index()
    {
        $items = Item::with(['category', 'currentStatus', 'currentLocation'])
                     ->orderBy('id')
                     ->paginate(10);

        return Inertia::render('inventory::Item/Index', compact('items'));
    }

    /**
     * Show the form for creating a new item.
     */
    public function create()
    {
        return Inertia::render('inventory::Item/Create', [
            'categories' => ItemCategory::all(),
            'statuses' => ItemStatus::all(),
            'locations' => Location::all(),
        ]);
    }

    /**
     * Store a newly created item in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'serial_number' => 'nullable|string|unique:items',
            'category_id' => 'required|exists:item_categories,id',
            'current_status_id' => 'required|exists:item_statuses,id',
            'current_location_id' => 'required|exists:locations,id',
            'description' => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($validated) {
            $item = Item::create($validated);

            InventoryMovement::create([
                'item_id' => $item->id,
                'user_id' => auth()->id(),
                'movement_type_id' => MovementType::where('name', 'Entrada')->first()->id,
                'description' => $item->description,
                'movement_date' => now(),
                'details' => ['initial_data' => $validated]
            ]);
        });

        return redirect()->route('admin.item.index')->with('success', 'Ítem creado exitosamente.');
    }

    /**
     * Show the form for editing the specified item.
     */
    public function edit(Item $item)
    {
        return Inertia::render('inventory::Item/Edit', [
            'item' => $item->load(['category', 'currentStatus', 'currentLocation']),
            'categories' => ItemCategory::all(),
            'statuses' => ItemStatus::all(),
            'locations' => Location::all(),
        ]);
    }

    /**
     * Update the specified item in storage.
     */
    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'serial_number' => 'nullable|string|unique:items,serial_number,' . $item->id,
            'category_id' => 'required|exists:item_categories,id',
            'current_status_id' => 'required|exists:item_statuses,id',
            'current_location_id' => 'required|exists:locations,id',
            'description' => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($item, $validated) {
            $item->update($validated);

            InventoryMovement::create([
                'item_id' => $item->id,
                'user_id' => auth()->id(),
                'movement_type_id' => MovementType::where('name', 'Actualización')->first()->id,
                'description' => $item->description,
                'movement_date' => now(),
                'details' => ['updated_data' => $validated]
            ]);
        });

        return redirect()->route('admin.item.index')->with('success', 'Ítem actualizado exitosamente.');
    }

    /**
     * Remove the specified item from storage.
     */
    public function destroy(Item $item)
    {
        $item->delete();

        return redirect()->route('admin.item.index')->with('success', 'Ítem eliminado exitosamente.');
    }
}
