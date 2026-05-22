<?php

namespace Modules\Thesis\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Modules\Thesis\Models\StudentStatus;
use Modules\Thesis\Models\StudentStatusHistory;
use Modules\Thesis\Models\Thesis;
use Modules\Thesis\Models\ThesisFile;
use Modules\Thesis\Models\ThesisStudent;
use Modules\Thesis\Models\ThesisTeacher;

class ThesisController extends Controller
{
    public function index()
    {
        return Inertia::render('thesis::ThesisProjects/index', [
            'thesis' => Thesis::with(['students', 'teachers'])->latest()->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('thesis::ThesisProjects/create', [
            'students' => ThesisStudent::all(),
            'teachers' => ThesisTeacher::all(),
        ]);
    }

    public function show(Thesis $thesis)
    {
        return Inertia::render('thesis::ThesisProjects/show', [
            'thesis' => $thesis->load(['students', 'teachers', 'files']),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255|unique:thesis,title',
            'date' => 'required|date',
            'student_ids' => 'required|array|min:1|max:2',
            'student_ids.*' => 'exists:thesis_student,id',
            'teacher_ids' => 'nullable|array',
            'teacher_ids.*' => 'exists:thesis_teachers,id',
            'pteg_document' => 'nullable|file|mimes:pdf,doc,docx,zip|max:20480',
            'teg_document' => 'nullable|file|mimes:pdf,doc,docx,zip|max:20480',
        ]);

        $newThesis = null;

        try {
            DB::transaction(function () use ($request, &$newThesis) {
                $newThesis = Thesis::create([
                    'title' => $request->title,
                    'date' => $request->date,
                ]);

                if ($request->hasFile('pteg_document')) {
                    $file = $request->file('pteg_document');
                    $path = $file->store('thesis_files', 'public');
                    $newThesis->files()->create([
                        'type' => 'pteg',
                        'original_name' => $file->getClientOriginalName(),
                        'path' => $path,
                    ]);
                }

                if ($request->hasFile('teg_document')) {
                    $file = $request->file('teg_document');
                    $path = $file->store('thesis_files', 'public');
                    $newThesis->files()->create([
                        'type' => 'teg',
                        'original_name' => $file->getClientOriginalName(),
                        'path' => $path,
                    ]);
                }

                $studentIds = $request->student_ids;
                $newThesis->students()->sync($studentIds);

                if ($request->has('teacher_ids')) {
                    $newThesis->teachers()->sync($request->teacher_ids);
                }

                Thesis::where('is_active', true)
                    ->where('id', '!=', $newThesis->id)
                    ->whereHas('students', function ($query) use ($studentIds) {
                        $query->whereIn('thesis_student.id', $studentIds);
                    })
                    ->update(['is_active' => false]);

                $targetStatusName = $request->hasFile('teg_document') ? 'TEG inscrito' : 'PTEG inscrito';
                $targetStatus = StudentStatus::where('name', $targetStatusName)->firstOrFail();

                foreach ($studentIds as $studentId) {
                    StudentStatusHistory::create([
                        'thesis_student_id' => $studentId,
                        'student_status_id' => $targetStatus->id,
                        'start_date' => now(),
                    ]);
                }

                ThesisStudent::whereIn('id', $studentIds)->update(['status_id' => $targetStatus->id]);
            });
        } catch (\Exception $e) {
            return back()->with('flash', [
                'alert' => [
                    'message' => 'Ocurrió un error al crear la tesis: ' . $e->getMessage(),
                    'severity' => 'error',
                ],
            ]);
        }

        return to_route('Thesis.index')->with('flash', [
            'alert' => [
                'id' => $newThesis->id,
                'message' => 'Proyecto de tesis creado correctamente.',
                'severity' => 'success',
            ],
        ]);
    }

    public function edit(Thesis $thesis)
    {
        return Inertia::render('thesis::ThesisProjects/edit', [
            'thesis' => $thesis->load(['students', 'teachers', 'files']),
            'students' => ThesisStudent::all(),
            'teachers' => ThesisTeacher::all(),
            'statuses' => StudentStatus::all(),
        ]);
    }

    public function update(Request $request, Thesis $thesis)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255|unique:thesis,title,' . $thesis->id,
            'date' => 'required|date',
            'student_ids' => 'required|array|min:1|max:2',
            'student_ids.*' => 'exists:thesis_student,id',
            'teacher_ids' => 'nullable|array',
            'teacher_ids.*' => 'exists:thesis_teachers,id',
            'pteg_document' => 'nullable|file|mimes:pdf,doc,docx,zip|max:20480',
            'teg_document' => 'nullable|file|mimes:pdf,doc,docx,zip|max:20480',
            'deleted_files' => 'nullable|array',
            'deleted_files.*' => 'nullable|integer|exists:thesis_files,id',
        ]);

        try {
            DB::transaction(function () use ($validatedData, $request, $thesis) {
                $originalStudentIds = $thesis->students()->pluck('thesis_student.id');
                $newStudentIds = collect($validatedData['student_ids']);
                $removedStudentIds = $originalStudentIds->diff($newStudentIds);
                $addedStudentIds = $newStudentIds->diff($originalStudentIds);

                $thesis->update([
                    'title' => $validatedData['title'],
                    'date' => $validatedData['date'],
                ]);
                $thesis->students()->sync($newStudentIds);
                $thesis->teachers()->sync($validatedData['teacher_ids'] ?? []);

                if ($thesis->is_active) {
                    if ($removedStudentIds->isNotEmpty()) {
                        $inscritoStatus = StudentStatus::where('name', 'inscrito')->firstOrFail();
                        foreach ($removedStudentIds as $studentId) {
                            StudentStatusHistory::create([
                                'thesis_student_id' => $studentId,
                                'student_status_id' => $inscritoStatus->id,
                                'start_date' => now(),
                            ]);
                        }
                        ThesisStudent::whereIn('id', $removedStudentIds)->update(['status_id' => $inscritoStatus->id]);
                    }

                    if ($addedStudentIds->isNotEmpty()) {
                        Thesis::where('is_active', true)
                            ->where('id', '!=', $thesis->id)
                            ->whereHas('students', function ($query) use ($addedStudentIds) {
                                $query->whereIn('thesis_student.id', $addedStudentIds);
                            })
                            ->update(['is_active' => false]);

                        $targetStatusName = $request->hasFile('teg_document') ? 'TEG inscrito' : 'PTEG inscrito';
                        $targetStatus = StudentStatus::where('name', $targetStatusName)->firstOrFail();

                        foreach ($addedStudentIds as $studentId) {
                            StudentStatusHistory::create([
                                'thesis_student_id' => $studentId,
                                'student_status_id' => $targetStatus->id,
                                'start_date' => now(),
                            ]);
                        }
                        ThesisStudent::whereIn('id', $addedStudentIds)->update(['status_id' => $targetStatus->id]);
                    }
                }

                if (!empty($validatedData['deleted_files'])) {
                    $filesToDelete = ThesisFile::whereIn('id', $validatedData['deleted_files'])
                        ->where('thesis_id', $thesis->id)
                        ->get();

                    foreach ($filesToDelete as $file) {
                        Storage::disk('public')->delete($file->path);
                        $file->delete();
                    }
                }

                if ($request->hasFile('pteg_document')) {
                    $oldFile = $thesis->files()->where('type', 'pteg')->first();
                    if ($oldFile) {
                        Storage::disk('public')->delete($oldFile->path);
                        $oldFile->delete();
                    }

                    $file = $request->file('pteg_document');
                    $path = $file->store('thesis_files', 'public');
                    $thesis->files()->create([
                        'type' => 'pteg',
                        'original_name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'size' => $file->getSize(),
                    ]);
                }

                if ($request->hasFile('teg_document')) {
                    $oldFile = $thesis->files()->where('type', 'teg')->first();
                    if ($oldFile) {
                        Storage::disk('public')->delete($oldFile->path);
                        $oldFile->delete();
                    }

                    $file = $request->file('teg_document');
                    $path = $file->store('thesis_files', 'public');
                    $thesis->files()->create([
                        'type' => 'teg',
                        'original_name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'size' => $file->getSize(),
                    ]);

                    $tegStatus = StudentStatus::where('name', 'TEG inscrito')->firstOrFail();
                    foreach ($newStudentIds as $studentId) {
                        StudentStatusHistory::updateOrCreate(
                            ['thesis_student_id' => $studentId, 'student_status_id' => $tegStatus->id],
                            ['start_date' => now()]
                        );
                    }
                    ThesisStudent::whereIn('id', $newStudentIds)->update(['status_id' => $tegStatus->id]);
                }
            });
        } catch (\Exception $e) {
            return back()->with('flash', [
                'alert' => [
                    'message' => 'Ocurrió un error al actualizar la tesis: ' . $e->getMessage(),
                    'severity' => 'error',
                ],
            ]);
        }

        return to_route('Thesis.index')->with('flash', [
            'alert' => [
                'id' => $thesis->id,
                'message' => 'Proyecto de tesis actualizado correctamente.',
                'severity' => 'success',
            ],
        ]);
    }
}
