<?php

use Illuminate\Support\Facades\Route;
use Modules\AgendaConsejo\Http\Controllers\AgendaConsejoController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('agendaconsejos', AgendaConsejoController::class)->names('agendaconsejo');
});
