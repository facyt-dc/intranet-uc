<?php

namespace App\Http\Controllers;

use App\Models\Council;
use App\Models\CouncilPoint;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CouncilPointController extends Controller
{
    /**
     * Almacena un nuevo punto de consejo asociado a un consejo existente.
     *
     * @param  Request $request
     * @param  Council $council El consejo padre, inyectado por Route Model Binding.
     * @return RedirectResponse
     */
    public function store(Request $request, Council $council): RedirectResponse
    {
        // Contamos cuántos usuarios votantes se seleccionaron en el request.
        $numberOfVotableUsers = count($request->input('votable_users', []));

        // Validación de los datos de entrada
        $validated = $request->validate([
            'description' => 'required|string',
            'requested_by_user_id' => [
                'required',
                'integer',
                // Regla: asegura que el usuario solicitante sea un participante del consejo.
                Rule::exists('council_user', 'user_id')->where('council_id', $council->id),
            ],
            'min_votes_to_close' => [
                'required',
                'integer',
                'min:1',
                'max:' . $numberOfVotableUsers // El máximo es el número de votantes.
            ],
            'order' => 'nullable|integer',
            'votable_users' => 'required|array|min:1',
            'votable_users.*' => [
                'required',
                'integer',
                // Regla: asegura que todos los votantes asignados sean participantes del consejo.
                Rule::exists('council_user', 'user_id')->where('council_id', $council->id),
            ],
            'available_options' => 'required|array|min:1',
            'available_options.*' => 'required|integer|exists:voting_options,id',
        ], [
            // Mensaje de error personalizado para esta regla
            'min_votes_to_close.max' => 'El mínimo de votos no puede ser mayor que el número de votantes seleccionados.'
        ]);

        // Creación del punto del consejo, asociándolo directamente al consejo padre
        $point = $council->points()->create([
            'description' => $validated['description'],
            'requested_by_user_id' => $validated['requested_by_user_id'],
            'min_votes_to_close' => $validated['min_votes_to_close'],
            'order' => $validated['order'] ?? 0,
            'status' => 'Abierto para Votación', // Estado por defecto
        ]);

        // Sincronización de las relaciones "muchos a muchos"
        $point->votableUsers()->sync($validated['votable_users']);
        $point->votingOptions()->sync($validated['available_options']);

        // Redirección a la vista del consejo con un mensaje de éxito
        return to_route('councils.show', $council)->with('success', 'Punto del consejo añadido correctamente.');
    }

    /**
     * Actualiza un punto de consejo existente.
     *
     * @param  Request      $request
     * @param  Council      $council El consejo padre
     * @param  CouncilPoint $point   El punto a actualizar
     * @return RedirectResponse
     */
    public function update(Request $request, CouncilPoint $point): RedirectResponse
    {
        $council = $point->council;

        if ($point->votes()->exists()) {
            return back()->with('error', 'No se puede editar un punto que ya tiene votos.');
        }

        if ($council->status === 'Cerrado') {
            return back()->with('error', 'No se pueden editar puntos de un consejo que ya ha sido cerrado.');
        }

        $numberOfVotableUsers = count($request->input('votable_users', []));
        // 1. Validación (idéntica a la de 'store')
        $validated = $request->validate([
            'description' => 'required|string',
            'requested_by_user_id' => [
                'required',
                'integer',
                Rule::exists('council_user', 'user_id')->where('council_id', $council->id),
            ],
            'min_votes_to_close' => [
                'required',
                'integer',
                'min:1',
                'max:' . $numberOfVotableUsers
            ],
            'order' => 'nullable|integer',
            'votable_users' => 'required|array|min:1',
            'votable_users.*' => ['required', 'integer', Rule::exists('council_user', 'user_id')->where('council_id', $council->id)],
            'available_options' => 'required|array|min:1',
            'available_options.*' => 'required|integer|exists:voting_options,id',
        ], [
            'min_votes_to_close.max' => 'El mínimo de votos no puede ser mayor que el número de votantes seleccionados.'
        ]);

        // 2. Actualización de los datos principales del punto
        $point->update($validated);

        // 3. Resincronización de las relaciones
        $point->votableUsers()->sync($validated['votable_users']);
        $point->votingOptions()->sync($validated['available_options']);

        return to_route('councils.show', $council)->with('success', 'Punto del consejo actualizado correctamente.');
    }

    /**
     * Elimina un punto de consejo.
     *
     * @param  Council      $council
     * @param  CouncilPoint $point
     * @return RedirectResponse
     */
    public function destroy(CouncilPoint $point): RedirectResponse
    {
        $councilCode = $point->council->code;
        $point->delete();

        return to_route('councils.show', $councilCode)->with('success', 'Punto del consejo eliminado correctamente.');
    }
}