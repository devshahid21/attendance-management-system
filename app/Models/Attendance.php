<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attendance extends Model
{
    protected $fillable = ['employee_id','attendance_date','check_in_at','check_out_at','status'];
    protected function casts(): array { return ['attendance_date'=>'date']; }
    public function setAttendanceDateAttribute($value): void
    {
        $this->attributes['attendance_date'] = \Illuminate\Support\Carbon::parse($value)->toDateString();
    }
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
    public function corrections(): HasMany { return $this->hasMany(AttendanceCorrection::class); }
}
