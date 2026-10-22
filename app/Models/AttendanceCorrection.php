<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceCorrection extends Model
{
    protected $fillable = ['attendance_id','admin_id','reason','before','after'];
    protected function casts(): array { return ['before'=>'array','after'=>'array']; }
    public function attendance(): BelongsTo { return $this->belongsTo(Attendance::class); }
    public function admin(): BelongsTo { return $this->belongsTo(User::class, 'admin_id'); }
}
