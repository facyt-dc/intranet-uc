<?php

namespace App\Http\Controllers;

use App\Models\EquipmentCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;
use Inertia\Response;

class EquipmentCategoryController extends Controller
{
    /**
     * Muestra la página de gestión de categorías.
     */
    public function index()
    {   
        return Inertia::render('Maintenance/Equipment/CategoryManager', [
            'categories' => EquipmentCategory::orderBy('name')->get(),
        ]);
    }

    /**
     * Almacena una nueva categoría en la base de datos.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:equipment_categories',
        ]);

        EquipmentCategory::create($validated);
        
        return Redirect::back()->with('success', 'Categoría creada con éxito.');
    }

    /**
     * Actualiza una categoría existente.
     */
    public function update(Request $request, EquipmentCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:equipment_categories,name,' . $category->id,
        ]);
                
        $category->update($validated);
        
        return back()->with('success', 'Categoría actualizada.');
    }

    /**
     * Elimina una categoría.
     */
    public function destroy(EquipmentCategory $category)
    {
        // Importante: Prevenir la eliminación si la categoría está en uso
        if ($category->equipments()->count() > 0) {
            return back()->with('error', 'No se puede eliminar la categoría porque está asignada a uno o más equipos.');
        }

        $category->delete();

        return Redirect::back()->with('success', 'Categoría eliminada.');
    }
}