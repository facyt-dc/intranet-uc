<?php

use Illuminate\Support\Facades\Route;
use Modules\Thesis\Http\Controllers\ThesisController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('theses', ThesisController::class)->names('thesis');
});
