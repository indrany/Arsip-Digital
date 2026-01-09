<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    // Tabel Master Rak
    Schema::create('rak_loker', function (Blueprint $table) {
        $table->id();
        $table->string('no_lemari');
        $table->string('kode_rak');     // Contoh: 5a
        $table->integer('kapasitas');   // Contoh: 100
        $table->integer('terisi')->default(0); 
        $table->enum('status', ['Tersedia', 'Penuh'])->default('Tersedia');
        $table->timestamps();
    });

    // Tambah kolom ke tabel permohonan agar bisa menyimpan ID rak
    Schema::table('permohonan', function (Blueprint $table) {
        $table->unsignedBigInteger('rak_id')->nullable()->after('status_berkas');
        $table->integer('no_urut_di_rak')->nullable()->after('rak_id');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rak_loker');
    }
};
