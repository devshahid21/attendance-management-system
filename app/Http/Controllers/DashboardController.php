<?php
namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use App\Support\AttendanceRules;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index() {
        $user = auth()->user();
        if ($user->role === 'admin') return view('dashboard', [
            'pendingLeaves' => \App\Models\LeaveRequest::where('status','pending')->count(),
            'employees' => Employee::where('active',true)->count(),
            'today' => Attendance::whereDate('attendance_date', now('Asia/Karachi')->toDateString())->count(),
        ]);
        $employee = $user->employee;
        abort_unless($employee, 403);
        $today = now('Asia/Karachi')->startOfDay();
        $attendance = $employee->attendances()->whereDate('attendance_date',$today)->first();
        $approvedLeave=$employee->leaveRequests()->approved()->overlapping($today->toDateString(),$today->toDateString())->exists();
        return view('dashboard', ['employee'=>$employee,'attendance'=>$attendance,
            'status'=>AttendanceRules::label($today,$attendance,$employee->active,$approvedLeave)]);
    }
}
