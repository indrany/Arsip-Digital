<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::table('pengiriman_batch', function (Blueprint $table) {
        // Menambahkan kolom untuk mencatat asal unit dan nama pengirim
        $table->string('asal_unit')->nullable()->after('status');
        $table->string('petugas_kirim')->nullable()->after('asal_unit');
    });
}

public function down(): void
{
    Schema::table('pengiriman_batch', function (Blueprint $table) {
        $table->dropColumn(['asal_unit', 'petugas_kirim']);
    });
}
};
