<?php

namespace Modules\Thesis\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThesisFile extends Model
{
    use HasFactory;

    protected $table = 'thesis_files';

    protected $fillable = [
        'thesis_id',
        'type',
        'original_name',
        'path',
    ];

    /**
     * Un archivo pertenece a una tesis.
     */
    public function thesis(): BelongsTo
    {
        return $this->belongsTo(Thesis::class);
    }
}