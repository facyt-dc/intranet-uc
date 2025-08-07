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

    public const STATUS_SCHEDULED = 'Agendado';
    public const STATUS_IN_PROGRESS = 'En Progreso';
    public const STATUS_CLOSED = 'Cerrado';
    
    protected $fillable = [
        'name',
        'director_id',
        'scheduled_at',
        'status',
        'closed_at',
    ];

    /**
     * El "método de arranque" del modelo.
     * Se utiliza aquí para asignar automáticamente el código del consejo.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        /**
         * Se ejecuta antes de que un nuevo consejo sea insertado en la base de datos.
         * Genera un código secuencial basado en el año actual.
         * Ejemplo: 001_2025, 002_2025, 001_2026, etc.
         */
        static::creating(function (self $council) {
            // Si el código ya ha sido establecido manualmente, no hacer nada.
            if ($council->code) {
                return;
            }

            $year = now()->year;

            // Busca el último consejo creado en el mismo año para determinar el siguiente número.
            $lastCouncilThisYear = self::whereYear('created_at', $year)
                ->orderBy('id', 'desc')
                ->first();

            $nextNumber = 1;
            if ($lastCouncilThisYear) {
                // Extrae el número del código del último consejo y le suma 1.
                // substr extrae los 3 primeros caracteres del código (ej: '001').
                $lastNumber = (int) substr($lastCouncilThisYear->code, 0, 3);
                $nextNumber = $lastNumber + 1;
            }

            // Formatea el nuevo número para que siempre tenga 3 dígitos (ej: 1 -> '001')
            // y le concatena el año.
            $council->code = str_pad($nextNumber, 3, '0', STR_PAD_LEFT) . '_' . $year;
        });
    }

    protected $casts = [
        'scheduled_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    /**
     * Define la relación "pertenece a" con el usuario que es el director del consejo.
     *
     * @return BelongsTo
     */
    public function director(): BelongsTo
    {
        return $this->belongsTo(User::class, 'director_id');
    }

    /**
     * Define la relación "pertenece a muchos" con los usuarios que son consejeros.
     *
     * @return BelongsToMany
     */
    public function counselors(): BelongsToMany
    {
        // Se especifica el nombre de la tabla pivote 'council_user'.
        // withTimestamps() asegura que las columnas created_at y updated_at en la tabla pivote se gestionen automáticamente.
        return $this->belongsToMany(User::class, 'council_user', 'council_id', 'user_id')->withTimestamps();
    }

    /**
     * Define la relación "tiene muchos" con los puntos del consejo.
     *
     * @return HasMany
     */
    public function points(): HasMany
    {
        return $this->hasMany(Point::class);
    }
}
