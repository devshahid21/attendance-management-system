<?php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Attendance;
use App\Support\AttendanceRules;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void {
        $this->get('/')->assertRedirect('/login');
        $this->get('/login')->assertOk();
    }

    public function test_employee_can_login_with_hashed_password_and_logout(): void {
        $user = $this->employee(8);
        $this->assertNotSame('secret12345', $user->getRawOriginal('password'));
        $this->post('/login', ['email' => $user->email, 'password' => 'secret12345'])
            ->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
        $this->get('/login')->assertRedirect('/');
        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    private function employee(int $n): User {
        $user=User::create(['name'=>"Person {$n}",'email'=>"person{$n}@example.test",'password'=>'secret12345','role'=>'employee']);
        $dept=Department::firstOrCreate(['name'=>'Engineering']);
        Employee::create(['user_id'=>$user->id,'department_id'=>$dept->id,'employee_code'=>"E{$n}",
            'name'=>$user->name,'email'=>$user->email,'active'=>true]);
        return $user;
    }
    public function test_employee_cannot_access_admin_or_other_employee_views(): void {
        $a=$this->employee(1); $b=$this->employee(2);
        $this->actingAs($a)->get('/employees')->assertForbidden();
        $this->actingAs($a)->get('/employees/'.$b->employee->id)->assertForbidden();
        $this->actingAs($a)->get('/reports')->assertForbidden();
        $this->actingAs($a)->get('/my-profile')->assertOk()->assertSee('Person 1')->assertDontSee('Person 2');
    }
    public function test_duplicate_check_in_is_rejected(): void {
        $user=$this->employee(3);
        Carbon::setTestNow(Carbon::parse('2026-10-22 08:58:00','Asia/Karachi'));
        try {
            $this->actingAs($user)->post('/check-in')->assertRedirect();
            $this->actingAs($user)->post('/check-in')->assertSessionHasErrors('attendance');
            $this->assertDatabaseCount('attendances',1);
        } finally { Carbon::setTestNow(); }
    }
    public function test_checkout_requires_checkin_and_only_once(): void {
        $user=$this->employee(4);
        Carbon::setTestNow(Carbon::parse('2026-10-22 09:00:00','Asia/Karachi'));
        try {
            $this->actingAs($user)->post('/check-out')->assertSessionHasErrors('attendance');
            $this->actingAs($user)->post('/check-in')->assertRedirect();
            $this->actingAs($user)->post('/check-out')->assertRedirect();
            $this->actingAs($user)->post('/check-out')->assertSessionHasErrors('attendance');
            $this->assertDatabaseCount('attendances',1);
        } finally { Carbon::setTestNow(); }
    }
    public function test_status_rules_handle_late_absent_future_and_weekends(): void {
        config()->set('attendance.office_start','09:00');
        config()->set('attendance.grace_minutes',15);
        config()->set('attendance.working_days',[1,2,3,4,5]);
        Carbon::setTestNow(Carbon::parse('2026-10-22 14:00:00','Asia/Karachi'));
        try {
            $this->assertSame('present',AttendanceRules::arrivalStatus('09:15:00'));
            $this->assertSame('late',AttendanceRules::arrivalStatus('09:16:00'));
            $this->assertSame('Absent',AttendanceRules::label(Carbon::parse('2026-10-21','Asia/Karachi'),null));
            $this->assertSame('Pending',AttendanceRules::label(Carbon::parse('2026-10-22','Asia/Karachi'),null));
            $this->assertSame('Non-working',AttendanceRules::label(Carbon::parse('2026-10-24','Asia/Karachi'),null));
            $this->assertSame('Future',AttendanceRules::label(Carbon::parse('2026-10-26','Asia/Karachi'),null));
        } finally { Carbon::setTestNow(); }
    }
    public function test_employee_cannot_forge_employee_id_on_checkin(): void {
        $a=$this->employee(5); $b=$this->employee(6);
        Carbon::setTestNow(Carbon::parse('2026-10-22 09:00:00','Asia/Karachi'));
        try {
            $this->actingAs($a)->post('/check-in',['employee_id'=>$b->employee->id])->assertRedirect();
            $this->assertDatabaseHas('attendances',['employee_id'=>$a->employee->id]);
            $this->assertDatabaseMissing('attendances',['employee_id'=>$b->employee->id]);
        } finally { Carbon::setTestNow(); }
    }
    public function test_admin_can_add_missing_attendance_with_reason_and_history(): void {
        $employeeUser=$this->employee(7);
        $admin=User::create(['name'=>'Admin','email'=>'admin@example.test','password'=>'secret12345','role'=>'admin']);
        Carbon::setTestNow(Carbon::parse('2026-10-22 14:00:00','Asia/Karachi'));
        try {
            $this->actingAs($employeeUser)->get('/attendance/add')->assertForbidden();
            $this->actingAs($admin)->post('/attendance/add',[
                'employee_id'=>$employeeUser->employee->id,
                'attendance_date'=>'2026-10-21',
                'check_in_at'=>'09:25',
                'check_out_at'=>'17:00',
                'reason'=>'Manual verified paper register correction',
            ])->assertRedirect();
            $this->assertDatabaseHas('attendances',[
                'employee_id'=>$employeeUser->employee->id,'attendance_date'=>'2026-10-21','status'=>'late'
            ]);
            $this->assertDatabaseHas('attendance_corrections',['reason'=>'Manual verified paper register correction','admin_id'=>$admin->id]);
            $this->actingAs($admin)->post('/attendance/add',[
                'employee_id'=>$employeeUser->employee->id,'attendance_date'=>'2026-10-21',
                'check_in_at'=>'09:25','reason'=>'Duplicate attempt',
            ])->assertSessionHasErrors('attendance_date');
            $this->assertDatabaseCount('attendances',1);
        } finally { Carbon::setTestNow(); }
    }

}
