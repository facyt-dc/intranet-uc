<?php

use Illuminate\Support\Facades\Route;
use Modules\Maintenance\Http\Controllers\MaintenanceRequestController;
use Modules\Maintenance\Http\Controllers\MaintenanceStageController;
use Modules\Maintenance\Http\Controllers\EquipmentController;
use Modules\Maintenance\Http\Controllers\EquipmentCategoryController;

Route::middleware(['auth', 'verified', 'role:technician'])->prefix('mantenimiento')->name('mantenimiento.')->group(function () {

    // === RUTAS ESTÁTICAS (sin parámetros dinámicos en la raíz) ===
    Route::get('/', [MaintenanceRequestController::class, 'index'])->name('index');
    Route::get('/archived', [MaintenanceRequestController::class, 'archivedIndex'])->name('archived.index');
    Route::get('/create', [MaintenanceRequestController::class, 'create'])->name('create');
    Route::post('/', [MaintenanceRequestController::class, 'store'])->name('store');

    // === RUTAS DE SUBRECURSOS (Stages, Equipment, EquipmentCategories) ===
    Route::resource('stages/manager', MaintenanceStageController::class)
        ->parameter('manager', 'stage')
        ->names('stages');

    Route::resource('equipment/categories', EquipmentCategoryController::class)
        ->parameter('categories', 'category')
        ->names('equipment.categories');

    Route::resource('equipment', EquipmentController::class)
        ->names('equipment');

    // === RUTAS DINÁMICAS DE MaintenanceRequest (al final, capturan todo) ===
    Route::get('/{maintenanceRequest}', [MaintenanceRequestController::class, 'show'])->name('show');
    Route::get('/{maintenanceRequest}/edit', [MaintenanceRequestController::class, 'edit'])->name('edit');
    Route::post('/{maintenanceRequest}', [MaintenanceRequestController::class, 'update'])->name('update');
    Route::delete('/{maintenanceRequest}', [MaintenanceRequestController::class, 'destroy'])->name('destroy');
    Route::delete('/{maintenanceRequest}/force', [MaintenanceRequestController::class, 'forceDestroy'])->name('forceDestroy');
    Route::post('/{maintenanceRequest}/stage', [MaintenanceRequestController::class, 'updateStage'])->name('updateStage');
    Route::post('/{maintenanceRequest}/archive', [MaintenanceRequestController::class, 'toggleArchive'])->name('toggleArchive');
});
