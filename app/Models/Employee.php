<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected $fillable = ['employee_code', 'name', 'email', 'department', 'active'];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
}
