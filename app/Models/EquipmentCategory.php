<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipmentCategory extends Model
{
        protected $fillable = ['name'];

    /**
     * Una categoría puede tener muchos equipos.
     */
    public function equipments()
    {
        return $this->hasMany(Equipment::class);
    }
}
