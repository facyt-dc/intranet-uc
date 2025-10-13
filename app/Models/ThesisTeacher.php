<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;



class ThesisTeacher extends Model
{
    use HasFactory;

    // Nombre de la tabla explícito
    protected $table = 'thesis_teachers';

    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'id_uc',
        'name',
        'ci',
        'email',
    ];

    /**
     * Un docente (tutor) puede estar asociado a muchos proyectos de tesis.
     */
    public function theses()
    {
        // La relación es a través de la tabla 'teacher_thesis_pivot'
        return $this->belongsToMany(Thesis::class, 'teacher_thesis_pivot', 'teacher_id', 'thesis_id');
    }
}