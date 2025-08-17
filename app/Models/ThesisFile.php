<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThesisFile extends Model
{
    use HasFactory;

    // Especifica el nombre de la tabla si no sigue la convención de Laravel
    protected $table = 'thesis_files';

    protected $fillable = [
        'thesis_id',
        'type',
        'original_name',
        'path'
    ];

    /**
     * Un archivo pertenece a una tesis.
     */
    public function thesis()
    {
        return $this->belongsTo(Thesis::class);
    }
}