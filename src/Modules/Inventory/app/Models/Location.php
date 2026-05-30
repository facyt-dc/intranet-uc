<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Inventory\Database\Factories\LocationFactory;

class Location extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'description'];

    protected static function newFactory(): LocationFactory
    {
        return LocationFactory::new();
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'current_location_id');
    }
}
