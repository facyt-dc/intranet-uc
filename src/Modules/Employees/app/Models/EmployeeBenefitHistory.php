<?php

namespace Modules\Employees\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Employees\Models\Employee;
use Modules\Employees\Models\Benefit;

class EmployeeBenefitHistory extends Model
{
    use HasFactory;

    protected $table = "employee_benefit_histories";
    protected $fillable = [
        'employee',
        'benefit',
        'request_date',
        'approvement_date',
        'start_date',
        'end_date',
        'state'
    ];

    public function employee():BelongsTo
    {
        return $this->belongsTo(Employee::class,'employee');
    }

    public function benefit():BelongsTo
    {
        return $this->belongsTo(Benefit::class,'benefit');
    }
}
