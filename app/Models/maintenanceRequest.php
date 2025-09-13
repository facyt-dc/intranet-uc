<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];
    protected $casts = [
        'completion_date' => 'datetime',
    ];
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
        return $this->hasOne(maintenanceStage::class,'id', 'stage_id');
    }
    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }
    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'equipment_id' );
    }
}
