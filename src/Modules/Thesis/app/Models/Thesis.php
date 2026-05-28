<?php

namespace Modules\Thesis\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Thesis extends Model
{
    use HasFactory;

    protected $table = 'thesis';

    protected $fillable = [
        'title',
        'date',
        'is_active',
    ];

    /**
     * Una tesis puede tener muchos estudiantes.
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(ThesisStudent::class, 'student_thesis_pivot', 'thesis_id', 'student_id');
    }

    /**
     * Un proyecto de tesis puede tener muchos docentes (tutores).
     */
    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(ThesisTeacher::class, 'teacher_thesis_pivot', 'thesis_id', 'teacher_id');
    }

    /**
     * Una tesis puede tener muchos archivos asociados.
     */
    public function files(): HasMany
    {
        return $this->hasMany(ThesisFile::class);
    }

    protected static function newFactory()
    {
        return \Modules\Thesis\Database\Factories\ThesisFactory::new();
    }
}