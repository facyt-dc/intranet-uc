<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    protected $fillable = ['council_point_id', 'user_id', 'voting_option_id'];

    public function councilPoint()
    {
        return $this->belongsTo(CouncilPoint::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function votingOption()
    {
        return $this->belongsTo(VotingOption::class);
    }
}
