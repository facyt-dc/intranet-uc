<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\User;
use App\Notifications\NewAgendaAssigned;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

// ¡Sin constructor de middleware!
class AgendaController extends Controller
{
    /**
     * Muestra una lista paginada de todos los consejos.
     */
    public function index(): Response
    {
        $user = Auth::user();
        $query = Agenda::query();

        // Si el usuario es solo Consejero, filtramos los consejos
        if ($user->hasRole('counselor') && !$user->hasRole('director')) {
            // Obtenemos solo los consejos en los que el usuario es participante.
            $query->whereHas('participants', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        }

        // Construimos la consulta final con las relaciones y paginación
        $agendas = $query->with('director:id,name')
            ->withCount('participants')
            ->latest()
            ->paginate(10);

        return Inertia::render('Agendas/Index', [
            'agendas' => $agendas,
        ]);
    }

    /**
     * Muestra el formulario para crear un nuevo consejo.
     */
    public function create(): Response
    {
        return Inertia::render('Agendas/Create', [
            'counselors' => User::role('counselor')
                ->select('id', 'name')
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Almacena un nuevo consejo en la base de datos.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'participants' => 'required|array|min:1',
            'participants.*' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->whereIn('id', function ($subQuery) {
                        $subQuery->select('model_id')->from('model_has_roles')->where('role_id', function ($roleQuery) {
                            $roleQuery->select('id')->from('roles')->where('name', 'counselor');
                        });
                    });
                }),
            ],
        ]);

        $agenda = Agenda::create([
            'name' => $validated['name'],
            'date' => $validated['date'],
            'director_id' => Auth::id(),
            'status' => 'Programado',
        ]);

        $agenda->participants()->sync($validated['participants']);

        $participantsToNotify = User::find($validated['participants']);
        Notification::send($participantsToNotify, new NewAgendaAssigned($agenda));

        return to_route('agendas.show', $agenda)->with('success', 'Consejo creado y notificaciones enviadas correctamente.');
    }

    /**
     * Muestra la vista detallada de un consejo específico.
     */
    public function show(Agenda $agenda): Response
    {
        $agenda->load([
            'director:id,name',
            'participants:id,name,email',
            'points' => fn($query) => $query->orderBy('order'),
            'points.requester:id,name',
            'points.votableUsers:id,name',
            'points.votingOptions:id,name',
            'points.votes.user:id,name',
            'points.votes.option:id,name',
            'points.comments.user:id,name',
        ]);

        return Inertia::render('Agendas/Show', [
            'agenda' => $agenda,
            'counselors' => User::role('counselor')->select('id', 'name')->orderBy('name')->get(),
            'votingOptions' => \App\Models\VotingOption::where('is_active', true)->get(['id', 'name']),
        ]);
    }

    /**
     * Muestra el formulario para editar un consejo existente.
     */
    public function edit(Agenda $agenda): Response
    {
        return Inertia::render('Agendas/Edit', [
            'agenda' => $agenda->load('participants:id'),
            'counselors' => User::role('counselor')->select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    /**
     * Actualiza un consejo existente en la base de datos.
     */
    public function update(Request $request, Agenda $agenda): RedirectResponse
    {
        if ($agenda->status === 'Cerrado') {
            return back()->with('error', 'No se puede editar un consejo que ya ha sido cerrado.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date_format:Y-m-d',
            'participants' => 'required|array|min:1',
            'participants.*' => 'required|integer|exists:users,id',
        ]);

        $agenda->update($request->only('name', 'date'));
        $agenda->participants()->sync($validated['participants']);

        return to_route('agendas.index')->with('success', 'Consejo actualizado correctamente.');
    }

    /**
     * Elimina un consejo de la base de datos.
     */
    public function destroy(Agenda $agenda): RedirectResponse
    {
        $agenda->delete();
        return to_route('agendas.index')->with('success', 'Consejo eliminado correctamente.');
    }

    /**
     * Método personalizado para cerrar un consejo y sus votaciones.
     */
    public function close(Agenda $agenda): RedirectResponse
    {
        $agenda->update([
            'status' => 'Cerrado',
            'closed_at' => now(),
        ]);

        return to_route('agendas.show', $agenda)->with('success', 'El consejo ha sido cerrado exitosamente.');
    }
}