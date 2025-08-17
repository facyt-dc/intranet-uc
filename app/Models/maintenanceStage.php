<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceStage extends Model
{
    use HasFactory;
    
    // Es una buena práctica definir qué campos se pueden llenar
    protected $fillable = ['name', 'sequence', 'is_final_stage'];

    function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class, 'maintenanceStage_id');
    }
}
