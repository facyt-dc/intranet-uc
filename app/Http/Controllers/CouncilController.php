<?php

namespace App\Http\Controllers;

use App\Models\Council;
use App\Models\User;
use App\Notifications\NewCouncilAssigned;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

// ¡Sin constructor de middleware!
class CouncilController extends Controller
{
    /**
     * Muestra una lista paginada de todos los consejos.
     */
    public function index(): Response
    {
        $user = Auth::user();
        $query = Council::query();

        // Si el usuario es Consejero, filtramos los consejos
        if ($user->hasRole('counselor')) {
            // Obtenemos solo los consejos en los que el usuario es participante.
            $query->whereHas('participants', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        }

        // Construimos la consulta final con las relaciones y paginación
        $councils = $query->with('director:id,name')
            ->withCount('participants')
            ->latest()
            ->paginate(10);

        return Inertia::render('Councils/Index', [
            'councils' => $councils,
        ]);
    }

    /**
     * Muestra el formulario para crear un nuevo consejo.
     */
    public function create(): Response
    {
        return Inertia::render('Councils/Create', [
            'counselors' => User::role('counselor')
                ->select('id', 'name')
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Almacena un nuevo consejo en la base de datos.
     */
    public function store(Request $request, Council $council): RedirectResponse
    {
        if ($council->status === 'Cerrado') {
            // Si está cerrado, redirigimos con un error.
            return back()->with('error', 'No se pueden añadir puntos a un consejo que ya ha sido cerrado.');
        }

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

        $council = Council::create([
            'name' => $validated['name'],
            'date' => $validated['date'],
            'director_id' => Auth::id(),
            'status' => 'Programado',
        ]);

        $council->participants()->sync($validated['participants']);

        $participantsToNotify = User::find($validated['participants']);
        Notification::send($participantsToNotify, new NewCouncilAssigned($council));

        return to_route('councils.show', $council)->with('success', 'Consejo creado y notificaciones enviadas correctamente.');
    }

    /**
     * Muestra la vista detallada de un consejo específico.
     */
    public function show(Council $council): Response
    {
        $council->load([
            'director:id,name',
            'participants:id,name,email',
            'points' => fn($query) => $query->orderBy('order'),
            'points.requester:id,name',
            'points.votableUsers:id,name',
            'points.votingOptions:id,name',
            'points.votes.user:id,name',
            'points.votes.option:id,name',
        ]);

        return Inertia::render('Councils/Show', [
            'council' => $council,
            'counselors' => User::role('counselor')->select('id', 'name')->orderBy('name')->get(),
            'votingOptions' => \App\Models\VotingOption::where('is_active', true)->get(['id', 'name']),
        ]);
    }

    /**
     * Muestra el formulario para editar un consejo existente.
     */
    public function edit(Council $council): Response
    {
        return Inertia::render('Councils/Edit', [
            'council' => $council->load('participants:id'),
            'counselors' => User::role('counselor')->select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    /**
     * Actualiza un consejo existente en la base de datos.
     */
    public function update(Request $request, Council $council): RedirectResponse
    {
        if ($council->status === 'Cerrado') {
            return back()->with('error', 'No se puede editar un consejo que ya ha sido cerrado.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date_format:Y-m-d',
            'participants' => 'required|array|min:1',
            'participants.*' => 'required|integer|exists:users,id',
        ]);

        $council->update($request->only('name', 'date'));
        $council->participants()->sync($validated['participants']);

        return to_route('councils.index')->with('success', 'Consejo actualizado correctamente.');
    }

    /**
     * Elimina un consejo de la base de datos.
     */
    public function destroy(Council $council): RedirectResponse
    {
        $council->delete();
        return to_route('councils.index')->with('success', 'Consejo eliminado correctamente.');
    }

    /**
     * Método personalizado para cerrar un consejo y sus votaciones.
     */
    public function close(Council $council): RedirectResponse
    {
        $council->update([
            'status' => 'Cerrado',
            'closed_at' => now(),
        ]);

        return to_route('councils.show', $council)->with('success', 'El consejo ha sido cerrado exitosamente.');
    }
}