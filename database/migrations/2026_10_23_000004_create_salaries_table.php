<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('salaries', function (Blueprint $t) {
            $t->id();
            $t->foreignId('employee_id')->constrained()->restrictOnDelete();
            $t->date('effective_month');
            $t->decimal('amount',12,2);
            $t->string('note',255)->nullable();
            $t->foreignId('updated_by')->constrained('users')->restrictOnDelete();
            $t->timestamps();
            $t->unique(['employee_id','effective_month']);
        });
    }
    public function down(): void { Schema::dropIfExists('salaries'); }
};
