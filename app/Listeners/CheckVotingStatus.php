<?php

namespace App\Listeners;

use App\Events\VoteCast;
use App\Notifications\AllPointsReadyForClosure;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

/**
 * Este Listener se ejecuta cada vez que se dispara el evento VoteCast.
 * Implementa ShouldQueue para que su lógica se ejecute en segundo plano
 * en una cola de trabajos, sin hacer esperar al usuario que acaba de votar.
 */
class CheckVotingStatus implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Crea el listener de eventos.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Maneja el evento.
     *
     * @param  \App\Events\VoteCast  $event
     * @return void
     */
    public function handle(VoteCast $event): void
    {
        // 1. Obtener el consejo y cargar las relaciones necesarias eficientemente
        // para evitar el problema N+1.
        $agenda = $event->vote->point->agenda()->with(['points.votes', 'director'])->first();

        // 2. Comprobar si ya se envió una notificación para este consejo.
        // Esto previene el envío de notificaciones duplicadas si se emiten más
        // votos después de que el consejo ya esté listo.
        if ($agenda->closure_notification_sent) {
            return; // No hacer nada más.
        }

        // 3. Iterar sobre todos los puntos del consejo para verificar su estado.
        $allPointsAreReady = true;
        foreach ($agenda->points as $point) {
            // Comparamos el número de votos emitidos (ya cargados) con el mínimo requerido.
            if ($point->votes->count() < $point->min_votes_to_close) {
                $allPointsAreReady = false; // Se encontró un punto que aún no está listo.
                break; // Salimos del bucle, no es necesario seguir comprobando.
            }
        }

        // 4. Si todos los puntos están listos, notificar al director.
        if ($allPointsAreReady) {
            
            // Enviamos la notificación al director del consejo.
            Notification::send($agenda->director, new AllPointsReadyForClosure($agenda));

            // 5. MARCAR el consejo como notificado. Este es un paso CRÍTICO.
            $agenda->update(['closure_notification_sent' => true]);
        }
    }
}
