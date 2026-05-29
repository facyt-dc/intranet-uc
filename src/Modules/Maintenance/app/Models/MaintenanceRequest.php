<?php

namespace Modules\Maintenance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Modules\Maintenance\Database\Factories\MaintenanceRequestFactory;

class MaintenanceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'type',
        'stage_id',
        'user_id',
        'technician_id',
        'equipment_id',
        'completion_date',
        'duration',
        'is_archived',
    ];

    protected $casts = [
        'completion_date' => 'datetime',
        'is_archived' => 'boolean',
    ];

    protected static function newFactory()
    {
        return MaintenanceRequestFactory::new();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function stage()
    {
        return $this->hasOne(MaintenanceStage::class, 'id', 'stage_id');
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }
}
