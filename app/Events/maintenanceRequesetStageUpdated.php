<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class maintenanceRequesetStageUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $incidencia;

    public function __construct(Incidencia $incidencia)
    {
        $this->incidencia = $incidencia;
    }

    public function broadcastOn(): array
    {
        // Cualquiera puede escuchar en este canal público
        return [new Channel('maintenance')];
    }
}
