<?php

namespace Modules\Thesis\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ThesisStudent extends Model
{
    use HasFactory;

    protected $table = 'thesis_student';

    protected $fillable = [
        'id_uc',
        'name',
        'ci',
        'email',
        'status_id',
    ];

    /**
     * Un estudiante de tesis pertenece a un estado.
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(StudentStatus::class, 'status_id');
    }

    public function theses(): BelongsToMany
    {
        return $this->belongsToMany(Thesis::class, 'student_thesis_pivot', 'student_id', 'thesis_id');
    }

    public function currentThesis(): HasOne
    {
        return $this->hasOne(Thesis::class)->where('is_active', true);
    }

    public function pastTheses(): HasMany
    {
        return $this->hasMany(Thesis::class)->where('is_active', false);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(StudentStatusHistory::class, 'thesis_student_id')->orderBy('start_date', 'asc');
    }

    protected static function newFactory()
    {
        return \Modules\Thesis\Database\Factories\ThesisStudentFactory::new();
    }
}