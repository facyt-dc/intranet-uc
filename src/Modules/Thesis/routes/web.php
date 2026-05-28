<?php

use Illuminate\Support\Facades\Route;
use Modules\Thesis\Http\Controllers\GanttChartController;
use Modules\Thesis\Http\Controllers\StudentStatusesController;
use Modules\Thesis\Http\Controllers\ThesisController;
use Modules\Thesis\Http\Controllers\ThesisFileController;
use Modules\Thesis\Http\Controllers\ThesisStudentController;
use Modules\Thesis\Http\Controllers\ThesisTeacherController;

Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::prefix('thesis')->group(function () {
        Route::resource('thesisStudent', ThesisStudentController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy', 'show'])
            ->names('thesisStudent');

        Route::post('thesisStudent/import-excel', [ThesisStudentController::class, 'importExcel'])
            ->name('thesisStudent.importExcel');

        Route::resource('studentStatuses', StudentStatusesController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy', 'show'])
            ->names('studentStatuses');

        Route::resource('Thesis', ThesisController::class)
            ->parameters(['Thesis' => 'thesis'])
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy', 'show'])
            ->names('Thesis');

        Route::get('gantt-chart', [GanttChartController::class, 'index'])->name('thesis.ganttChart');

        Route::resource('thesisTeachers', ThesisTeacherController::class)
            ->names('thesisTeacher');
    });

    Route::get('thesis-files/{thesisFile}/download', [ThesisFileController::class, 'download'])
        ->name('thesis-files.download');
});
