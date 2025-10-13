<?php

namespace App\Http\Controllers\Thesis;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\ThesisStudent;
use App\Models\StudentStatus;
use App\Models\Thesis;
use App\Models\ThesisFile;
use App\Models\StudentStatusHistory;
use App\Models\ThesisTeacher;
use Spatie\Permission\Models\Role;

use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ThesisController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render("Thesis/ThesisProjects/index",[
            'thesis' => Thesis::with(['students', 'teachers'])->latest()->get(),

        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render("Thesis/ThesisProjects/create",[
                'students' => ThesisStudent::all(), 
                'teachers' => ThesisTeacher::all(),
        ]);
    }

        public function show(Thesis $thesis)
    {

        return Inertia::render('Thesis/ThesisProjects/show', [
           'thesis' => $thesis->load(['students', 'teachers', 'files']), 
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
        {
        // 1. VALIDACIÓN
        $request->validate([
            'title' => 'required|string|max:255|unique:thesis,title',
            'date'  => 'required|date',
            'student_ids'   => 'required|array|min:1|max:2',
            'student_ids.*' => 'exists:thesis_student,id', 
            'teacher_ids'   => 'nullable|array',
            'teacher_ids.*' => 'exists:thesis_teachers,id',
            'pteg_document' => 'nullable|file|mimes:pdf,doc,docx,zip|max:20480',
            'teg_document'  => 'nullable|file|mimes:pdf,doc,docx,zip|max:20480',
        ]);
        $newThesis = null; 


        try{
        DB::transaction(function () use ($request, &$newThesis) {

            // 2. CREAR LA TESIS
            $newThesis = Thesis::create([
                'title'     => $request->title,
                'date'      => $request->date,
            ]);

            // 3. GUARDAR DOCUMENTO PTEG
            if ($request->hasFile('pteg_document')) {
                $file = $request->file('pteg_document');
                $path = $file->store('thesis_files', 'public');
                $newThesis->files()->create([
                    'type' => 'pteg',
                    'original_name' => $file->getClientOriginalName(),
                    'path' => $path,
                ]);
            }

            // 4. GUARDAR DOCUMENTO TEG
            if ($request->hasFile('teg_document')) {
                $file = $request->file('teg_document');
                $path = $file->store('thesis_files', 'public');
                $newThesis->files()->create([
                    'type' => 'teg',
                    'original_name' => $file->getClientOriginalName(),
                    'path' => $path,

                ]);
            }

            // 5. SINCRONIZAR ESTUDIANTES
            $studentIds = $request->student_ids;
            $newThesis->students()->sync($studentIds);

             if ($request->has('teacher_ids')) {
                    $newThesis->teachers()->sync($request->teacher_ids);
                }

             // Desactivar todas las tesis anteriores de los estudiantes involucrados.
            Thesis::where('is_active', true) // Solo nos interesan las que están activas
                    ->where('id', '!=', $newThesis->id) // Excluimos la tesis que acabamos de crear
                    ->whereHas('students', function ($query) use ($studentIds) {
                        // Filtramos para encontrar tesis que tengan al menos uno de los estudiantes
                        $query->whereIn('thesis_student.id', $studentIds); 
                    })
                    ->update(['is_active' => false]); // Actualizamos su estado a inactivo
                
              

                // 6. ACTUALIZAR EL ESTATUS DE LOS ESTUDIANTES A "PTEG INSCRITO"
            
            // Primero, encontramos el ID del estatus que queremos asignar.
            // Usamos firstOrFail() para que, si el estatus no existe, la transacción falle y se revierta todo.
             $targetStatusName = $request->hasFile('teg_document') ? 'TEG inscrito' : 'PTEG inscrito';
                
                // Buscar el estado correspondiente. Si no existe, la transacción fallará.
                $targetStatus = StudentStatus::where('name', $targetStatusName)->firstOrFail();
                
                // Actualizar el historial para cada estudiante
                foreach ($studentIds as $studentId) {
                    StudentStatusHistory::create([
                        'thesis_student_id' => $studentId,
                        'student_status_id' => $targetStatus->id,
                        'start_date'        => now(),
                    ]);
                }
                
                // Actualizar el estado actual en la tabla principal
                ThesisStudent::whereIn('id', $studentIds)
                    ->update(['status_id' => $targetStatus->id]);
                              });
        }

         catch (Exception $e) {
            return back()->with('flash', [
                'alert' => [
                    'message' => 'Ocurrió un error al crear la tesis: ' . $e->getMessage(),
                    'severity' => 'error'
                ]
            ]);
        }

        // 6. REDIRECCIÓN
        return to_route('Thesis.index')->with('flash', [
            'alert' => [
                'id' => $newThesis->id,
                'message' => 'Proyecto de tesis creado correctamente.',
                'severity' => 'success'
            ]
        ]);
    }

public function edit(Thesis $thesis)
{
    return Inertia::render('Thesis/ThesisProjects/edit', [
        'thesis' => $thesis->load(['students', 'teachers', 'files']),
        'students' => ThesisStudent::all(),
        'teachers' => ThesisTeacher::all(),
        'statuses' => StudentStatus::all(),
    ]);
}

    /**
     * Update the specified resource in storage.
     */

public function update(Request $request, Thesis $thesis)
{
    // 1. VALIDACIÓN
    $validatedData = $request->validate([
        'title' => 'required|string|max:255|unique:thesis,title,' . $thesis->id,
        'date'  => 'required|date',
        'student_ids'   => 'required|array|min:1|max:2',
        'student_ids.*' => 'exists:thesis_student,id',
        'teacher_ids'   => 'nullable|array',
        'teacher_ids.*' => 'exists:thesis_teachers,id',
        'pteg_document' => 'nullable|file|mimes:pdf,doc,docx,zip|max:20480',
        'teg_document'  => 'nullable|file|mimes:pdf,doc,docx,zip|max:20480',
        'deleted_files' => 'nullable|array',
        'deleted_files.*' => 'nullable|integer|exists:thesis_files,id',
    ]);

    // 2. USAR UNA TRANSACCIÓN PARA GARANTIZAR LA INTEGRIDAD
    try {
        DB::transaction(function () use ($validatedData, $request, $thesis) {
            
            // --- CÁLCULO DE DIFERENCIAS DE ESTUDIANTES ---
            $originalStudentIds = $thesis->students()->pluck('thesis_student.id');
            $newStudentIds = collect($validatedData['student_ids']);
            $removedStudentIds = $originalStudentIds->diff($newStudentIds);
            $addedStudentIds = $newStudentIds->diff($originalStudentIds);

            // A. ACTUALIZAR DATOS DE LA TESIS Y SINCRONIZAR RELACIONES
            $thesis->update([
                'title' => $validatedData['title'],
                'date'  => $validatedData['date'],
            ]);
            $thesis->students()->sync($newStudentIds);
            $thesis->teachers()->sync($validatedData['teacher_ids'] ?? []);

            // B. MANEJAR ESTADOS DE ESTUDIANTES SI LA TESIS ESTÁ ACTIVA
            if ($thesis->is_active) {
                // 1. Revertir estado de estudiantes ELIMINADOS
                if ($removedStudentIds->isNotEmpty()) {
                    $inscritoStatus = StudentStatus::where('name', 'inscrito')->firstOrFail();
                    foreach ($removedStudentIds as $studentId) {
                        StudentStatusHistory::create([
                            'thesis_student_id' => $studentId,
                            'student_status_id' => $inscritoStatus->id,
                            'start_date'        => now(),
                        ]);
                    }
                    ThesisStudent::whereIn('id', $removedStudentIds)->update(['status_id' => $inscritoStatus->id]);
                }

                // 2. Actualizar estado de estudiantes NUEVOS y desactivar sus tesis antiguas
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
                            'start_date'        => now(),
                        ]);
                    }
                    ThesisStudent::whereIn('id', $addedStudentIds)->update(['status_id' => $targetStatus->id]);
                }
            }
            
            // C. BORRAR ARCHIVOS MARCADOS
            if (!empty($validatedData['deleted_files'])) {
                $filesToDelete = ThesisFile::whereIn('id', $validatedData['deleted_files'])
                    ->where('thesis_id', $thesis->id)->get();
                
                foreach ($filesToDelete as $file) {
                    Storage::disk('public')->delete($file->path);
                    $file->delete();
                }
            }

            // D. PROCESAR ARCHIVO PTEG
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

            // E. PROCESAR ARCHIVO TEG Y ACTUALIZAR ESTADO DE TODOS LOS ESTUDIANTES ASOCIADOS
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

                // Actualizamos el estado de TODOS los estudiantes actualmente en la tesis a "TEG inscrito"
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
                'severity' => 'error'
            ]
        ]);
    }
    
    // 3. REDIRECCIÓN EN CASO DE ÉXITO
    return to_route('Thesis.index')->with('flash', [
        'alert' => [
            'id' => $thesis->id,
            'message' => 'Proyecto de tesis actualizado correctamente.',
            'severity' => 'success'
        ]
    ]);
}

    /**
     * Remove the specified resource from storage.
     */
       public function destroy(Thesis $thesis)
    {
        $id = $thesis->id;

        // Si la tesis que se va a eliminar está activa...
        if ($thesis->is_active) {
            // ...obtenemos los IDs de los estudiantes asociados.
            $studentIds = $thesis->students()->pluck('thesis_student.id');
            
            if ($studentIds->isNotEmpty()) {
                // Buscamos el estado "inscrito".
                $inscritoStatus = StudentStatus::where('name', 'inscrito')->first();

                if ($inscritoStatus) {
                    // Actualizamos el historial y el estado de los estudiantes.
                    foreach ($studentIds as $studentId) {
                        StudentStatusHistory::create([
                            'thesis_student_id' => $studentId,
                            'student_status_id' => $inscritoStatus->id,
                            'start_date'        => now(),
                            // 'notes' => 'Proyecto de tesis activo eliminado: ' . $thesis->title,
                        ]);
                    }
                    
                    ThesisStudent::whereIn('id', $studentIds)
                        ->update(['status_id' => $inscritoStatus->id]);
                }
            }
        }

        // El borrado de la tesis ocurre después de haber actualizado a los estudiantes.
        $thesis->delete();

        return to_route('Thesis.index')->with('flash',[
            'alert' => [
                'id' => $id,
                'message' => 'Proyecto de tesis eliminado correctamente.',
                'severity' => 'error'
            ]
        ]);
    }

}
