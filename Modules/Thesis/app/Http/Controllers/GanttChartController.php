<?php

namespace Modules\Thesis\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Inertia\Inertia;
use Modules\Thesis\Models\ThesisStudent;

class GanttChartController extends Controller
{
    public function index()
    {
        $students = ThesisStudent::with(['statusHistory.status'])
            ->whereHas('statusHistory')
            ->get();

        $ganttData = $students->map(function ($student) {
            $statuses = [];
            $history = $student->statusHistory;

            foreach ($history as $index => $record) {
                $startDate = Carbon::parse($record->start_date);
                $endDate = isset($history[$index + 1])
                    ? Carbon::parse($history[$index + 1]->start_date)->subDay()
                    : Carbon::now();

                $statuses[] = [
                    'status_name' => $record->status->name,
                    'start' => $startDate->toDateString(),
                    'end' => $endDate->toDateString(),
                ];
            }

            return [
                'student_id' => $student->id,
                'student_name' => $student->name,
                'statuses' => $statuses,
            ];
        });

        return Inertia::render('Thesis/GanttChart/index', [
            'ganttData' => $ganttData,
        ]);
    }
}