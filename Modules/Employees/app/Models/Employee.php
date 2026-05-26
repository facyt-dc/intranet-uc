<?php

namespace Modules\Employees\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employee extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return \Modules\Employees\Database\Factories\EmployeeFactory::new();
    }

    protected $table = "employees";
    protected $fillable = [
        'name',
        'staff',
        'teaching_level',
        'address',
        'phone',
        'cedula',
        'lastname',
        'email',
        'birthday'
    ];

    protected function name():Attribute
    {
        return Attribute::make(
            get: fn(string $value) => ucfirst($value),
            set: fn(string $value) => ucfirst($value)
        );
    }

    protected function cedula():Attribute
    {
        return Attribute::make(
            get: fn(string $value) => ucfirst($value),
            set: fn(string $value) => ucfirst($value)
        );
    }

    protected function address():Attribute
    {
        return Attribute::make(
            get: fn(string $value) => ucfirst($value),
            set: fn(string $value) => ucfirst($value)
        );
    }

    protected function lastname():Attribute
    {
        return Attribute::make(
            get: fn(string $value) => ucfirst($value),
            set: fn(string $value) => ucfirst($value)
        );
    }

    protected function email():Attribute
    {
        return Attribute::make(
            get: fn(string $value) => ucfirst($value),
            set: fn(string $value) => ucfirst($value)
        );
    }

    protected function phone():Attribute
    {
        return Attribute::make(
            get: fn(string $value) => ucfirst($value),
            set: fn(string $value) => ucfirst($value)
        );
    }

    public function staff():BelongsTo
    {
        return $this->belongsTo(Staff::class,"staff");
    }

    public function teaching_level():BelongsTo
    {
        return $this->belongsTo(TeachingLevel::class,"teaching_level");
    }



}
