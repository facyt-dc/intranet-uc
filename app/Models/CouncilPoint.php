<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Representa un punto del día a discutir y votar en un Consejo.
 *
 * Cada punto tiene una descripción, un estado, y una lista específica de
 * consejeros que están autorizados para votar en él.
 *
 * @property int $id
 * @property int $council_id ID del consejo al que pertenece.
 * @property string $requesting_counselor Nombre del consejero que solicitó el punto.
 * @property string $description Texto que detalla el punto a tratar.
 * @property string $status Estado del punto (ej. 'Pendiente', 'Abierto para Votación', 'Cerrado').
 * @property int $min_votes_to_close Número mínimo de votos para poder cerrar la votación.
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class CouncilPoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'council_id',
        'description',
        'requested_by_user_id',
        'status',
        'min_votes_to_close',
        'order',
    ];

    protected $casts = [
        'min_votes_to_close' => 'integer',
        'order' => 'integer',
    ];

    /**
     * Obtiene el Consejo al que pertenece este punto.
     *
     * @return BelongsTo
     */
    public function council(): BelongsTo
    {
        return $this->belongsTo(Council::class);
    }

    /**
     * Obtiene todos los votos emitidos para este punto.
     *
     * @return HasMany
     */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    /**
     * Obtiene la colección de usuarios (Consejeros) que tienen permiso para votar en este punto específico.
     *
     * @return BelongsToMany
     */
    public function votableUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'council_point_votable_user');
    }

    /**
     * Obtiene las opciones de votación configuradas para este punto.
     * La relación se define a través de la tabla pivote 'point_voting_option'.
     *
     * @return BelongsToMany
     */
    public function votingOptions(): BelongsToMany
    {
        return $this->belongsToMany(VotingOption::class, 'council_point_voting_option');
    }

    /**
     * Obtiene el usuario (Consejero) que solicitó la inclusión de este punto.
     *
     * @return BelongsTo
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }
}
