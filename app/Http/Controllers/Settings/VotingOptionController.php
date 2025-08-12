<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller; 
use App\Models\VotingOption;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class VotingOptionController extends Controller
{
    /**
     * Muestra la página de gestión con una lista de todas las opciones de votación.
     *
     * @return Response
     */
    public function index(): Response
    {
        return Inertia::render('Settings/VotingOptions/Index', [
            'votingOptions' => VotingOption::orderBy('name')->get(),
        ]);
    }

    /**
     * Almacena una nueva opción de votación global en la base de datos.
     *
     * @param  Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:voting_options,name',
        ]);

        VotingOption::create($validated);

        return to_route('voting-options.index')->with('success', 'Opción de votación creada correctamente.');
    }

    /**
     * Actualiza una opción de votación existente.
     * Principalmente para cambiar su nombre o su estado (activo/inactivo).
     *
     * @param  Request      $request
     * @param  VotingOption $votingOption Inyección del modelo a través de Route Model Binding.
     * @return RedirectResponse
     */
    public function update(Request $request, VotingOption $votingOption): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                // La regla unique debe ignorar el registro actual al actualizar.
                Rule::unique('voting_options')->ignore($votingOption->id),
            ],
            'is_active' => 'required|boolean',
        ]);

        $votingOption->update($validated);

        return to_route('voting-options.index')->with('success', 'Opción de votación actualizada correctamente.');
    }

    /**
     * Elimina una opción de votación de la base de datos.
     *
     * @param  VotingOption $votingOption
     * @return RedirectResponse
     */
    public function destroy(VotingOption $votingOption): RedirectResponse
    {
        // Se usa un bloque try-catch para manejar el error de la base de datos
        // si se intenta eliminar una opción que ya ha sido utilizada en una votación.
        // La migración de la tabla 'votes' tiene una restricción onDelete('restrict').
        try {
            $votingOption->delete();
        } catch (QueryException $e) {
            // El código de error '23000' indica una violación de integridad referencial.
            if ($e->getCode() === '23000') {
                return to_route('voting-options.index')->with('error', 'No se puede eliminar esta opción porque ya ha sido utilizada en votaciones pasadas.');
            }
            // Si el error es otro, lo relanzamos para debugging.
            throw $e;
        }

        return to_route('voting-options.index')->with('success', 'Opción de votación eliminada correctamente.');
    }
}
