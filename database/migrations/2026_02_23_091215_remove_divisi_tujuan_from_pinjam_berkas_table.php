<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pinjam_berkas', function (Blueprint $table) {
            // Kita cek dulu kolomnya ada atau nggak, biar nggak error pas eksekusi
            if (Schema::hasColumn('pinjam_berkas', 'divisi_tujuan')) {
                $table->dropColumn('divisi_tujuan');
            }
        });
    }

    public function down()
    {
        Schema::table('pinjam_berkas', function (Blueprint $table) {
            // Ini untuk jaga-jaga kalau mau dibalikin (Rollback)
            $table->string('divisi_tujuan')->nullable();
        });
    }
};