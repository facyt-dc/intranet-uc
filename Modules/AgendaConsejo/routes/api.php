<?php

use Illuminate\Support\Facades\Route;
use Modules\AgendaConsejo\Http\Controllers\AgendaController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('agendaconsejos', AgendaController::class)->names('agendaconsejo');
});
