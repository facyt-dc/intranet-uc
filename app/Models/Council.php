<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Council extends Model
{
    use HasFactory;
    
     protected $fillable = [
        'code',
        'name',
        'date',
        'status',
        'director_id',
        'closed_at',
    ];

    protected $casts = [
        'date' => 'datetime',
        'closed_at' => 'datetime',
    ];

    /**
     * El "método de arranque" del modelo.
     * Se utiliza aquí para asignar automáticamente el código del consejo.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        /**
         * Escucha el evento 'creating' para generar automáticamente el código del consejo
         * antes de que se guarde en la base de datos.
         * El formato del código es 'NNN_YYYY'.
         */
        static::creating(function ($council) {
            // Si el código ya está establecido por alguna razón, no hacer nada.
            if ($council->code) {
                return;
            }

            $currentYear = Carbon::now()->year;
            
            // Contar cuántos consejos ya existen para el año actual para determinar el siguiente número.
            $lastCouncilCount = self::whereYear('created_at', $currentYear)->count();
            
            // El nuevo número será el conteo actual + 1.
            $newCouncilNumber = $lastCouncilCount + 1;
            
            // Formatear el número a 3 dígitos con ceros a la izquierda (001, 002, ..., 010, ..., 100).
            $formattedNumber = str_pad($newCouncilNumber, 3, '0', STR_PAD_LEFT);
            
            $council->code = "{$formattedNumber}_{$currentYear}";
        });
    }

    /**
     * Obtiene la clave de la ruta para el modelo.
     * Esto permite usar el campo 'code' en lugar del 'id' en las URLs (Route Model Binding).
     * Por ejemplo: /councils/001_2025
     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'code';
    }

    /**
     * Obtiene el usuario (Director) que creó y organiza el consejo.
     *
     * @return BelongsTo
     */
    public function director(): BelongsTo
    {
        return $this->belongsTo(User::class, 'director_id');
    }

    /**
     * Obtiene todos los usuarios (Consejeros) que participan en este consejo.
     *
     * @return BelongsToMany
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'council_user');
    }

    /**
     * Obtiene todos los puntos a discutir en este consejo.
     *
     * @return HasMany
     */
    public function points(): HasMany
    {
        return $this->hasMany(CouncilPoint::class);
    }
}
