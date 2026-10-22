<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('departments', function (Blueprint $t) {
            $t->id(); $t->string('name')->unique(); $t->timestamps();
        });
        Schema::create('employees', function (Blueprint $t) {
            $t->id(); $t->foreignId('user_id')->unique()->constrained()->restrictOnDelete();
            $t->foreignId('department_id')->constrained()->restrictOnDelete();
            $t->string('employee_code')->unique(); $t->string('name'); $t->string('email')->unique();
            $t->boolean('active')->default(true); $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('employees'); Schema::dropIfExists('departments'); }
};
