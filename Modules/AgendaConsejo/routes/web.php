<?php

use Illuminate\Support\Facades\Route;
use Modules\AgendaConsejo\Http\Controllers\AgendaConsejoController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('agendaconsejos', AgendaConsejoController::class)->names('agendaconsejo');
});
