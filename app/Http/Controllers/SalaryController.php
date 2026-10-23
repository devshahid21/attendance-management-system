<?php
namespace App\Http\Controllers;
use App\Models\Employee;
use App\Models\Salary;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SalaryController extends Controller {
    public function index(Request $request) {
        $v=$request->validate(['month'=>['nullable','date_format:Y-m'],'q'=>['nullable','string','max:100']]);
        $month=$v['month']??now('Asia/Karachi')->format('Y-m');
        $employees=Employee::with('department')
            ->when(!empty($v['q']),fn($q)=>$q->where('name','like','%'.$v['q'].'%'))
            ->orderBy('name')->paginate(12)->withQueryString();
        $salaries=Salary::where('effective_month',$month.'-01')->whereIn('employee_id',$employees->pluck('id'))->get()->keyBy('employee_id');
        return view('salaries.index',compact('employees','salaries','month'));
    }
    public function store(Request $request) {
        $v=$request->validate([
            'employee_id'=>['required','exists:employees,id'],
            'month'=>['required','date_format:Y-m'],
            'amount'=>['required','numeric','min:0','max:99999999.99'],
            'note'=>['nullable','string','max:255'],
        ]);
        Salary::updateOrCreate(
            ['employee_id'=>$v['employee_id'],'effective_month'=>$v['month'].'-01'],
            ['amount'=>$v['amount'],'note'=>$v['note']??null,'updated_by'=>$request->user()->id]
        );
        return redirect()->route('salaries.index',['month'=>$v['month']])->with('message','Monthly salary saved.');
    }
}
