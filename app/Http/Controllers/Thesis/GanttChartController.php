<?php

namespace App\Http\Controllers\Thesis;


use App\Http\Controllers\Controller;
use App\Models\ThesisStudent;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class GanttChartController extends Controller
{
    public function index()
    {
        // 1. Obtenemos todos los estudiantes con su historial de estados y el modelo de estado asociado.
        $students = ThesisStudent::with(['statusHistory.status'])
            // Solo estudiantes que tengan al menos un estado en su historial
            ->whereHas('statusHistory')
            ->get();

        // 2. Transformamos los datos para el frontend.
        $ganttData = $students->map(function ($student) {
            $statuses = [];
            $history = $student->statusHistory;

            foreach ($history as $index => $record) {
                $startDate = Carbon::parse($record->start_date);
                
                // El fin de un estado es el comienzo del siguiente.
                $endDate = isset($history[$index + 1])
                    ? Carbon::parse($history[$index + 1]->start_date)->subDay()
                    : Carbon::now(); // Si es el último, termina "ahora".

                $statuses[] = [
                    'status_name' => $record->status->name,
                    'start'       => $startDate->toDateString(),
                    'end'         => $endDate->toDateString(),
                ];
            }

            return [
                'student_id'   => $student->id,
                'student_name' => $student->name,
                'statuses'     => $statuses,
            ];
        });

        // 3. Pasamos los datos a la vista de Inertia.
        return Inertia::render('Thesis/GanttChart/index', [
            'ganttData' => $ganttData,
        ]);
    }
}