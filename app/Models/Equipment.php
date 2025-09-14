<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
     use HasFactory;

    protected $fillable = [
        'name',
        'brand',
        'model',
        'serial_number',
        'description',
        'equipment_category_id',
        'last_maintained_at',
        'next_maintenance_at',
        'maintenance_frequency',
        'maintenance_interval',
        'last_failure_at',
        'mtbf',
        'mttr',
    ];

    protected $casts = [
        'last_maintained_at' => 'date',
        'next_maintenance_at' => 'date',
        'last_failure_at' => 'date',
    ];

    /**
     * Un equipo puede tener muchas solicitudes de mantenimiento.
     */
    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class);
    }
    public function category()
    {
        return $this->belongsTo(EquipmentCategory::class, 'equipment_category_id');
    }
}
