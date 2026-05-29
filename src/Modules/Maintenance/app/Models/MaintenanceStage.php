<?php

namespace Modules\Maintenance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Maintenance\Database\Factories\MaintenanceStageFactory;

class MaintenanceStage extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'sequence', 'is_final_stage'];

    protected static function newFactory()
    {
        return MaintenanceStageFactory::new();
    }

    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class, 'stage_id');
    }
}
