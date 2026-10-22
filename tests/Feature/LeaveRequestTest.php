<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-10-22 14:00:00', 'Asia/Karachi'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function employee(int $number): User
    {
        $user = User::create([
            'name' => 'Demo '.$number, 'email' => 'leave'.$number.'@example.test',
            'password' => 'password123', 'role' => 'employee',
        ]);
        $department = Department::firstOrCreate(['name' => 'Engineering']);
        Employee::create([
            'user_id' => $user->id, 'department_id' => $department->id,
            'employee_code' => 'L'.$number, 'name' => $user->name, 'email' => $user->email, 'active' => true,
        ]);
        return $user;
    }

    private function admin(): User
    {
        return User::create([
            'name' => 'Admin', 'email' => 'reviewer@example.test',
            'password' => 'password123', 'role' => 'admin',
        ]);
    }

    private function requestData(array $overrides = []): array
    {
        return array_replace([
            'type' => 'casual', 'start_date' => '2026-10-26', 'end_date' => '2026-10-27',
            'reason' => 'Private employee reason',
        ], $overrides);
    }

    public function test_submission_uses_current_employee_and_cannot_self_approve(): void
    {
        $first = $this->employee(1);
        $second = $this->employee(2);
        $this->actingAs($first)->post('/leaves', $this->requestData([
            'employee_id' => $second->employee->id, 'status' => 'approved', 'reviewed_by' => $second->id,
        ]))->assertRedirect('/leaves');
        $this->assertDatabaseHas('leave_requests', [
            'employee_id' => $first->employee->id, 'status' => 'pending', 'reviewed_by' => null,
        ]);
        $this->assertDatabaseMissing('leave_requests', ['employee_id' => $second->employee->id]);
    }

    public function test_employee_only_sees_own_leave_and_cannot_review_requests(): void
    {
        $first = $this->employee(3);
        $second = $this->employee(4);
        $mine = $first->employee->leaveRequests()->create($this->requestData(['status' => 'pending']));
        $second->employee->leaveRequests()->create($this->requestData([
            'reason' => 'Confidential second employee reason', 'status' => 'pending',
        ]));
        $this->actingAs($first)->get('/leaves')->assertOk()
            ->assertSee('Private employee reason')->assertDontSee('Confidential second employee reason');
        $this->patch('/leaves/'.$mine->id, ['status' => 'approved'])->assertForbidden();
        $this->actingAs($this->admin())->post('/leaves', $this->requestData())->assertForbidden();
    }

    public function test_invalid_dates_and_overlapping_requests_are_rejected(): void
    {
        $user = $this->employee(5);
        $this->actingAs($user)->post('/leaves', $this->requestData())->assertRedirect('/leaves');
        $this->post('/leaves', $this->requestData(['start_date' => '2026-10-27']))
            ->assertSessionHasErrors('start_date');
        $this->post('/leaves', $this->requestData(['start_date' => '2026-10-20']))
            ->assertSessionHasErrors('start_date');
        $this->post('/leaves', $this->requestData(['end_date' => '2026-10-25']))
            ->assertSessionHasErrors('end_date');
        $this->post('/leaves', $this->requestData(['end_date' => '2026-12-01']))
            ->assertSessionHasErrors('end_date');
        $this->post('/leaves', $this->requestData(['start_date' => '2026-10-24', 'end_date' => '2026-10-25']))
            ->assertSessionHasErrors('start_date');
        $this->assertDatabaseCount('leave_requests', 1);
    }

    public function test_admin_review_records_reviewer_and_cannot_overwrite_decision(): void
    {
        $user = $this->employee(6);
        $leave = $user->employee->leaveRequests()->create($this->requestData(['status' => 'pending']));
        $admin = $this->admin();
        $this->actingAs($admin)->patch('/leaves/'.$leave->id, ['status' => 'rejected'])
            ->assertSessionHasErrors('review_note');
        $this->patch('/leaves/'.$leave->id, ['status' => 'approved', 'review_note' => 'Coverage arranged'])
            ->assertRedirect('/leaves');
        $this->assertDatabaseHas('leave_requests', [
            'id' => $leave->id, 'status' => 'approved', 'reviewed_by' => $admin->id,
        ]);
        $this->assertNotNull($leave->fresh()->reviewed_at);
        $this->patch('/leaves/'.$leave->id, ['status' => 'rejected', 'review_note' => 'Second decision'])
            ->assertSessionHasErrors('status');
        $this->assertSame('approved', $leave->fresh()->status);
        $this->get('/leaves?status=approved')->assertOk()->assertSee('Coverage arranged');
    }

    public function test_rejected_request_can_be_resubmitted_and_inactive_employee_cannot_apply(): void
    {
        $user = $this->employee(7);
        $leave = $user->employee->leaveRequests()->create($this->requestData(['status' => 'pending']));
        $this->actingAs($this->admin())->patch('/leaves/'.$leave->id, [
            'status' => 'rejected', 'review_note' => 'Please choose another date',
        ])->assertRedirect('/leaves');
        $this->actingAs($user)->post('/leaves', $this->requestData())->assertRedirect('/leaves');
        $this->assertDatabaseCount('leave_requests', 2);
        $user->employee->update(['active' => false]);
        $this->post('/leaves', $this->requestData(['start_date' => '2026-11-02', 'end_date' => '2026-11-02']))
            ->assertForbidden();
    }

    public function test_approved_leave_is_reported_separately_and_attendance_takes_precedence(): void
    {
        $user = $this->employee(8);
        $employee = $user->employee;
        $employee->leaveRequests()->create($this->requestData([
            'start_date' => '2026-10-21', 'end_date' => '2026-10-21', 'status' => 'approved',
        ]));
        $this->actingAs($user)->get('/my-attendance?month=2026-10')->assertOk()
            ->assertViewHas('days', fn ($days) => collect($days)->firstWhere('date', '2026-10-21')['label'] === 'Leave');
        $admin = $this->admin();
        $this->actingAs($admin)->get('/attendance?date=2026-10-21')->assertOk()->assertSee('Leave');
        $this->get('/reports?month=2026-10&employee_id='.$employee->id)->assertOk()
            ->assertViewHas('rows', fn ($rows) => count($rows) === 1 && $rows[0]['leave'] === 1);
        $csv = $this->get('/reports/export?month=2026-10&employee_id='.$employee->id)->assertOk()->streamedContent();
        $lines = preg_split('/\r?\n/', trim($csv));
        $this->assertSame('Leave', str_getcsv($lines[0])[7]);
        $this->assertSame('1', str_getcsv($lines[1])[7]);
        Attendance::create([
            'employee_id' => $employee->id, 'attendance_date' => '2026-10-21',
            'check_in_at' => '09:00:00', 'check_out_at' => '17:00:00', 'status' => 'present',
        ]);
        $this->actingAs($user)->get('/my-attendance?month=2026-10')->assertOk()
            ->assertViewHas('days', fn ($days) => collect($days)->firstWhere('date', '2026-10-21')['label'] === 'Present');
    }
}
