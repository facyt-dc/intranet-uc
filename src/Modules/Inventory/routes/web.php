<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventory\Http\Controllers\ItemController;
use Modules\Inventory\Http\Controllers\ItemCategoryController;
use Modules\Inventory\Http\Controllers\ItemStatusController;
use Modules\Inventory\Http\Controllers\LocationController;
use Modules\Inventory\Http\Controllers\MovementTypeController;
use Modules\Inventory\Http\Controllers\InventoryMovementController;

Route::middleware(['auth', 'verified', 'permission:inventory.access'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('/item', ItemController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])->names('item');
        Route::resource('/item-category', ItemCategoryController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])->names('item-category');
        Route::resource('/location', LocationController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])->names('item-location');
        Route::resource('/item-status', ItemStatusController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])->names('item-status');
        Route::resource('/inventory-movement', InventoryMovementController::class)->only(['index', 'show'])->names('item-inventory-movement');
        Route::resource('/movement-type', MovementTypeController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])->names('item-movement-type');
    });
