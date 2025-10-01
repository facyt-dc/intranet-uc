<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItemStatus extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'description', 'is_operational'];

    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'current_status_id');
    }
}
