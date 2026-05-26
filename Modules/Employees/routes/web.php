<?php

use Illuminate\Support\Facades\Route;
use Modules\Employees\Http\Controllers\StaffTypeController;
use Modules\Employees\Http\Controllers\StaffController;
use Modules\Employees\Http\Controllers\BenefitController;
use Modules\Employees\Http\Controllers\TeachingLevelController;
use Modules\Employees\Http\Controllers\EmployeeController;
use Modules\Employees\Http\Controllers\EmployeeBenefitHistoryController;

Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {

    Route::resource('/employee-staff-type', StaffTypeController::class)
        ->names('employee.staff.type');

    Route::resource('/employee-staff', StaffController::class)
        ->names('employee.staff');

    Route::resource('/employee-benefit', BenefitController::class)
        ->names('employee.benefit');

    Route::resource('/employee-teaching-level', TeachingLevelController::class)
        ->names('employee.teaching.level');

    Route::resource('/employee', EmployeeController::class)
        ->only(['index', 'create', 'store', 'edit', 'update', 'destroy', 'show'])
        ->names('employee');

    Route::resource('/employee-benefit-history', EmployeeBenefitHistoryController::class)
        ->only(['index', 'create', 'store', 'edit', 'update', 'destroy', 'show'])
        ->names('employee.benefit.history');

});
