<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
    Schema::table('pinjam_berkas', function (Blueprint $table) {
        $table->string('divisi_tujuan')->nullable()->after('nama_peminjam');
    });
    }
    public function down(): void
    {
        Schema::table('pinjam_berkas', function (Blueprint $table) {
            //
        });
    }
};
