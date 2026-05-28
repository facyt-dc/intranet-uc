<?php

namespace Modules\Thesis\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\Thesis\Models\StudentStatus;
use Spatie\Permission\Models\Role;

class StudentStatusesController extends Controller
{
    public function index()
    {
        return Inertia::render('thesis::StudentStatuses/index', [
            'studentStatuses' => StudentStatus::all(),
        ]);
    }

    public function create()
    {
        return Inertia::render('thesis::StudentStatuses/create', [
            'roles' => Role::all(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:student_statuses,name',
            'description' => 'nullable|string',
        ]);

        $newStatus = StudentStatus::create($request->only('name', 'description'));

        return to_route('studentStatuses.index')->with('flash', [
            'alert' => [
                'id' => $newStatus->id,
                'message' => 'Estatus creado correctamente.',
                'severity' => 'success',
            ],
        ]);
    }

    public function edit(StudentStatus $studentStatus)
    {
        return Inertia::render('thesis::StudentStatuses/edit', [
            'studentStatus' => $studentStatus,
        ]);
    }

    public function show(StudentStatus $studentStatus)
    {
        return Inertia::render('thesis::StudentStatuses/show', [
            'studentStatus' => $studentStatus,
        ]);
    }

    public function update(Request $request, StudentStatus $studentStatus)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:student_statuses,name,' . $studentStatus->id,
            'description' => 'nullable|string',
        ]);

        $studentStatus->name = $request->name;
        $studentStatus->description = $request->description;
        $studentStatus->save();

        return to_route('studentStatuses.index')->with('flash', [
            'alert' => [
                'id' => $studentStatus->id,
                'message' => 'Estatus actualizado correctamente.',
                'severity' => 'success',
            ],
        ]);
    }

    public function destroy(StudentStatus $studentStatus)
    {
        $id = $studentStatus->id;

        $studentStatus->delete();

        return to_route('studentStatuses.index')->with('flash', [
            'alert' => [
                'id' => $id,
                'message' => 'Estatus eliminado correctamente!!!.',
                'severity' => 'error',
            ],
        ]);
    }
}