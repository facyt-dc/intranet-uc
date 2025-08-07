<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Representa una opción de votación global y reutilizable.
 *
 * Estas opciones (ej. 'Aprobado', 'Reprobado') se crean en una ventana
 * de configuración y luego se asignan a los puntos específicos que
*  se van a votar.
 *
 * @property int $id
 * @property string $name Nombre de la opción de votación (ej. 'A favor').
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class VotingOption extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    /**
     * Obtiene todos los Puntos que utilizan esta opción de votación.
     *
     * La relación se define a través de la tabla pivote 'point_voting_option'.
     *
     * @return BelongsToMany
     */
    public function points(): BelongsToMany
    {
        return $this->belongsToMany(Point::class, 'point_voting_option');
    }
}
