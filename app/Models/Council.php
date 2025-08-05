<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Council extends Model
{
    protected $fillable = ['code', 'name', 'description', 'director_id', 'scheduled_at'];

    public function director()
    {
        return $this->belongsTo(User::class, 'director_id');
    }

    public function counselors()
    {
        return $this->belongsToMany(User::class);
    }

    public function points()
    {
        return $this->hasMany(CouncilPoint::class);
    }
}
