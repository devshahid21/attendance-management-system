<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Support\AttendanceRules;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $day = now('Asia/Karachi')->startOfDay();
        $date = $day->toDateString();

        if (in_array($user->role, ['admin','superadmin'], true)) {
            $roster = Employee::where('active', true)->with([
                'attendances' => fn ($query) => $query->whereDate('attendance_date', $date),
                'leaveRequests' => fn ($query) => $query->approved()->overlapping($date, $date),
            ])->get();
            $weekStart = $day->copy()->subDays(6);
            $counts = Attendance::whereBetween('attendance_date', [$weekStart->toDateString(), $date])
                ->selectRaw('attendance_date, COUNT(*) as total')->groupBy('attendance_date')->pluck('total', 'attendance_date');
            $week = [];
            for ($d = $weekStart->copy(); $d->lte($day); $d->addDay()) {
                $week[] = ['label' => $d->format('D'), 'date' => $d->toDateString(), 'count' => (int) ($counts[$d->toDateString()] ?? 0)];
            }
            return view('dashboard', [
                'employees' => $roster->count(),
                'today' => $roster->filter(fn ($employee) => $employee->attendances->isNotEmpty())->count(),
                'onLeave' => $roster->filter(fn ($employee) => $employee->attendances->isEmpty() && $employee->leaveRequests->isNotEmpty() && AttendanceRules::workingDay($day))->count(),
                'pendingLeaves' => LeaveRequest::where('status', 'pending')->count(),
                'week' => $week, 'chartMax' => max(1, max(array_column($week, 'count'))),
                'recent' => Attendance::with('employee.department')->orderByDesc('attendance_date')->orderByDesc('id')->limit(5)->get(),
            ]);
        }

        $employee = $user->employee;
        abort_unless($employee, 403);
        $employee->load('department');
        $attendance = $employee->attendances()->whereDate('attendance_date', $date)->first();
        $start = $day->copy()->startOfMonth()->toDateString();
        $records = $employee->attendances()->whereBetween('attendance_date', [$start, $date])->get()
            ->keyBy(fn ($record) => $record->attendance_date->toDateString());
        $leaves = $employee->leaveRequests()->approved()->overlapping($start, $date)->get();
        $summary = ['Present' => 0, 'Late' => 0, 'Absent' => 0, 'Leave' => 0];
        for ($d = Carbon::parse($start, 'Asia/Karachi'); $d->lte($day); $d->addDay()) {
            $label = AttendanceRules::label($d, $records->get($d->toDateString()), $employee->active, $leaves->contains(fn ($leave) => $leave->coversDate($d)));
            if (array_key_exists($label, $summary)) $summary[$label]++;
        }
        $approved = $leaves->contains(fn ($leave) => $leave->coversDate($day));

        return view('dashboard', [
            'employee' => $employee, 'attendance' => $attendance,
            'status' => AttendanceRules::label($day, $attendance, $employee->active, $approved),
            'workingDay' => AttendanceRules::workingDay($day), 'summary' => $summary,
            'pendingLeaves' => $employee->leaveRequests()->where('status', 'pending')->count(),
            'recent' => $employee->attendances()->orderByDesc('attendance_date')->limit(5)->get(),
        ]);
    }
}
