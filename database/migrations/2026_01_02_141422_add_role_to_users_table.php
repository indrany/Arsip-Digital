<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        // Tambahkan username setelah kolom name
        $table->string('username')->unique()->after('name'); 
        
        // Tambahkan role setelah kolom email
        $table->enum('role', ['admin', 'ukk', 'ulp', 'lantaskim'])->default('admin')->after('email');
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        // Hapus kedua kolom jika rollback
        $table->dropColumn(['username', 'role']);
    });
}
};
