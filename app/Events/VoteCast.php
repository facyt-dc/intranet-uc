<?php

namespace App\Events;

use App\Models\Vote;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VoteCast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * El modelo del voto que acaba de ser creado.
     *
     * @var \App\Models\Vote
     */
    public $vote;

    /**
     * Crea una nueva instancia del evento.
     *
     * @param  \App\Models\Vote $vote El nuevo voto que se ha guardado en la base de datos.
     * @return void
     */
    public function __construct(Vote $vote)
    {
        $this->vote = $vote;
    }

    /**
     * Obtiene los canales en los que el evento debería ser transmitido.
     *
     * Este método solo es necesario si se implementa la interfaz ShouldBroadcast
     * para notificaciones en tiempo real (websockets).
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('councils.' . $this->vote->point->council_id);
    }
}
