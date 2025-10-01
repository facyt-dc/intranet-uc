<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DocumentController;

use App\Http\Controllers\ItemController;
use App\Http\Controllers\ItemCategoryController;
use App\Http\Controllers\ItemStatusController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\MovementTypeController;
use App\Http\Controllers\InventoryMovementController;


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

});

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('/item', ItemController::class)->only(['index','create','store','edit','update','destroy'])->names('item');
    Route::resource('/item-category', ItemCategoryController::class)->only(['index','create','store','edit','update','destroy'])->names('item-category');
    Route::resource('/location', LocationController::class)->only(['index','create','store','edit','update','destroy'])->names('item-location');
    Route::resource('/item-status', ItemStatusController::class)->only(['index','create','store','edit','update','destroy'])->names('item-status');
    Route::resource('/inventory-movement', InventoryMovementController::class)->only(['index', 'show'])->names('item-inventory-movement');
    Route::resource('/movement-type', MovementTypeController::class)->only(['index','create','store','edit','update','destroy'])->names('item-movement-type');
});




require __DIR__.'/auth.php';
