<?php

namespace Modules\Employees\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Employees\Models\Employee;
use Inertia\Inertia;
use Modules\Employees\Models\Staff;
use Modules\Employees\Models\TeachingLevel;
use Modules\Employees\Models\Benefit;

class EmployeeController extends Controller
{
        /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('employees::Employee/index',[
            'employees' => Employee::with('staff')->get(),
            'model' => 'employee'
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('employees::Employee/create',[
            'staffs' => Staff::with(['benefits','teaching_levels','type'])->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:64',
            'lastname' => 'required|max:64',
            'staff' => 'required',
            'email' => 'required|unique:employees,email',
            'birthday' => 'required',
            'cedula' => 'required|max:10|unique:employees,cedula',
        ]);

        $employee = Employee::create([
            'name' => $request->input('name'),
            'lastname' => $request->input('lastname'),
            'email' => $request->input('email'),
            'address' => $request->input('address'),
            'phone' => $request->input('phone'),
            'staff' => $request->input('staff'),
            'cedula' => $request->input('cedula'),
            'birthday' => explode('T',$request->input('birthday'))[0],
            'teaching_level' => $request->input('teaching_level') != 0 ? $request->input('teaching_level') : null
        ]);

        $staff = Staff::find($request->input('staff'));
        $employee->staff()->associate($staff);

        if($request->input('teaching_level') != 0){
            $teaching_level = TeachingLevel::find($request->input('teaching_level'));
            $employee->teaching_level()->associate($teaching_level);
        }

        $employee->save();

        return to_route('employee.index')->with('flash',[
            'alert' => [
                'id' => $employee->id,
                'message' => 'Empleado creado correctamente.',
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
        return Inertia::render('employees::Employee/edit',[
            'employee' => Employee::find($id),
            'staffs' => Staff::with(['benefits','teaching_levels','type'])->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,int $id)
    {
        $request->validate([
            'name' => 'required|max:64',
            'lastname' => 'required|max:64',
            'staff' => 'required',
            // 'email' => 'required|unique:employees,email',
            'email' => 'required',
            'birthday' => 'required',
            'cedula' => 'required'
            // 'cedula' => 'required|max:10|unique:employees,cedula',
        ]);

        $employee = Employee::find($id);

        $employee->name = $request->name;
        $employee->lastname = $request->lastname;
        $employee->email = $request->email;
        $employee->address = $request->address;
        $employee->cedula = $request->cedula;
        $employee->phone = $request->phone;
        $employee->staff = $request->staff;
        $employee->birthday = explode('T',$request->input('birthday'))[0];
        $employee->teaching_level = $request->teaching_level != 0 ? $request->teaching_level : null;

        if($request->teaching_level){
            $teaching_level = TeachingLevel::find($request->teaching_level);
            $employee->teaching_level()->associate($teaching_level);
        }

        $staff = Staff::find($request->staff);
        $employee->staff()->associate($staff);
        $employee->save();

        return to_route('employee.index')->with('flash',[
            'alert' => [
                'id' => $employee->id,
                'message' => 'Cargo actualizado correctamente.',
                'severity' => 'success'
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $destroyed = Employee::destroy($id);

        if($destroyed){
            return to_route('employee.index')->with('flash',[
                'alert' => [
                    'id' => $id,
                    'message' => 'Empleado eliminado correctamente.',
                    'severity' => 'success'
                ]
            ]);
        }
        else {
            return to_route('employee.index')->with('flash',[
                'alert' => [
                    'id' => $id,
                    'message' => 'No se pudo eliminar el empleado, intente nuevamente',
                    'severity' => 'error'
                ]
            ]);
        }
    }
}
