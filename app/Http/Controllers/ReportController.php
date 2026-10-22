<?php
namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use App\Support\AttendanceRules;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    private function data(Request $r): array {
        $v=$r->validate(['month'=>['nullable','date_format:Y-m'],'department_id'=>['nullable','exists:departments,id'],'employee_id'=>['nullable','exists:employees,id']]);
        $month=$v['month']??now('Asia/Karachi')->format('Y-m');
        $start=Carbon::createFromFormat('!Y-m',$month,'Asia/Karachi')->startOfMonth();
        $end=$start->copy()->endOfMonth();
        $employees=Employee::with(['department','attendances'=>fn($q)=>$q->whereBetween('attendance_date',[$start->toDateString(),$end->toDateString()])])
            ->when(isset($v['department_id']),fn($q)=>$q->where('department_id',$v['department_id']))
            ->when(isset($v['employee_id']),fn($q)=>$q->whereKey($v['employee_id']))->orderBy('name')->get();
        $rows=[];
        foreach($employees as $employee){
            $present=$late=$absent=0; $byDate=$employee->attendances->keyBy(fn($a)=>$a->attendance_date->toDateString());
            for($d=$start->copy();$d->lte($end);$d->addDay()){
                $status=AttendanceRules::label($d,$byDate->get($d->toDateString()),$employee->active);
                if($status==='Present')$present++; elseif($status==='Late')$late++; elseif($status==='Absent')$absent++;
            }
            $rows[]=compact('employee','present','late','absent');
        }
        return compact('rows','month');
    }
    public function index(Request $r) {
        return view('reports.monthly',$this->data($r)+['departments'=>Department::orderBy('name')->get(),'employees'=>Employee::orderBy('name')->get()]);
    }
    public function csv(Request $r) {
        $data=$this->data($r);
        return response()->streamDownload(function()use($data) {
            $h=fopen('php://output','w');
            fputcsv($h,['Code','Employee','Department','Month','Present','Late','Absent']);
            foreach($data['rows'] as $row){
                $e=$row['employee'];
                fputcsv($h,[$e->employee_code,$e->name,$e->department->name,$data['month'],$row['present'],$row['late'],$row['absent']]);
            }
            fclose($h);
        },'attendance-'.$data['month'].'.csv',['Content-Type'=>'text/csv; charset=UTF-8']);
    }
}
