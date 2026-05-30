<?php

namespace Modules\Inventory\Http\Controllers;

use Modules\Inventory\Models\ItemCategory;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class ItemCategoryController extends Controller
{
    /**
     * Display a listing of the item categories.
     */
    public function index()
    {
        $categories = ItemCategory::orderBy('id')
            ->paginate(10);

        return Inertia::render('inventory::Category/Index', compact('categories'));
    }

    /**
     * Show the form for creating a new item category.
     */
    public function create()
    {
        return Inertia::render('inventory::Category/Create');
    }

    /**
     * Store a newly created item category in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:item_categories,name',
            'description' => 'nullable|string'
        ]);

        DB::transaction(function () use ($validated) {
            ItemCategory::create($validated);
        });

        // En ItemController usas redirect()->route() con un mensaje de 'success'.
        return redirect()->route('admin.item-category.index')->with('success', 'Categoría creada exitosamente.');
    }

    /**
     * Show the form for editing the specified item category.
     */
    public function edit(ItemCategory $itemCategory)
    {
        return Inertia::render('inventory::Category/Edit', [
            'category' => $itemCategory
        ]);
    }

    /**
     * Update the specified item category in storage.
     */
    public function update(Request $request, ItemCategory $itemCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:item_categories,name,' . $itemCategory->id,
            'description' => 'nullable|string'
        ]);

        DB::transaction(function () use ($itemCategory, $validated) {
            $itemCategory->update($validated);
        });

        return redirect()->route('admin.item-category.index')->with('success', 'Categoría actualizada exitosamente.');
    }

    /**
     * Remove the specified item category from storage.
     */
    public function destroy(ItemCategory $itemCategory)
    {
        // Aquí validamos si la categoría tiene ítems asociados antes de eliminarla.
        // Esto previene errores de integridad referencial.
        // Si no se usa "DB::transaction", la validación debe ir antes de "delete".
        if ($itemCategory->items()->exists()) {
            return redirect()->route('admin.item-category.index')
                ->with('error', 'No se puede eliminar la categoría porque tiene ítems asociados.');
        }

        $itemCategory->delete();

        // Retornamos de la misma forma que en ItemController, pero con un mensaje 'success' para la eliminación.
        return redirect()->route('admin.item-category.index')->with('success', 'Categoría eliminada exitosamente.');
    }
}
