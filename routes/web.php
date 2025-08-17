<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\MaintenanceRequestController;
use App\Http\Controllers\MaintenanceStageController;
use App\Http\Controllers\EquipmentController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth','verified'])->group(function(){
    Route::get('/hola',function(){ return Inertia::render('holaMundo'); })->name('hola');

    Route::resource('/admin/role', RoleController::class )
            ->only(['index','create','store','edit','update','destroy'])
            ->names('admin.role');

    Route::resource('/admin/permission', PermissionController::class )
            ->only(['index','create','store','edit','update','destroy'])
            ->names('admin.permission');

    Route::resource('/admin/user', UserController::class )
            ->only(['index','create','store','edit','update','destroy'])
            ->names('admin.user');

    Route::resource('/document', DocumentController::class )
        ->only(['index','create','show','edit','update','destroy'])
        ->names('document');

    Route::prefix('mantenimiento')->name('mantenimiento.')->middleware('auth', 'verified')->group(function () {

        // Rutas para las solicitudes de mantenimiento
        Route::get('/', [MaintenanceRequestController::class, 'index'])
            ->name('index');

        Route::get('/stages/manager', [MaintenanceStageController::class, 'index'])
            ->name('stages.index');
        Route::post('/stages/manager', [MaintenanceStageController::class, 'store'])
            ->name('stages.store');
        Route::resource('equipment', EquipmentController::class)
            ->names('equipment');

        Route::get('/create', [MaintenanceRequestController::class, 'create'])
            ->name('create');

        Route::post('/', [MaintenanceRequestController::class, 'store'])
            ->name('store');

        Route::get('/{maintenanceRequest}', [MaintenanceRequestController::class, 'show'])
            ->name('show');

        Route::get('/{maintenanceRequest}/edit', [MaintenanceRequestController::class, 'edit'])
            ->name('edit');

        Route::post('/{maintenanceRequest}', [MaintenanceRequestController::class, 'update']) // Se usa PUT para actualizaciones completas
            ->name('update');

        Route::delete('/{maintenanceRequest}', [MaintenanceRequestController::class, 'destroy'])
            ->name('destroy');

        Route::post('/{maintenanceRequest}/stage', [MaintenanceRequestController::class, 'updateStage'])
            ->name('updateStage');

    });

    
});

require __DIR__.'/auth.php';
