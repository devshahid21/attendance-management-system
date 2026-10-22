<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\AccountController;

Route::get('/login',[AuthController::class,'form'])->middleware('guest')->name('login');
Route::post('/login',[AuthController::class,'login'])->middleware(['guest','throttle:5,1'])->name('login.submit');
Route::post('/logout',[AuthController::class,'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function(){
    Route::get('/account',[AccountController::class,'edit'])->name('account.edit');
    Route::put('/account/password',[AccountController::class,'password'])->name('account.password');
    Route::get('/leaves',[LeaveRequestController::class,'index'])->name('leaves.index');
    Route::get('/',[DashboardController::class,'index'])->name('dashboard');
    Route::middleware('role:employee')->group(function(){
        Route::post('/leaves',[LeaveRequestController::class,'store'])->name('leaves.store');
        Route::post('/check-in',[AttendanceController::class,'checkIn'])->name('checkin');
        Route::post('/check-out',[AttendanceController::class,'checkOut'])->name('checkout');
        Route::get('/my-attendance',[AttendanceController::class,'mine'])->name('attendance.mine');
        Route::get('/my-profile',fn()=>view('employees.profile',['employee'=>auth()->user()->employee?->load('department')]))->name('profile');
    });
    Route::middleware('role:admin')->group(function(){
        Route::view('/access','access.index')->name('access.index');
        Route::patch('/leaves/{leaveRequest}',[LeaveRequestController::class,'review'])->name('leaves.review');
        Route::resource('departments',DepartmentController::class)->only(['index','store','update','destroy']);
        Route::resource('employees',EmployeeController::class)->except(['destroy']);
        Route::post('/employees/{employee}/deactivate',[EmployeeController::class,'deactivate'])->name('employees.deactivate');
        Route::post('/employees/{employee}/activate',[EmployeeController::class,'activate'])->name('employees.activate');
        Route::get('/attendance',[AttendanceController::class,'index'])->name('attendance.index');
        Route::get('/attendance/add',[AttendanceController::class,'manualForm'])->name('attendance.manual');
        Route::post('/attendance/add',[AttendanceController::class,'manualStore'])->name('attendance.manual.store');
        Route::get('/attendance/{attendance}/edit',[AttendanceController::class,'edit'])->name('attendance.edit');
        Route::put('/attendance/{attendance}',[AttendanceController::class,'update'])->name('attendance.update');
        Route::get('/reports',[ReportController::class,'index'])->name('reports.index');
        Route::get('/reports/export',[ReportController::class,'csv'])->name('reports.csv');
    });
});
