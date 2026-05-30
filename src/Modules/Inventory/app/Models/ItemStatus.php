<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Inventory\Database\Factories\ItemStatusFactory;

class ItemStatus extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'description', 'is_operational'];

    protected static function newFactory(): ItemStatusFactory
    {
        return ItemStatusFactory::new();
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'current_status_id');
    }
}
