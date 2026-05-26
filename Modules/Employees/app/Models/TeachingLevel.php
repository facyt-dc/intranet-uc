<?php

namespace Modules\Employees\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Employees\Models\TimeUnit;

class TeachingLevel extends Model
{
    use HasFactory;

    protected $table = "teaching_levels";

    protected $fillable = [
        "name",
        "time_unit",
        "time",
        "previous_level"
    ];

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => ucfirst($value),
            set: fn (string $value) => ucfirst($value),
        );
    }

    public function time_unit():BelongsTo
    {
        return $this->belongsTo(TimeUnit::class,"time_unit");
    }

    public function previous_level():BelongsTo
    {
        return $this->belongsTo(TeachingLevel::class,"previous_level");
    }

    protected function time():Attribute
    {
        return Attribute::make(
            get: fn (int $value) => abs($value),
            set: fn (int $value) => abs($value)
        );
    }


}
