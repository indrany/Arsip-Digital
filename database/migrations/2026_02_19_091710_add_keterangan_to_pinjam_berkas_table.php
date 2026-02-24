<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('pinjam_berkas', function (Blueprint $table) {
        // Kita tambah kolom keterangan saja karena yang lain sudah ada
        if (!Schema::hasColumn('pinjam_berkas', 'keterangan')) {
            $table->text('keterangan')->after('petugas_arsip')->nullable();
        }
    });
}

public function down(): void
{
    Schema::table('pinjam_berkas', function (Blueprint $table) {
        $table->dropColumn('keterangan');
    });
}
};
