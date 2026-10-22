<?php
namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\AttendanceCorrection;
use App\Models\Department;
use App\Support\AttendanceRules;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttendanceController extends Controller
{
    public function checkIn() {
        $employee=auth()->user()->employee;
        abort_unless($employee && $employee->active,403);
        $now=now('Asia/Karachi'); $day=$now->toDateString();
        if (!AttendanceRules::workingDay($now)) return back()->withErrors(['attendance'=>'Today is not a working day.']);
        try {
            Attendance::create(['employee_id'=>$employee->id,'attendance_date'=>$day,'check_in_at'=>$now->format('H:i:s'),
                'status'=>AttendanceRules::arrivalStatus($now->format('H:i:s'))]);
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            throw ValidationException::withMessages(['attendance'=>'Already checked in today.']);
        }
        return back()->with('message','Checked in.');
    }
    public function checkOut() {
        $employee=auth()->user()->employee;
        abort_unless($employee && $employee->active,403);
        $now=now('Asia/Karachi');
        $updated=Attendance::where('employee_id',$employee->id)
            ->whereDate('attendance_date',$now->toDateString())
            ->whereNotNull('check_in_at')->whereNull('check_out_at')
            ->update(['check_out_at'=>$now->format('H:i:s')]);
        if (!$updated) return back()->withErrors(['attendance'=>'Check in first or already checked out.']);
        return back()->with('message','Checked out.');
    }

    public function manualForm()
    {
        return view('attendance.manual',['employees'=>Employee::orderBy('name')->get()]);
    }

    public function manualStore(Request $r)
    {
        $v=$r->validate([
            'employee_id'=>['required','exists:employees,id'],
            'attendance_date'=>['required','date_format:Y-m-d','before_or_equal:today'],
            'check_in_at'=>['required','date_format:H:i'],
            'check_out_at'=>['nullable','date_format:H:i','after_or_equal:check_in_at'],
            'reason'=>['required','string','min:5','max:1000'],
        ]);
        $date=Carbon::parse($v['attendance_date'],'Asia/Karachi');
        if ($date->gt(now('Asia/Karachi')->startOfDay()) || !AttendanceRules::workingDay($date)) {
            throw ValidationException::withMessages(['attendance_date'=>'Choose a current or past working day.']);
        }
        try {
            DB::transaction(function() use($v) {
                $after=[
                    'employee_id'=>$v['employee_id'],
                    'attendance_date'=>$v['attendance_date'],
                    'check_in_at'=>$v['check_in_at'].':00',
                    'check_out_at'=>empty($v['check_out_at'])?null:$v['check_out_at'].':00',
                    'status'=>AttendanceRules::arrivalStatus($v['check_in_at']),
                ];
                $attendance=Attendance::create($after);
                AttendanceCorrection::create([
                    'attendance_id'=>$attendance->id,'admin_id'=>auth()->id(),
                    'reason'=>$v['reason'],'before'=>['record'=>'missing'],'after'=>$after,
                ]);
            });
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            throw ValidationException::withMessages(['attendance_date'=>'Attendance already exists; use Correct instead.']);
        }
        return redirect()->route('attendance.index',['date'=>$v['attendance_date']])->with('message','Missing record entered with correction history.');
    }

    public function index(Request $r) {
        $v=$r->validate(['date'=>['nullable','date_format:Y-m-d'],'department_id'=>['nullable','exists:departments,id'],'employee_id'=>['nullable','exists:employees,id']]);
        $date=$v['date']??now('Asia/Karachi')->toDateString();
        $q=Employee::with(['department','attendances'=>fn($q)=>$q->whereDate('attendance_date',$date),
            'leaveRequests'=>fn($q)=>$q->approved()->overlapping($date,$date)])
            ->when(isset($v['department_id']),fn($q)=>$q->where('department_id',$v['department_id']))
            ->when(isset($v['employee_id']),fn($q)=>$q->whereKey($v['employee_id']));
        return view('attendance.index',['items'=>$q->orderBy('name')->paginate(15)->withQueryString(),
            'date'=>$date,'departments'=>Department::orderBy('name')->get(),'employees'=>Employee::orderBy('name')->get()]);
    }
    public function edit(Attendance $attendance) {
        return view('attendance.edit',['attendance'=>$attendance->load('employee','corrections.admin')]);
    }
    public function update(Request $r, Attendance $attendance) {
        $v=$r->validate([
            'check_in_at'=>['required','date_format:H:i'],
            'check_out_at'=>['nullable','date_format:H:i','after_or_equal:check_in_at'],
            'reason'=>['required','string','min:5','max:1000'],
        ]);
        if (!empty($v['check_out_at']) && empty($v['check_in_at']))
            throw ValidationException::withMessages(['check_out_at'=>'Check-in required before check-out.']);
        $new=['check_in_at'=>empty($v['check_in_at'])?null:$v['check_in_at'].':00',
            'check_out_at'=>empty($v['check_out_at'])?null:$v['check_out_at'].':00',
            'status'=>empty($v['check_in_at'])?'present':AttendanceRules::arrivalStatus($v['check_in_at'])];
        DB::transaction(function()use($attendance,$new,$v) {
            $locked=Attendance::whereKey($attendance->id)->lockForUpdate()->firstOrFail();
            $before=$locked->only('check_in_at','check_out_at','status');
            $locked->update($new);
            AttendanceCorrection::create(['attendance_id'=>$locked->id,'admin_id'=>auth()->id(),
                'reason'=>$v['reason'],'before'=>$before,'after'=>$new]);
        });
        return redirect()->route('attendance.edit',$attendance)->with('message','Correction saved with history.');
    }
    public function mine(Request $r) {
        $employee=auth()->user()->employee;
        abort_unless($employee,403);
        $r->validate(['month'=>['nullable','date_format:Y-m']]);
        $month=$r->input('month',now('Asia/Karachi')->format('Y-m'));
        $start=Carbon::createFromFormat('!Y-m',$month,'Asia/Karachi')->startOfMonth();
        $end=$start->copy()->endOfMonth();
        $records=$employee->attendances()->whereBetween('attendance_date',[$start->toDateString(),$end->toDateString()])
            ->get()->keyBy(fn($x)=>$x->attendance_date->toDateString());
        $leaves=$employee->leaveRequests()->approved()->overlapping($start->toDateString(),$end->toDateString())->get();
        $days=[];
        for($d=$start->copy();$d->lte($end);$d->addDay()){
            $record=$records->get($d->toDateString());
            $days[]=['date'=>$d->toDateString(),'record'=>$record,
                'label'=>AttendanceRules::label($d,$record,$employee->active,$leaves->contains(fn($leave)=>$leave->coversDate($d)))];
        }
        return view('attendance.mine',compact('employee','month','days'));
    }
}
