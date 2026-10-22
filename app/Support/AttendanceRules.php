<?php
namespace App\Support;

use Carbon\Carbon;
use App\Models\Attendance;

class AttendanceRules
{
    public static function workingDay(Carbon $date): bool
    {
        return in_array($date->dayOfWeekIso, config('attendance.working_days'), true);
    }
    public static function label(Carbon $date, ?Attendance $attendance, bool $active = true): string
    {
        if (!self::workingDay($date)) return 'Non-working';
        if ($date->isAfter(now('Asia/Karachi')->startOfDay())) return 'Future';
        if ($attendance) return ucfirst($attendance->status);
        if ($date->isSameDay(now('Asia/Karachi'))) return 'Pending';
        return $active ? 'Absent' : 'Inactive';
    }
    public static function arrivalStatus(string $time): string
    {
        $start = Carbon::createFromFormat('H:i', config('attendance.office_start'), 'Asia/Karachi');
        $limit = $start->copy()->addMinutes((int) config('attendance.grace_minutes'));
        $arrival = Carbon::createFromFormat('H:i:s', strlen($time) === 5 ? $time.':00' : $time, 'Asia/Karachi');
        return $arrival->greaterThan($limit) ? 'late' : 'present';
    }
}
