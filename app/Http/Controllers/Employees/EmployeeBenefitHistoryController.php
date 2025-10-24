<?php

namespace App\Http\Controllers\Employees;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employees\Employee;
use Inertia\Inertia;
use App\Models\Employees\Benefit;
use App\Models\Employees\EmployeeBenefitHistory;

class EmployeeBenefitHistoryController extends Controller
{
        /**
     * Display a listing of the resource.
     */
    public function index()
    {

        return Inertia::render('Employee/EmployeeBenefitHistory/index',[
            'records' => EmployeeBenefitHistory::with([
                'employee' => ['staff'],
                'benefit' => []
            ])->get(),
            'model' => 'employee.benefit.history'
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Employee/EmployeeBenefitHistory/create',[
            'employees' => Employee::with([
                'staff' => ["benefits"]
            ])->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee' => 'required',
            'benefit' => 'required',
            'request_date' => 'required',
            'start_date' => 'required',
            'end_date' => 'required'
        ]);

        $record = EmployeeBenefitHistory::create([
            'employee' => $request->input('employee'),
            'benefit' => $request->input('benefit'),
            'request_date' => explode('T',$request->input('request_date'))[0],
            'approvement_date' => explode('T',$request->input('request_date'))[0],
            'start_date' => explode('T',$request->input('start_date'))[0],
            'end_date' => explode('T',$request->input('end_date'))[0],
            'state' => $request->input('state')
        ]);

        $employee = Employee::find($request->input('employee'));
        $record->employee()->associate($employee);

        $benefit = Benefit::find($request->input('benefit'));
        $record->benefit()->associate($benefit);

        $record->save();

        return to_route('employee.benefit.history.index')->with('flash',[
            'alert' => [
                'id' => $record->id,
                'message' => 'Solicitud creada correctamente.',
                'severity' => 'success'
            ]
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id)
    {
        return Inertia::render('Employee/EmployeeBenefitHistory/edit',[
            'history' => EmployeeBenefitHistory::find($id),
            'employees' => Employee::with([
                'staff' => ['benefits']
            ])->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,int $id)
    {
        $request->validate([
            'request_date' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'approvement_date' => 'required',
            'employee' => 'required',
            'benefit' => 'required',
            'state' => 'required'
        ]);

        $history = EmployeeBenefitHistory::find($id);
        $history->request_date = explode('T',$request->input('request_date'))[0];
        $history->start_date = explode('T',$request->input('start_date'))[0];
        $history->end_date = explode('T',$request->input('end_date'))[0];
        $history->approvement_date = explode('T',$request->input('approvement_date'))[0];
        $history->state = $request->state;
        
        $employee = Employee::find($request->employee);
        $history->employee()->associate($employee);

        $benefit = Benefit::find($request->benefit);
        $history->benefit()->associate($benefit);

        $history->save();

        return to_route('employee.benefit.history.index')->with('flash',[
            'alert' => [
                'id' => $history->id,
                'message' => 'Solicitud actualizada correctamente.',
                'severity' => 'success'
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $destroyed = null;

        if(EmployeeBenefitHistory::find($id)->state == 'draft')
            $destroyed = EmployeeBenefitHistory::destroy($id);

        if($destroyed){
            return to_route('employee.benefit.history.index')->with('flash',[
                'alert' => [
                    'id' => $id,
                    'message' => 'Solicitud eliminada correctamente.',
                    'severity' => 'success'
                ]
            ]);
        }
        else {
            return to_route('employee.benefit.history.index')->with('flash',[
                'alert' => [
                    'id' => $id,
                    'message' => 'No se pudo eliminar la solicitud, sólo solicitudes en estado borrador pueden ser eliminadas',
                    'severity' => 'error'
                ]
            ]);
        }
    }
}
