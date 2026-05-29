<?php

namespace Modules\Maintenance\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Maintenance\Models\MaintenanceRequest;

class MaintenanceRequesetStageUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $maintenanceRequest;

    public function __construct(MaintenanceRequest $maintenanceRequest)
    {
        $this->maintenanceRequest = $maintenanceRequest;
    }

    public function broadcastOn(): array
    {
        return [new Channel('maintenance')];
    }
}
