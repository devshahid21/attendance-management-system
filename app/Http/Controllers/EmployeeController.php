<?php
namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index(Request $r) {
        $q=Employee::with('department')->when($r->filled('q'),function($q) use($r) {
            $search=$r->string('q')->toString();
            $q->where(fn($sub)=>$sub->where('name','like',"%{$search}%")->orWhere('employee_code','like',"%{$search}%"));
        })->when($r->filled('department_id'),fn($q)=>$q->where('department_id',$r->integer('department_id')));
        return view('employees.index',['items'=>$q->latest()->paginate(12)->withQueryString(),'departments'=>Department::orderBy('name')->get()]);
    }
    public function create(){ return view('employees.form',['employee'=>new Employee,'departments'=>Department::orderBy('name')->get()]); }
    public function show(Employee $employee){ return view('employees.show', ['employee'=>$employee->load('department','user')]); }
    public function edit(Employee $employee){ return view('employees.form',['employee'=>$employee,'departments'=>Department::orderBy('name')->get()]); }
    private function rules(?Employee $e=null): array {
        return [
            'name'=>['required','string','max:150'],
            'employee_code'=>['required','string','max:40',Rule::unique('employees','employee_code')->ignore($e?->id)],
            'email'=>['required','email','max:255',Rule::unique('users','email')->ignore($e?->user_id)],
            'department_id'=>['required','exists:departments,id'],
            'password'=>$e ? ['nullable','string','min:8','confirmed'] : ['required','string','min:8','confirmed'],
        ];
    }
    public function store(Request $r) {
        $d=$r->validate($this->rules());
        DB::transaction(function() use($d){
            $u=User::create(['name'=>$d['name'],'email'=>$d['email'],'password'=>$d['password'],'role'=>'employee']);
            Employee::create(['user_id'=>$u->id,'name'=>$d['name'],'email'=>$d['email'],
                'employee_code'=>$d['employee_code'],'department_id'=>$d['department_id'],'active'=>true]);
        });
        return redirect()->route('employees.index')->with('message','Employee account created.');
    }
    public function update(Request $r, Employee $employee) {
        $d=$r->validate($this->rules($employee));
        DB::transaction(function()use($d,$employee){
            $employee->update(collect($d)->only('name','email','employee_code','department_id')->all());
            $values=['name'=>$d['name'],'email'=>$d['email']];
            if (!empty($d['password'])) $values['password']=$d['password'];
            $employee->user->update($values);
        });
        return redirect()->route('employees.show',$employee)->with('message','Employee updated.');
    }
    public function deactivate(Employee $employee) {
        $employee->update(['active'=>false]);
        return back()->with('message','Employee deactivated; history retained.');
    }
    public function activate(Employee $employee) {
        $employee->update(['active'=>true]);
        return back()->with('message','Employee reactivated.');
    }
}
