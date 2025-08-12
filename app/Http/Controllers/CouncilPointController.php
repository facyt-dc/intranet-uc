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
        // 1. Validación de los datos de entrada
        $validated = $request->validate([
            'description' => 'required|string',
            'requested_by_user_id' => [
                'required',
                'integer',
                // Regla avanzada: asegura que el usuario solicitante sea un participante del consejo.
                Rule::exists('council_user', 'user_id')->where('council_id', $council->id),
            ],
            'min_votes_to_close' => 'required|integer|min:1',
            'order' => 'nullable|integer',
            'votable_users' => 'required|array|min:1',
            'votable_users.*' => [
                'required',
                'integer',
                // Regla avanzada: asegura que todos los votantes asignados sean participantes del consejo.
                Rule::exists('council_user', 'user_id')->where('council_id', $council->id),
            ],
            'available_options' => 'required|array|min:1',
            'available_options.*' => 'required|integer|exists:voting_options,id',
        ]);

        // 2. Creación del punto del consejo, asociándolo directamente al consejo padre
        $point = $council->points()->create([
            'description' => $validated['description'],
            'requested_by_user_id' => $validated['requested_by_user_id'],
            'min_votes_to_close' => $validated['min_votes_to_close'],
            'order' => $validated['order'] ?? 0,
            'status' => 'Abierto para Votación', // Estado por defecto
        ]);

        // 3. Sincronización de las relaciones "muchos a muchos"
        $point->votableUsers()->sync($validated['votable_users']);
        $point->availableOptions()->sync($validated['available_options']);

        // 4. Redirección a la vista del consejo con un mensaje de éxito
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
    public function update(Request $request, Council $council, CouncilPoint $point): RedirectResponse
    {
        // Laravel por defecto se asegura de que el {point} pertenezca al {council}
        // si se definen las rutas como recursos anidados.

        // 1. Validación (idéntica a la de 'store')
        $validated = $request->validate([
            'description' => 'required|string',
            'requested_by_user_id' => [
                'required',
                'integer',
                Rule::exists('council_user', 'user_id')->where('council_id', $council->id),
            ],
            'min_votes_to_close' => 'required|integer|min:1',
            'order' => 'nullable|integer',
            'votable_users' => 'required|array|min:1',
            'votable_users.*' => ['required', 'integer', Rule::exists('council_user', 'user_id')->where('council_id', $council->id)],
            'available_options' => 'required|array|min:1',
            'available_options.*' => 'required|integer|exists:voting_options,id',
        ]);
        
        // 2. Actualización de los datos principales del punto
        $point->update([
            'description' => $validated['description'],
            'requested_by_user_id' => $validated['requested_by_user_id'],
            'min_votes_to_close' => $validated['min_votes_to_close'],
            'order' => $validated['order'] ?? $point->order,
        ]);
        
        // 3. Resincronización de las relaciones
        $point->votableUsers()->sync($validated['votable_users']);
        $point->availableOptions()->sync($validated['available_options']);

        return to_route('councils.show', $council)->with('success', 'Punto del consejo actualizado correctamente.');
    }

    /**
     * Elimina un punto de consejo.
     *
     * @param  Council      $council
     * @param  CouncilPoint $point
     * @return RedirectResponse
     */
    public function destroy(Council $council, CouncilPoint $point): RedirectResponse
    {
        // Las restricciones de la base de datos (onDelete('cascade')) se encargarán de
        // limpiar votos, permisos de voto y opciones de voto asociados a este punto.
        $point->delete();

        return to_route('councils.show', $council)->with('success', 'Punto del consejo eliminado correctamente.');
    }
}