<?php

namespace Modules\Employees\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class TimeUnit extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return \Modules\Employees\Database\Factories\TimeUnitFactory::new();
    }

    protected $table = "time_units";

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => ucfirst($value),
            set: fn (string $value) => ucfirst($value),
        );
    }
}
