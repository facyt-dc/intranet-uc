<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DocumentController;

use App\Http\Controllers\AgendaController;
use App\Http\Controllers\AgendaPointController;
use App\Http\Controllers\VoteController;
use App\Http\Controllers\Settings\VotingOptionController;
use Spatie\Permission\Middleware\RoleMiddleware;


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

Route::middleware(['auth', 'verified', 'role:director'])->group(function () {

    // --- Gestión de Consejos (Agenda) ---
    Route::put('agendas/{agenda}/close', [AgendaController::class, 'close'])->name('agendas.close');
    Route::resource('agendas', AgendaController::class)->except(['index', 'show']);

    // --- Gestión de Puntos de Consejo (AgendaPoint) ---
    // Es un recurso anidado bajo los consejos.
    Route::resource('agendas.points', AgendaPointController::class)
         ->only(['store', 'update', 'destroy']) // Solo se necesitan los endpoints, no las vistas.
         ->shallow(); // Hace las URLs de update/destroy más limpias (ej: /points/123)

    // --- Gestión de Opciones de Votación (VotingOption) ---
    // Se agrupan bajo un prefijo 'settings' para organizar las URLs.
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::resource('voting-options', VotingOptionController::class)
             ->except(['create', 'edit', 'show']); // El CRUD se maneja en el componente Index.
    });

    // --- Acción de Añadir Conclusión a un Punto de Consejo ---
    Route::patch('points/{point}/conclusion', [AgendaPointController::class, 'addConclusion'])->name('points.conclusion.add');
});

Route::middleware(['auth', 'verified', 'role:director|counselor'])->group(function () {
    Route::get('/agendas', [AgendaController::class, 'index'])->name('agendas.index');
    Route::get('/agendas/{agenda}', [AgendaController::class, 'show'])->name('agendas.show');
});

Route::middleware(['auth', 'verified', 'role:counselor'])->group(function () {
    
    // --- Acción de Votar ---
    // El consejero envía un POST a esta ruta para registrar su voto.
    Route::post('points/{point}/votes', [VoteController::class, 'store'])->name('points.votes.store');
});

require __DIR__.'/auth.php';
