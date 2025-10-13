<?php

namespace App\Http\Controllers\Thesis;

use App\Http\Controllers\Controller;
use App\Models\ThesisTeacher;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ThesisTeacherController extends Controller
{
    /**
     * Muestra una lista de todos los docentes.
     */
    public function index()
    {
        return Inertia::render("Thesis/ThesisTeacher/Index", [
            // Enviamos todos los docentes con la relación a sus tesis
            'teachers' => ThesisTeacher::with('theses')->latest()->get(),
        ]);
    }

    /**
     * Muestra el formulario para crear un nuevo docente.
     */
    public function create()
    {
        return Inertia::render("Thesis/ThesisTeacher/Create");
    }

    /**
     * Almacena un nuevo docente en la base de datos.
     */
    public function store(Request $request)
    {
        $request->validate([
            "name"  => "required|string|max:255",
            "email" => "required|email|unique:thesis_teachers,email",
            "ci"    => "required|string|unique:thesis_teachers,ci",
            "id_uc" => "nullable|string|unique:thesis_teachers,id_uc",
        ]);

        $newTeacher = ThesisTeacher::create($request->all());

        return to_route('thesisTeacher.index')->with('flash', [
            'alert' => [
                'id' => $newTeacher->id,
                'message' => 'Docente creado correctamente.',
                'severity' => 'success'
            ]
        ]);
    }

    /**
     * Muestra los detalles de un docente específico.
     */
    public function show(ThesisTeacher $thesisTeacher)
    {
        return Inertia::render('Thesis/ThesisTeacher/Show', [
            'teacher' => $thesisTeacher->load('theses'),
        ]);
    }

    /**
     * Muestra el formulario para editar un docente.
     */
    public function edit(ThesisTeacher $thesisTeacher)
    {
        return Inertia::render('Thesis/ThesisTeacher/Edit', [
            'teacher' => $thesisTeacher,
        ]);
    }

    /**
     * Actualiza un docente específico en la base de datos.
     */
    public function update(Request $request, ThesisTeacher $thesisTeacher)
    {
        $request->validate([
            "name"  => "required|string|max:255",
            "email" => "required|email|unique:thesis_teachers,email," . $thesisTeacher->id,
            "ci"    => "required|string|unique:thesis_teachers,ci," . $thesisTeacher->id,
            "id_uc" => "nullable|string|unique:thesis_teachers,id_uc," . $thesisTeacher->id,
        ]);

        $thesisTeacher->update($request->all());

        return to_route('thesisTeacher.index')->with('flash', [
            'alert' => [
                'id' => $thesisTeacher->id,
                'message' => 'Docente actualizado correctamente.',
                'severity' => 'success'
            ]
        ]);
    }

    /**
     * Elimina un docente de la base de datos.
     */
    public function destroy(ThesisTeacher $thesisTeacher)
    {
        $id = $thesisTeacher->id;
        $thesisTeacher->delete();

        return to_route('thesisTeacher.index')->with('flash', [
            'alert' => [
                'id' => $id,
                'message' => 'Docente eliminado correctamente.',
                'severity' => 'error'
            ]
        ]);
    }
}