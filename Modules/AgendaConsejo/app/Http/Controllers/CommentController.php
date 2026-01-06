<?php

namespace Modules\AgendaConsejo\Http\Controllers;

use Modules\AgendaConsejo\Models\AgendaPoint;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * Almacena un nuevo comentario para un punto de agenda específico.
     *
     * @param  Request      $request
     * @param  AgendaPoint $point   El punto que se está comentando (inyectado por Route Model Binding).
     * @return RedirectResponse
     */
    public function store(Request $request, AgendaPoint $point): RedirectResponse
    {
        // 1. Obtener el usuario autenticado
        $user = Auth::user();

        // 2. Cláusula de Guarda: Verificar si la agenda está cerrado.
        // No se deberían permitir nuevos comentarios si el acta ya se cerró.
        if ($point->agenda->status === 'Cerrado') {
            return back()->with('error', 'No se pueden añadir comentarios a una agenda cerrado.');
        }

        // 3. Cláusula de Guarda: Autorización específica del Agenda.
        // Aunque el usuario tenga el rol global de 'Consejero', debemos verificar
        // si realmente es un participante invitado a ESTa agenda en particular.
        // También permitimos que el Director de la agenda pueda comentar.
        $isParticipant = $point->agenda->participants()->where('users.id', $user->id)->exists();
        $isDirector = $point->agenda->director_id === $user->id;

        if (! $isParticipant && ! $isDirector) {
            // Si no es ni participante ni director, no tiene permiso para estar aquí.
            abort(403, 'No tienes permiso para participar en esta agenda.');
        }

        // 4. Validación de los datos de entrada.
        $validated = $request->validate([
            'body' => 'required|string|max:1000', // Limitamos la longitud para evitar abusos.
        ], [
            'body.max' => 'El comentario no puede exceder los 1000 caracteres.',
        ]);

        // 5. Crear el comentario usando la relación.
        // Eloquent asignará automáticamente el 'agenda_point_id'.
        $point->comments()->create([
            'user_id' => $user->id,
            'body' => $validated['body'],
        ]);

        // 6. Redirigir de vuelta a la misma página (preservando la posición del scroll en el frontend).
        return back()->with('success', 'Comentario publicado correctamente.');
    }
}