<?php
namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    public function index() { return view('departments.index', ['items'=>Department::withCount('employees')->orderBy('name')->paginate(12)]); }
    public function store(Request $r) {
        Department::create($r->validate(['name'=>['required','string','max:100','unique:departments,name']]));
        return back()->with('message','Department created.');
    }
    public function update(Request $r, Department $department) {
        $department->update($r->validate(['name'=>['required','string','max:100',Rule::unique('departments','name')->ignore($department->id)]]));
        return back()->with('message','Department updated.');
    }
    public function destroy(Department $department) {
        if ($department->employees()->exists()) return back()->withErrors(['department'=>'Department has employees; move them first.']);
        $department->delete(); return back()->with('message','Department deleted.');
    }
}
