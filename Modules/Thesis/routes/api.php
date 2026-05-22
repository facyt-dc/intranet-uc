<?php

use Illuminate\Support\Facades\Route;
use Modules\Thesis\Http\Controllers\ThesisController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('theses', ThesisController::class)->names('thesis');
});
