<?php
namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin=User::firstOrCreate(['email'=>'admin@example.test'],[
            'name'=>'Demo Administrator','password'=>'DemoAdmin123!','role'=>'admin'
        ]);
        $engineering=Department::firstOrCreate(['name'=>'Engineering']);
        $sales=Department::firstOrCreate(['name'=>'Sales']);

        foreach([
            ['EMP001','Ayesha Khan','ayesha@example.test',$engineering->id],
            ['EMP002','Bilal Ahmed','bilal@example.test',$sales->id],
        ] as [$code,$name,$email,$dept]){
            $user=User::firstOrCreate(['email'=>$email],[
                'name'=>$name,'password'=>'DemoEmployee123!','role'=>'employee'
            ]);
            Employee::firstOrCreate(['employee_code'=>$code],[
                'user_id'=>$user->id,'department_id'=>$dept,'name'=>$name,'email'=>$email,'active'=>true
            ]);
        }
    }
}
