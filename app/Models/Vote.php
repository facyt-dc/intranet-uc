<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Representa un voto individual emitido por un usuario en un punto específico.
 *
 * Cada registro en esta tabla es la evidencia de una acción de votación,
 * vinculando a un usuario, un punto y la opción de votación seleccionada.
 *
 * @property int $id
 * @property int $user_id ID del usuario (Consejero) que emitió el voto.
 * @property int $point_id ID del punto por el cual se votó.
 * @property int $voting_option_id ID de la opción de votación seleccionada.
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Vote extends Model
{
    use HasFactory;

    protected $fillable = [
        'council_point_id',
        'user_id',
        'voting_option_id',
    ];

    /**
     * Obtiene el punto del consejo al que pertenece este voto.
     *
     * @return BelongsTo
     */
    public function point(): BelongsTo
    {
        return $this->belongsTo(CouncilPoint::class, 'council_point_id');
    }

    /**
     * Obtiene el Usuario (Consejero) que emitió este voto.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtiene la opción de votación que fue seleccionada en este voto.
     *
     * @return BelongsTo
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(VotingOption::class, 'voting_option_id');
    }
}
