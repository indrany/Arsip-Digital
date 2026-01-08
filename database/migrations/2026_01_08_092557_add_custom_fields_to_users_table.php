<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        // Karena file lama sudah dihapus, buat semua kolom di sini secara berurutan
        $table->string('nama_lengkap')->nullable()->after('name');
        
        // Tambahkan kembali kolom 'role' karena file yang lama sudah dihapus
        $table->enum('role', ['TIKIM', 'LANTASKIM', 'INTELDAKIM', 'INTELTUSKIM', 'ADMIN'])
              ->default('ADMIN')
              ->after('email');
              
        $table->boolean('is_active')->default(1)->after('role'); 
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        // Menghapus semua kolom yang ditambahkan jika rollback
        $table->dropColumn(['nama_lengkap', 'role', 'is_active']);
    });
}
};
