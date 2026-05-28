<?php

namespace Modules\AgendaConsejo\Http\Controllers;

use Modules\AgendaConsejo\Models\Agenda;
use Modules\AgendaConsejo\Models\AgendaPoint;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AgendaPointController extends Controller
{
    /**
     * Almacena un nuevo punto de consejo asociado a un consejo existente.
     *
     * @param  Request $request
     * @param  Agenda $agenda El consejo padre, inyectado por Route Model Binding.
     * @return RedirectResponse
     */
    public function store(Request $request, Agenda $agenda): RedirectResponse
    {
        if ($agenda->status === 'Cerrado') {
            // Si está cerrado, redirigimos con un error.
            return back()->with('error', 'No se pueden añadir puntos a un consejo que ya ha sido cerrado.');
        }

        // Contamos cuántos usuarios votantes se seleccionaron en el request.
        $numberOfVotableUsers = count($request->input('votable_users', []));

        // Validación de los datos de entrada
        $validated = $request->validate([
            'description' => 'required|string',
            'requested_by_user_id' => [
                'required',
                'integer',
                // Regla: asegura que el usuario solicitante sea un participante del consejo.
                Rule::exists('agenda_user', 'user_id')->where('agenda_id', $agenda->id),
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
                Rule::exists('agenda_user', 'user_id')->where('agenda_id', $agenda->id),
            ],
            'available_options' => 'required|array|min:1',
            'available_options.*' => 'required|integer|exists:voting_options,id',
        ], [
            // Mensaje de error personalizado para esta regla
            'min_votes_to_close.max' => 'El mínimo de votos no puede ser mayor que el número de votantes seleccionados.'
        ]);

        // Creación del punto del consejo, asociándolo directamente al consejo padre
        $point = $agenda->points()->create([
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
        return to_route('agendas.show', $agenda)->with('success', 'Punto del consejo añadido correctamente.');
    }

    /**
     * Actualiza un punto de consejo existente.
     *
     * @param  Request      $request
     * @param  Agenda      $agenda El consejo padre
     * @param  AgendaPoint $point   El punto a actualizar
     * @return RedirectResponse
     */
    public function update(Request $request, AgendaPoint $point): RedirectResponse
    {
        $agenda = $point->agenda;

        if ($point->votes()->exists()) {
            return back()->with('error', 'No se puede editar un punto que ya tiene votos.');
        }

        if ($agenda->status === 'Cerrado') {
            return back()->with('error', 'No se pueden editar puntos de un consejo que ya ha sido cerrado.');
        }

        $numberOfVotableUsers = count($request->input('votable_users', []));
        // 1. Validación (idéntica a la de 'store')
        $validated = $request->validate([
            'description' => 'required|string',
            'requested_by_user_id' => [
                'required',
                'integer',
                Rule::exists('agenda_user', 'user_id')->where('agenda_id', $agenda->id),
            ],
            'min_votes_to_close' => [
                'required',
                'integer',
                'min:1',
                'max:' . $numberOfVotableUsers
            ],
            'order' => 'nullable|integer',
            'votable_users' => 'required|array|min:1',
            'votable_users.*' => ['required', 'integer', Rule::exists('agenda_user', 'user_id')->where('agenda_id', $agenda->id)],
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

        return to_route('agendas.show', $agenda)->with('success', 'Punto del consejo actualizado correctamente.');
    }

    /**
     * Elimina un punto de consejo.
     *
     * @param  Agenda      $agenda
     * @param  AgendaPoint $point
     * @return RedirectResponse
     */
    public function destroy(AgendaPoint $point): RedirectResponse
    {
        $agendaCode = $point->agenda->code;
        $point->delete();

        return to_route('agendas.show', $agendaCode)->with('success', 'Punto del consejo eliminado correctamente.');
    }

    /**
     * Añade o actualiza la conclusión de un punto de consejo.
     * Este es el nuevo método para manejar específicamente la conclusión.
     *
     * @param  Request      $request
     * @param  AgendaPoint $point
     * @return RedirectResponse
     */
    public function addConclusion(Request $request, AgendaPoint $point): RedirectResponse
    {
        // 1. Cláusula de guarda: Verificar que el consejo no esté cerrado
        if ($point->agenda->status === 'Cerrado') {
            return back()->with('error', 'No se puede añadir una conclusión a un punto de un consejo cerrado.');
        }

        // 2. Cláusula de guarda: Verificar que se haya alcanzado el mínimo de votos
        if ($point->votes()->count() < $point->min_votes_to_close) {
            return back()->with('error', 'No se puede añadir una conclusión hasta que se alcance el mínimo de votos.');
        }

        // 3. Validar los datos de entrada
        $validated = $request->validate([
            'conclusion' => 'required|string',
        ]);

        // 4. Actualizar únicamente el campo 'conclusion'
        $point->update([
            'conclusion' => $validated['conclusion'],
        ]);

        // 5. Redirigir de vuelta con un mensaje de éxito
        return back()->with('success', 'Conclusión guardada correctamente.');
    }
}