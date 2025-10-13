<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Importar BelongsTo
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\StudentStatus;
class ThesisStudent extends Model
{
    use HasFactory;

    protected $table = 'thesis_student';

    protected $fillable = [
        'id_uc',
        'name',
        'ci',
        'email',
        'status_id'
    ];

    /**
     * Un estudiante de tesis pertenece a un estado.
     */
    public function status(): BelongsTo
    {
    return $this->belongsTo(StudentStatus::class, 'status_id');
    }


    public function theses()
    {
        return $this->belongsToMany(Thesis::class, 'student_thesis_pivot', 'student_id', 'thesis_id');
    }

    public function currentThesis()
    {
        return $this->hasOne(Thesis::class)->where('is_active', true);
    }

    public function pastTheses()
    {
        return $this->hasMany(Thesis::class)->where('is_active', false);
    }

    public function statusHistory(): HasMany
    {
        // Ordenamos por fecha para que el más reciente esté al final
        return $this->hasMany(StudentStatusHistory::class, 'thesis_student_id')->orderBy('start_date', 'asc');
    }
    // public function thesis(): BelongsTo
    // {
    //     return $this->belongsTo(Thesis::class, 'thesis_id');
    // }

    // ... otras relaciones (si las tiene)
}