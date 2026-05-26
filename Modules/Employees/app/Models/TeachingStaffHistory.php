<?php

namespace Modules\Employees\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Modules\Employees\Models\Staff;
use Modules\Employees\Models\Employee;
use Modules\Employees\Models\TeachingLevel;

class TeachingStaffHistory extends Model
{
    use HasFactory;

    protected $table = "teaching_staff_histories";

    public function staff():BelongsTo
    {
        return $this->belongsTo(Staff::class,"staff");
    }

    public function employee():BelongsTo
    {
        return $this->belongsTo(Employee::class,"employee");
    }

    public function teaching_level():BelongsTo
    {
        return $this->belongsTo(TeachingLevel::class,"teaching_level");
    }

}
