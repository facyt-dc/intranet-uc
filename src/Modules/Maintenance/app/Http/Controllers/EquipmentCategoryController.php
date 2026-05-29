<?php

namespace Modules\Maintenance\Http\Controllers;

use Modules\Maintenance\Models\EquipmentCategory;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;
use Inertia\Response;
use Illuminate\Validation\ValidationException;

class EquipmentCategoryController extends Controller
{
    public function index()
    {
        return Inertia::render('maintenance::Equipment/CategoryManager', [
            'categories' => EquipmentCategory::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:equipment_categories',
        ]);

        EquipmentCategory::create($validated);

        return Redirect::back()->with('success', 'Categoría creada con éxito.');
    }

    public function update(Request $request, EquipmentCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:equipment_categories,name,' . $category->id,
        ]);

        $category->update($validated);

        return back()->with('success', 'Categoría actualizada.');
    }

    public function destroy(EquipmentCategory $category)
    {
        if ($category->equipments()->count() > 0) {
            throw ValidationException::withMessages([
                'error' => 'No se puede eliminar la categoría porque está asignada a uno o más equipos.',
            ]);
        }

        $category->delete();

        return Redirect::back()->with('success', 'Categoría eliminada.');
    }
}
