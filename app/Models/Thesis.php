<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Thesis extends Model
{
    use HasFactory;

    protected $table = 'thesis';

    // ... fillable, etc.
    protected $fillable = [
            'title',
            'date',

        ];
 

    /**
     * 
     * Una tesis puede tener muchos estudiantes.
     */
    public function students()
    {
        return $this->belongsToMany(ThesisStudent::class, 'student_thesis_pivot', 'thesis_id', 'student_id');
    }

    /**
     * Un proyecto de tesis puede tener muchos docentes (tutores).
     */
    public function teachers()
    {
        return $this->belongsToMany(ThesisTeacher::class, 'teacher_thesis_pivot', 'thesis_id', 'teacher_id');
    }
    
    /**
     * 
     * Una tesis puede tener muchos archivos asociados.
     */
    public function files()
    {
        // El nombre de la clase del modelo de archivos
        return $this->hasMany(ThesisFile::class); 
    }
}