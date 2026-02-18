<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pinjam_berkas', function (Blueprint $table) {
            $table->string('no_peminjaman')->after('permohonan_id')->nullable();
            $table->string('petugas_arsip')->after('nama_personil')->nullable();
        });
    }
    public function down(): void
    {
        Schema::table('pinjam_berkas', function (Blueprint $table) {
            $table->dropColumn(['no_peminjaman', 'petugas_arsip']);
        });
    }
};