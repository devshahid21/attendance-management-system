<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seed_is_repeatable_and_preserves_existing_records(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-22 14:00:00', 'Asia/Karachi'));
        try {
            $this->seed(DatabaseSeeder::class);
            $this->assertDatabaseCount('users', 10);
            $this->assertDatabaseCount('departments', 4);
            $this->assertDatabaseCount('employees', 8);
            $this->assertDatabaseCount('leave_requests', 3);
            $this->assertGreaterThan(80, Attendance::count());
            $counts = [Attendance::count(), LeaveRequest::count(), User::count()];
            $record = Attendance::firstOrFail();
            $record->update(['check_out_at' => '18:00:00']);
            $this->seed(DatabaseSeeder::class);
            $this->assertSame($counts, [Attendance::count(), LeaveRequest::count(), User::count()]);
            $this->assertSame('18:00:00', $record->fresh()->check_out_at);
            $leave = LeaveRequest::approved()->firstOrFail();
            $this->assertDatabaseMissing('attendances', [
                'employee_id' => $leave->employee_id, 'attendance_date' => $leave->start_date->toDateString(),
            ]);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_synthetic_seed_refuses_to_run_in_production(): void
    {
        $before = $this->app['env'];
        $this->app['env'] = 'production';
        try {
            (new DatabaseSeeder)->run();
            $this->fail('Production demo seeding should be refused.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('local/testing', $exception->getMessage());
        } finally {
            $this->app['env'] = $before;
        }
        $this->assertDatabaseCount('users', 0);
    }
}
