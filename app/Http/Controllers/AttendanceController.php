<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttendanceController extends Controller
{
    public function employees(): JsonResponse
    {
        return response()->json(Employee::orderBy('name')->paginate(20));
    }

    public function storeEmployee(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_code' => ['required', 'string', 'max:50', 'unique:employees,employee_code'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:employees,email'],
            'department' => ['nullable', 'string', 'max:255'],
        ]);

        return response()->json(Employee::create($validated), 201);
    }

    public function checkIn(Employee $employee): JsonResponse
    {
        if (!$employee->active) {
            throw ValidationException::withMessages(['employee' => 'Inactive employee cannot check in.']);
        }

        $today = now()->toDateString();
        $attendance = DB::transaction(function () use ($employee, $today) {
            $record = Attendance::firstOrCreate(
                ['employee_id' => $employee->id, 'attendance_date' => $today],
                ['status' => 'present']
            );

            if ($record->check_in_at !== null) {
                throw ValidationException::withMessages(['attendance' => 'Already checked in today.']);
            }

            $record->update(['check_in_at' => now()->format('H:i:s')]);
            return $record->fresh();
        });

        return response()->json($attendance);
    }

    public function checkOut(Employee $employee): JsonResponse
    {
        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('attendance_date', now()->toDateString())
            ->first();

        if (!$attendance || !$attendance->check_in_at) {
            throw ValidationException::withMessages(['attendance' => 'Check in first.']);
        }
        if ($attendance->check_out_at !== null) {
            throw ValidationException::withMessages(['attendance' => 'Already checked out today.']);
        }

        $attendance->update(['check_out_at' => now()->format('H:i:s')]);
        return response()->json($attendance->fresh());
    }

    public function report(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from' => ['required', 'date_format:Y-m-d'],
            'to' => ['required', 'date_format:Y-m-d', 'after_or_equal:from'],
            'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
        ]);

        $query = Attendance::with('employee:id,employee_code,name')
            ->whereBetween('attendance_date', [$validated['from'], $validated['to']]);

        if (isset($validated['employee_id'])) {
            $query->where('employee_id', $validated['employee_id']);
        }

        return response()->json($query->orderByDesc('attendance_date')->paginate(25));
    }
}
