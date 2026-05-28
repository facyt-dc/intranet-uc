<?php

use Illuminate\Support\Facades\Route;

use Modules\AgendaConsejo\Http\Controllers\AgendaController;
use Modules\AgendaConsejo\Http\Controllers\AgendaPointController;
use Modules\AgendaConsejo\Http\Controllers\VoteController;
use Modules\AgendaConsejo\Http\Controllers\Settings\VotingOptionController;
use Modules\AgendaConsejo\Http\Controllers\CommentController;

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
    Route::post('points/{point}/comments', [CommentController::class, 'store'])->name('points.comments.store');
});

Route::middleware(['auth', 'verified', 'role:counselor'])->group(function () {
    
    // --- Acción de Votar ---
    // El consejero envía un POST a esta ruta para registrar su voto.
    Route::post('points/{point}/votes', [VoteController::class, 'store'])->name('points.votes.store');

    // --- Accion de aliminar voto ---
    // La ruta recibe el ID del voto específico a eliminar.
    Route::delete('votes/{vote}', [VoteController::class, 'destroy'])->name('votes.destroy');
});
