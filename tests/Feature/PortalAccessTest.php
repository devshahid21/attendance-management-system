<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PortalAccessTest extends TestCase
{
    use RefreshDatabase;

    private function fixture(): array
    {
        $department = Department::create(['name' => 'Engineering']);
        $admin = User::create(['name' => 'Portal Admin', 'email' => 'admin@portal.test', 'password' => 'password123', 'role' => 'admin']);
        $user = User::create(['name' => 'Portal Employee', 'email' => 'employee@portal.test', 'password' => 'password123', 'role' => 'employee']);
        $employee = Employee::create(['user_id' => $user->id, 'department_id' => $department->id, 'employee_code' => 'P001', 'name' => $user->name, 'email' => $user->email, 'active' => true]);
        $attendance = Attendance::create(['employee_id' => $employee->id, 'attendance_date' => now('Asia/Karachi')->toDateString(), 'check_in_at' => '09:00:00', 'status' => 'present']);
        $leave = $employee->leaveRequests()->create(['type' => 'casual', 'start_date' => now()->addDays(2)->toDateString(), 'end_date' => now()->addDays(2)->toDateString(), 'reason' => 'Personal appointment', 'status' => 'pending']);
        return compact('department', 'admin', 'user', 'employee', 'attendance', 'leave');
    }

    public function test_admin_portal_renders_every_management_screen(): void
    {
        $f = $this->fixture();
        $this->actingAs($f['admin']);
        foreach (['/', '/employees', '/employees/create', '/employees/'.$f['employee']->id, '/employees/'.$f['employee']->id.'/edit', '/departments', '/attendance', '/attendance/add', '/attendance/'.$f['attendance']->id.'/edit', '/leaves', '/reports', '/access', '/account'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get('/')->assertSee('Admin portal')->assertSee('Team directory');
        $this->post('/check-in')->assertForbidden();
        $this->post('/check-out')->assertForbidden();
        $this->get('/my-profile')->assertForbidden();
        $this->get('/my-attendance')->assertForbidden();
    }

    public function test_employee_is_blocked_from_all_admin_read_and_write_routes(): void
    {
        $f = $this->fixture();
        $this->actingAs($f['user']);
        foreach (['/employees', '/employees/create', '/employees/'.$f['employee']->id, '/employees/'.$f['employee']->id.'/edit', '/departments', '/attendance', '/attendance/add', '/attendance/'.$f['attendance']->id.'/edit', '/reports', '/reports/export', '/access'] as $url) {
            $this->get($url)->assertForbidden();
        }
        foreach ([
            ['POST', '/employees'], ['PUT', '/employees/'.$f['employee']->id],
            ['POST', '/employees/'.$f['employee']->id.'/deactivate'], ['POST', '/employees/'.$f['employee']->id.'/activate'],
            ['POST', '/departments'], ['PUT', '/departments/'.$f['department']->id], ['DELETE', '/departments/'.$f['department']->id],
            ['POST', '/attendance/add'], ['PUT', '/attendance/'.$f['attendance']->id],
            ['PATCH', '/leaves/'.$f['leave']->id],
        ] as [$method, $url]) {
            $this->call($method, $url, ['role' => 'admin', 'status' => 'approved'])->assertForbidden();
        }
        foreach (['/', '/my-profile', '/my-attendance', '/leaves', '/account'] as $url) $this->get($url)->assertOk();
        $this->get('/')->assertSee('Employee portal')->assertDontSee('Team directory')->assertDontSee('Roles &amp; access', false);
    }

    public function test_guest_cannot_open_private_portals_or_submit_actions(): void
    {
        foreach (['/', '/employees', '/departments', '/attendance', '/reports', '/leaves', '/access', '/account', '/my-profile', '/my-attendance'] as $url) $this->get($url)->assertRedirect('/login');
        $this->post('/check-in')->assertRedirect('/login');
        $this->put('/account/password', [])->assertRedirect('/login');
    }

    public function test_password_change_requires_current_password_and_changes_only_own_account(): void
    {
        $f = $this->fixture();
        $this->actingAs($f['user']);
        $this->put('/account/password', ['current_password' => 'wrong-password', 'password' => 'newSecret123!', 'password_confirmation' => 'newSecret123!'])->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('password123', $f['user']->fresh()->password));
        $this->put('/account/password', ['current_password' => 'password123', 'password' => 'newSecret123!', 'password_confirmation' => 'newSecret123!', 'user_id' => $f['admin']->id, 'role' => 'admin'])->assertRedirect('/account');
        $this->assertTrue(Hash::check('newSecret123!', $f['user']->fresh()->password));
        $this->assertTrue(Hash::check('password123', $f['admin']->fresh()->password));
        $this->assertSame('employee', $f['user']->fresh()->role);
    }

    public function test_admin_manages_accounts_without_role_escalation_and_retains_history(): void
    {
        $f = $this->fixture();
        $this->actingAs($f['admin']);
        $input = ['name' => 'New Employee', 'email' => 'new@portal.test', 'employee_code' => 'P002', 'department_id' => $f['department']->id, 'password' => 'password123', 'password_confirmation' => 'password123', 'role' => 'admin'];
        $this->post('/employees', $input)->assertRedirect('/employees');
        $created = User::where('email', $input['email'])->firstOrFail();
        $this->assertSame('employee', $created->role);
        $employee = $created->employee;
        $this->put('/employees/'.$employee->id, array_replace($input, ['name' => 'Updated Employee', 'password' => '', 'password_confirmation' => '']))->assertRedirect('/employees/'.$employee->id);
        $this->assertSame('Updated Employee', $created->fresh()->name);
        $this->assertSame('employee', $created->fresh()->role);
        $this->assertTrue(Hash::check('password123', $created->fresh()->password));
        $this->post('/employees/'.$f['employee']->id.'/deactivate')->assertRedirect();
        $this->assertFalse($f['employee']->fresh()->active);
        $this->assertDatabaseHas('attendances', ['id' => $f['attendance']->id]);
        $this->post('/employees/'.$f['employee']->id.'/activate')->assertRedirect();
        $this->assertTrue($f['employee']->fresh()->active);
        $this->delete('/departments/'.$f['department']->id)->assertSessionHasErrors('department');
        $this->assertDatabaseHas('departments', ['id' => $f['department']->id]);
    }

    public function test_admin_can_correct_checkin_before_checkout_with_audit_reason(): void
    {
        $f = $this->fixture();
        $this->actingAs($f['admin'])->put('/attendance/'.$f['attendance']->id, ['check_in_at' => '09:05', 'check_out_at' => '', 'reason' => 'Corrected against register'])->assertRedirect('/attendance/'.$f['attendance']->id.'/edit');
        $this->assertDatabaseHas('attendances', ['id' => $f['attendance']->id, 'check_in_at' => '09:05:00', 'check_out_at' => null]);
        $this->assertDatabaseHas('attendance_corrections', ['attendance_id' => $f['attendance']->id, 'admin_id' => $f['admin']->id]);
        $this->put('/attendance/'.$f['attendance']->id, ['check_in_at' => '', 'reason' => 'Clearing checkin attempt'])->assertSessionHasErrors('check_in_at');
    }

    public function test_inactive_employee_has_read_only_history_and_login_is_rate_limited(): void
    {
        $f = $this->fixture();
        $f['employee']->update(['active' => false]);
        $this->actingAs($f['user'])->get('/my-attendance')->assertOk();
        $this->post('/check-in')->assertForbidden();
        $this->post('/check-out')->assertForbidden();
        $this->post('/leaves', [])->assertForbidden();
        auth()->logout();
        for ($i = 0; $i < 5; $i++) $this->post('/login', ['email' => 'invalid@portal.test', 'password' => 'bad'])->assertRedirect();
        $this->post('/login', ['email' => 'invalid@portal.test', 'password' => 'bad'])->assertStatus(429);
    }
}
