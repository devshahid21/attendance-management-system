<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Salary extends Model {
    protected $fillable=['employee_id','effective_month','amount','note','updated_by'];
    protected $casts=['amount'=>'decimal:2','effective_month'=>'date'];
    public function setEffectiveMonthAttribute($value): void { $this->attributes['effective_month'] = \Illuminate\Support\Carbon::parse($value)->startOfMonth()->toDateString(); }
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
}
