<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Support\AttendanceRules;
use Illuminate\Database\Seeder;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (!app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Synthetic demo seeding is allowed only in local/testing environments.');
        }

        $super = User::firstOrCreate(['email' => 'superadmin@example.test'], [
            'name' => 'Demo Super Administrator', 'password' => 'DemoSuper123!', 'role' => 'superadmin',
        ]);
        $admin = User::firstOrCreate(['email' => 'admin@example.test'], [
            'name' => 'Demo Administrator', 'password' => 'DemoAdmin123!', 'role' => 'admin',
        ]);
        $departments = [];
        foreach (['Engineering', 'Sales', 'Human Resources', 'Finance'] as $name) {
            $departments[$name] = Department::firstOrCreate(['name' => $name]);
        }

        $people = [
            ['EMP001', 'Ayesha Khan', 'ayesha@example.test', 'Engineering'],
            ['EMP002', 'Bilal Ahmed', 'bilal@example.test', 'Sales'],
            ['EMP003', 'Sara Malik', 'sara@example.test', 'Human Resources'],
            ['EMP004', 'Usman Ali', 'usman@example.test', 'Finance'],
            ['EMP005', 'Hina Shah', 'hina@example.test', 'Engineering'],
            ['EMP006', 'Omar Raza', 'omar@example.test', 'Sales'],
            ['EMP007', 'Zain Hassan', 'zain@example.test', 'Engineering'],
            ['EMP008', 'Maryam Noor', 'maryam@example.test', 'Finance'],
        ];
        $employees = [];
        foreach ($people as [$code, $name, $email, $department]) {
            $user = User::firstOrCreate(['email' => $email], [
                'name' => $name, 'password' => 'DemoEmployee123!', 'role' => 'employee',
            ]);
            $employees[] = Employee::firstOrCreate(['employee_code' => $code], [
                'user_id' => $user->id, 'department_id' => $departments[$department]->id,
                'name' => $name, 'email' => $email, 'active' => true,
            ]);
        }

        // Synthetic salary figures, in PKR, for demo screens only.
        foreach ($employees as $index => $employee) {
            \App\Models\Salary::firstOrCreate(['employee_id' => $employee->id, 'effective_month' => now('Asia/Karachi')->startOfMonth()->toDateString()], [
                'amount' => 50000 + $index * 7500, 'note' => 'Synthetic monthly base salary', 'updated_by' => $super->id,
            ]);
        }

        // Dates stay relative to today so the demo remains useful when installed later.
        $pastDays = [];
        $day = now('Asia/Karachi')->startOfDay()->subDay();
        for ($i = 0; $i < 90 && count($pastDays) < 20; $i++, $day->subDay()) {
            if (AttendanceRules::workingDay($day)) $pastDays[] = $day->toDateString();
        }
        if (!$pastDays) {
            throw new RuntimeException('Configure at least one working day before loading demo attendance.');
        }

        $future = now('Asia/Karachi')->startOfDay()->addDay();
        for ($i = 0; $i < 7 && !AttendanceRules::workingDay($future); $i++) $future->addDay();
        $approvedDay = $pastDays[0];

        foreach ([
            [$employees[0], $approvedDay, 'casual', 'approved', 'Synthetic approved personal leave', 'Demo approval'],
            [$employees[1], $future->toDateString(), 'annual', 'pending', 'Synthetic upcoming personal appointment', null],
            [$employees[2], $future->toDateString(), 'casual', 'rejected', 'Synthetic request for personal work', 'Demo rejection: team coverage required'],
        ] as [$employee, $date, $type, $status, $reason, $note]) {
            LeaveRequest::firstOrCreate([
                'employee_id' => $employee->id, 'start_date' => $date, 'end_date' => $date,
            ], [
                'type' => $type, 'reason' => $reason, 'status' => $status,
                'review_note' => $note,
                'reviewed_by' => $status === 'pending' ? null : $admin->id,
                'reviewed_at' => $status === 'pending' ? null : now('Asia/Karachi'),
            ]);
        }

        foreach ($employees as $index => $employee) {
            if (!$employee->active) continue;
            foreach ($pastDays as $number => $date) {
                // Gaps demonstrate derived absence; preserve the approved leave day.
                if (($index === 0 && $date === $approvedDay) || ($index + $number) % 7 === 0) continue;
                $arrival = \Carbon\Carbon::createFromFormat('H:i', config('attendance.office_start'), 'Asia/Karachi');
                $arrival->addMinutes(($index + $number) % 4 === 0 ? (int) config('attendance.grace_minutes') + 10 : 0);
                Attendance::firstOrCreate(['employee_id' => $employee->id, 'attendance_date' => $date], [
                    'check_in_at' => $arrival->format('H:i:s'),
                    'check_out_at' => $arrival->copy()->addHours(8)->format('H:i:s'),
                    'status' => AttendanceRules::arrivalStatus($arrival->format('H:i:s')),
                ]);
            }
        }
    }
}
