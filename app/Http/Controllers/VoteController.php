<?php

namespace App\Http\Controllers;

use App\Events\VoteCast;
use App\Models\AgendaPoint;
use App\Models\Vote;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class VoteController extends Controller
{
    /**
     * Almacena un nuevo voto emitido por un consejero para un punto específico.
     *
     * @param  Request      $request
     * @param  AgendaPoint $point   El punto que se está votando, inyectado vía Route Model Binding.
     * @return RedirectResponse
     */
    public function store(Request $request, AgendaPoint $point): RedirectResponse
    {
        // --- INICIO DE VALIDACIONES Y AUTORIZACIÓN ---

        // 1. Validar que el consejo no esté cerrado
        if ($point->agenda->status === 'Cerrado') {
            return back()->with('error', 'No se puede votar, este consejo ya ha sido cerrado.');
        }

        // 2. Validar que el usuario actual tiene permiso para votar en ESTE punto específico.
        // Se hace una consulta directa a la tabla pivote para mayor eficiencia.
        $canVote = $point->votableUsers()->where('user_id', Auth::id())->exists();
        if (! $canVote) {
            // Abortar con 403 es semánticamente correcto para fallos de autorización.
            abort(403, 'No tiene permiso para votar en este punto.');
        }

        // 3. Validar los datos de la petición.
        $validated = $request->validate([
            'voting_option_id' => [
                'required',
                'integer',
                // La opción de voto debe existir y debe ser una de las opciones
                // disponibles específicamente para este punto.
                Rule::exists('agenda_point_voting_option', 'voting_option_id')
                    ->where('agenda_point_id', $point->id),
            ],
        ]);

        // --- FIN DE VALIDACIONES Y AUTORIZACIÓN ---


        // --- LÓGICA DE NEGOCIO ---

        // Se intenta crear el voto. Se usa un bloque try-catch para manejar el caso
        // de que un usuario intente votar dos veces. La restricción UNIQUE en la
        // base de datos lanzará una QueryException, que capturamos para dar
        // un mensaje de error amigable.
        try {
            $vote = Vote::create([
                'agenda_point_id' => $point->id,
                'user_id' => Auth::id(),
                'voting_option_id' => $validated['voting_option_id'],
            ]);

            // Disparar un evento para manejar lógica secundaria (como notificaciones)
            // de forma desacoplada y limpia.
            VoteCast::dispatch($vote);

        } catch (QueryException $e) {
            // El código de error '23000' es el estándar SQL para violaciones de integridad,
            // como una clave única duplicada.
            if ($e->getCode() === '23000') {
                return back()->with('error', 'Usted ya ha emitido un voto para este punto.');
            }
            // Si es otro error de base de datos, lo relanzamos.
            throw $e;
        }

        // --- RESPUESTA ---

        // Se redirige de vuelta a la página del consejo con un mensaje de éxito.
        return to_route('agendas.show', $point->agenda)
               ->with('success', 'Voto emitido correctamente.');
    }
}