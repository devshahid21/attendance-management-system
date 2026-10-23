<?php
namespace Tests\Feature;
use Tests\TestCase;
use App\Models\User;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Salary;
use Illuminate\Foundation\Testing\RefreshDatabase;
class SalaryAccessTest extends TestCase {
 use RefreshDatabase;
 public function test_salary_data_restricted_to_superadmin_and_salary_can_be_saved(): void {
  $super=User::create(['name'=>'Super','email'=>'super@example.test','password'=>'secret12345','role'=>'superadmin']);
  $admin=User::create(['name'=>'Admin','email'=>'admin@example.test','password'=>'secret12345','role'=>'admin']);
  $employeeUser=User::create(['name'=>'Employee','email'=>'e@example.test','password'=>'secret12345','role'=>'employee']);
  $department=Department::create(['name'=>'IT']);
  $employee=Employee::create(['user_id'=>$employeeUser->id,'department_id'=>$department->id,'employee_code'=>'EMP100','name'=>'Employee','email'=>'e@example.test','active'=>true]);
  $this->actingAs($admin)->get('/salaries')->assertForbidden();
  $this->actingAs($employeeUser)->get('/salaries')->assertForbidden();
  $this->actingAs($admin)->post('/salaries',['employee_id'=>$employee->id,'month'=>'2026-10','amount'=>85000])->assertForbidden();
  $this->actingAs($super)->get('/salaries')->assertOk();
  $this->actingAs($super)->get('/employees')->assertOk();
  $this->actingAs($super)->get('/')->assertOk()->assertSee('A clear view of your team.');
  $this->actingAs($super)->get('/leaves')->assertOk()->assertSee('Leave requests');
  $this->actingAs($super)->get('/account')->assertOk()->assertSee('Super Administrator');
  $this->actingAs($super)->post('/salaries',['employee_id'=>$employee->id,'month'=>'2026-10','amount'=>85000,'note'=>'Demo'])->assertRedirect();
  $this->assertDatabaseHas('salaries',['employee_id'=>$employee->id,'amount'=>85000]);
  $this->actingAs($super)->post('/salaries',['employee_id'=>$employee->id,'month'=>'2026-10','amount'=>90000])->assertRedirect();
  $this->assertDatabaseCount('salaries',1);
 }
 public function test_salary_inputs_are_validated_before_writing(): void {
  $super=User::create(['name'=>'Super','email'=>'validation@example.test','password'=>'secret12345','role'=>'superadmin']);
  $this->actingAs($super)->get('/salaries?month=invalid')->assertSessionHasErrors('month');
  $this->post('/salaries',['employee_id'=>99999,'month'=>'2026-13','amount'=>-1])->assertSessionHasErrors(['employee_id','month','amount']);
  $this->assertDatabaseCount('salaries',0);
 }
}
