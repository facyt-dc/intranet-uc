<?php

namespace Modules\Thesis\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentStatusHistory extends Model
{
    use HasFactory;

    protected $table = 'student_status_history';

    protected $fillable = [
        'thesis_student_id',
        'student_status_id',
        'start_date',
    ];

    protected $casts = [
        'start_date' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(ThesisStudent::class, 'thesis_student_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(StudentStatus::class, 'student_status_id');
    }
}