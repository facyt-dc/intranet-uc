<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CouncilPoint extends Model
{
    protected $fillable = ['council_id', 'topic', 'requested_by_id', 'status', 'minimum_votes'];

    public function council()
    {
        return $this->belongsTo(Council::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by_id');
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }
}
