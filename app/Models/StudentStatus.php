<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // Importar HasMany

class StudentStatus extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
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