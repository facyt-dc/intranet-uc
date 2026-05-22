<?php

namespace Modules\Thesis\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ThesisTeacher extends Model
{
    use HasFactory;

    protected $table = 'thesis_teachers';

    protected $fillable = [
        'id_uc',
        'name',
        'ci',
        'email',
    ];

    /**
     * Un docente (tutor) puede estar asociado a muchos proyectos de tesis.
     */
    public function theses(): BelongsToMany
    {
        return $this->belongsToMany(Thesis::class, 'teacher_thesis_pivot', 'teacher_id', 'thesis_id');
    }

    protected static function newFactory()
    {
        return \Modules\Thesis\Database\Factories\ThesisTeacherFactory::new();
    }
}