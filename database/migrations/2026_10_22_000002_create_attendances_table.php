<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('attendances', function (Blueprint $t) {
            $t->id(); $t->foreignId('employee_id')->constrained()->restrictOnDelete();
            $t->date('attendance_date'); $t->time('check_in_at')->nullable();
            $t->time('check_out_at')->nullable(); $t->enum('status',['present','late'])->default('present');
            $t->timestamps(); $t->unique(['employee_id','attendance_date']); $t->index('attendance_date');
        });
        Schema::create('attendance_corrections', function (Blueprint $t) {
            $t->id(); $t->foreignId('attendance_id')->constrained()->cascadeOnDelete();
            $t->foreignId('admin_id')->constrained('users')->restrictOnDelete();
            $t->text('reason'); $t->json('before'); $t->json('after'); $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('attendance_corrections'); Schema::dropIfExists('attendances'); }
};
