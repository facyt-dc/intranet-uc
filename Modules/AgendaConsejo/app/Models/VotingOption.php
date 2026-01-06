<?php

namespace Modules\AgendaConsejo\Models;

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

    protected $table = 'voting_options';

    protected $fillable = [
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Obtiene los puntos de consejo que utilizan esta opción de votación.
     *
     * Esta relación permite saber en qué puntos se ha ofrecido esta opción específica,
     * aunque su uso principal será a la inversa (desde AgendaPoint para ver sus opciones).
     *
     * @return BelongsToMany
     */
    public function points(): BelongsToMany
    {
        return $this->belongsToMany(AgendaPoint::class, 'agenda_point_voting_option');
    }
}
