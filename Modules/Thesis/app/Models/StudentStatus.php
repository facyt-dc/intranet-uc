<?php

namespace Modules\Thesis\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * El historial de estudiantes que han tenido este estado.
     */
    public function statusHistory(): HasMany
    {
        return $this->hasMany(StudentStatusHistory::class, 'student_status_id');
    }
}