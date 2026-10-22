<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    protected $fillable = [
        'employee_id', 'type', 'start_date', 'end_date', 'reason',
        'status', 'reviewed_by', 'review_note', 'reviewed_at',
    ];

    protected $casts = [
        'start_date' => 'date', 'end_date' => 'date', 'reviewed_at' => 'datetime',
    ];

    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by'); }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function scopeOverlapping(Builder $query, string $from, string $to): Builder
    {
        return $query->whereDate('start_date', '<=', $to)->whereDate('end_date', '>=', $from);
    }

    public function coversDate(Carbon $date): bool
    {
        $day = $date->toDateString();
        return $this->status === 'approved'
            && $this->start_date->toDateString() <= $day
            && $this->end_date->toDateString() >= $day;
    }
}
