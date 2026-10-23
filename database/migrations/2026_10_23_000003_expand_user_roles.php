<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
 public function up(): void {
   if(DB::getDriverName()==='mysql'){
     DB::statement("ALTER TABLE users MODIFY role ENUM('superadmin','admin','employee') NOT NULL DEFAULT 'employee'");
   } else {
     // SQLite accepts original enum as varchar; existing CHECK constraints differ by engine.
   }
 }
 public function down(): void {
   if(DB::getDriverName()==='mysql') {
     if(DB::table('users')->where('role','superadmin')->exists()) throw new RuntimeException('Reassign superadmins before rollback.');
     DB::statement("ALTER TABLE users MODIFY role ENUM('admin','employee') NOT NULL DEFAULT 'employee'");
   }
 }
};
