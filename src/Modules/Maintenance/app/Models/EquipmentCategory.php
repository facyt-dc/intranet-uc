<?php

namespace Modules\Maintenance\Models;

use Illuminate\Database\Eloquent\Model;

class EquipmentCategory extends Model
{
    protected $fillable = ['name'];

    public function equipments()
    {
        return $this->hasMany(Equipment::class);
    }
}
