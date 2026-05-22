<?php

namespace Modules\Thesis\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\Thesis\Models\ThesisTeacher;

class ThesisTeacherController extends Controller
{
    public function index()
    {
        return Inertia::render('thesis::ThesisTeacher/Index', [
            'teachers' => ThesisTeacher::with('theses')->latest()->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('thesis::ThesisTeacher/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:thesis_teachers,email',
            'ci' => 'required|string|unique:thesis_teachers,ci',
            'id_uc' => 'nullable|string|unique:thesis_teachers,id_uc',
        ]);

        $newTeacher = ThesisTeacher::create($request->all());

        return to_route('thesisTeacher.index')->with('flash', [
            'alert' => [
                'id' => $newTeacher->id,
                'message' => 'Docente creado correctamente.',
                'severity' => 'success',
            ],
        ]);
    }

    public function show(ThesisTeacher $thesisTeacher)
    {
        return Inertia::render('thesis::ThesisTeacher/Show', [
            'teacher' => $thesisTeacher->load('theses'),
        ]);
    }

    public function edit(ThesisTeacher $thesisTeacher)
    {
        return Inertia::render('thesis::ThesisTeacher/Edit', [
            'teacher' => $thesisTeacher,
        ]);
    }

    public function update(Request $request, ThesisTeacher $thesisTeacher)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:thesis_teachers,email,' . $thesisTeacher->id,
            'ci' => 'required|string|unique:thesis_teachers,ci,' . $thesisTeacher->id,
            'id_uc' => 'nullable|string|unique:thesis_teachers,id_uc,' . $thesisTeacher->id,
        ]);

        $thesisTeacher->update($request->all());

        return to_route('thesisTeacher.index')->with('flash', [
            'alert' => [
                'id' => $thesisTeacher->id,
                'message' => 'Docente actualizado correctamente.',
                'severity' => 'success',
            ],
        ]);
    }

    public function destroy(ThesisTeacher $thesisTeacher)
    {
        $id = $thesisTeacher->id;
        $thesisTeacher->delete();

        return to_route('thesisTeacher.index')->with('flash', [
            'alert' => [
                'id' => $id,
                'message' => 'Docente eliminado correctamente.',
                'severity' => 'error',
            ],
        ]);
    }
}