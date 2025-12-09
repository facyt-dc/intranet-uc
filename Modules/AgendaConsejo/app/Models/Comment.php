<?php

namespace Modules\AgendaConsejo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use app\Models\User;

/**
 * Representa un comentario realizado por un usuario en un punto del día.
 *
 * @property int $id
 * @property int $user_id ID del usuario que hizo el comentario.
 * @property int $agenda_point_id ID del punto del día al que pertenece el comentario.
 * @property string $body Contenido del comentario.
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Comment extends Model
{
    protected $fillable = [
        'user_id',
        'agenda_point_id',
        'body',
    ];

    /**
     * Obtiene el usuario que hizo el comentario.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtiene el punto del día al que pertenece el comentario.
     *
     * @return BelongsTo
     */
    public function agendaPoint(): BelongsTo
    {
        return $this->belongsTo(AgendaPoint::class);
    }
}
