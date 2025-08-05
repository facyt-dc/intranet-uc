<?php

namespace App\Observers;

use App\Models\Council;
use Carbon\Carbon;

class CouncilObserver
{
    /**
     * Handle the Council "created" event.
     */
    public function created(Council $council): void
    {
        $year = Carbon::now()->year;
        $lastCouncil = Council::whereYear('created_at', $year)->latest('id')->first();
        $number = $lastCouncil ? (int)substr($lastCouncil->code, 0, 3) + 1 : 1;
        $council->code = str_pad($number, 3, '0', STR_PAD_LEFT) . '_' . $year;
    }

    /**
     * Handle the Council "updated" event.
     */
    public function updated(Council $council): void
    {
        //
    }

    /**
     * Handle the Council "deleted" event.
     */
    public function deleted(Council $council): void
    {
        //
    }

    /**
     * Handle the Council "restored" event.
     */
    public function restored(Council $council): void
    {
        //
    }

    /**
     * Handle the Council "force deleted" event.
     */
    public function forceDeleted(Council $council): void
    {
        //
    }
}
