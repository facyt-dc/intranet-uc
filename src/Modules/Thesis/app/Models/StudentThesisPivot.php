<?php

namespace Modules\Thesis\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentThesisPivot extends Model
{
    use HasFactory;

    protected $table = 'student_thesis_pivot';

    protected $fillable = [
        'student_id',
        'thesis_id',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(ThesisStudent::class, 'student_id');
    }

    public function thesis(): BelongsTo
    {
        return $this->belongsTo(Thesis::class, 'thesis_id');
    }
}