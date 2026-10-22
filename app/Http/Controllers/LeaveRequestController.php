<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Support\AttendanceRules;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LeaveRequestController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'status' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])],
        ]);
        $admin = $request->user()->role === 'admin';
        abort_unless($admin || $request->user()->employee, 403);

        $items = LeaveRequest::with(['employee.department', 'reviewer'])
            ->when(!$admin, fn ($query) => $query->where('employee_id', $request->user()->employee->id))
            ->when(!empty($data['status']), fn ($query) => $query->where('status', $data['status']))
            ->latest()->paginate(15)->withQueryString();

        return view('leaves.index', compact('items', 'admin'));
    }

    public function store(Request $request)
    {
        $employee = $request->user()->employee;
        abort_unless($employee && $employee->active, 403);
        $data = $request->validate([
            'type' => ['required', Rule::in(['casual', 'sick', 'annual'])],
            'start_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);
        $start = Carbon::parse($data['start_date'], 'Asia/Karachi');
        $end = Carbon::parse($data['end_date'], 'Asia/Karachi');
        if ($start->diffInDays($end) > 30) {
            throw ValidationException::withMessages(['end_date' => 'Maximum request length is 31 calendar days.']);
        }
        $workingDays = 0;
        for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
            if (AttendanceRules::workingDay($day)) $workingDays++;
        }
        if (!$workingDays) {
            throw ValidationException::withMessages(['start_date' => 'Select a range containing a working day.']);
        }

        DB::transaction(function () use ($employee, $data) {
            // Serialize submissions for this employee to prevent overlapping concurrent requests.
            $locked = Employee::whereKey($employee->id)->lockForUpdate()->firstOrFail();
            abort_unless($locked->active, 403);
            $overlap = $locked->leaveRequests()->whereIn('status', ['pending', 'approved'])
                ->overlapping($data['start_date'], $data['end_date'])->exists();
            if ($overlap) {
                throw ValidationException::withMessages(['start_date' => 'A pending or approved leave already overlaps these dates.']);
            }
            $locked->leaveRequests()->create($data + ['status' => 'pending']);
        });

        return redirect()->route('leaves.index')->with('message', 'Leave request submitted for review.');
    }

    public function review(Request $request, LeaveRequest $leaveRequest)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['approved', 'rejected'])],
            'review_note' => ['nullable', 'required_if:status,rejected', 'string', 'min:5', 'max:1000'],
        ]);
        DB::transaction(function () use ($request, $leaveRequest, $data) {
            $locked = LeaveRequest::whereKey($leaveRequest->id)->lockForUpdate()->firstOrFail();
            if ($locked->status !== 'pending') {
                throw ValidationException::withMessages(['status' => 'This request has already been reviewed.']);
            }
            $locked->update([
                'status' => $data['status'],
                'review_note' => $data['review_note'] ?? null,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now('Asia/Karachi'),
            ]);
        });

        return redirect()->route('leaves.index')->with('message', 'Leave request reviewed.');
    }
}
