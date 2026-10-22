<?php

use App\Http\Controllers\AttendanceController;
use Illuminate\Support\Facades\Route;

// Include this file from routes/web.php, after setting up your application's authentication.
Route::middleware(['auth'])->prefix('attendance')->name('attendance.')->group(function () {
    Route::get('/employees', [AttendanceController::class, 'employees'])->name('employees.index');
    Route::post('/employees', [AttendanceController::class, 'storeEmployee'])->name('employees.store');
    Route::post('/employees/{employee}/check-in', [AttendanceController::class, 'checkIn'])->name('check-in');
    Route::post('/employees/{employee}/check-out', [AttendanceController::class, 'checkOut'])->name('check-out');
    Route::get('/report', [AttendanceController::class, 'report'])->name('report');
});
