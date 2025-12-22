<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permohonan', function (Blueprint $table) {
            $table->id();
            $table->string('no_permohonan')->unique();
            $table->date('tanggal_permohonan');
            $table->date('tanggal_terbit')->nullable();
            $table->string('nama');
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('jenis_kelamin')->nullable();
            $table->string('no_telp')->nullable();
            $table->string('jenis_permohonan')->nullable();
            $table->string('jenis_paspor')->nullable();
            $table->string('tujuan_paspor')->nullable();
            $table->string('no_paspor')->nullable();
            $table->string('alur_terakhir')->nullable();
            $table->string('lokasi_arsip')->nullable();
            $table->string('status_berkas')->default('TERDAFTAR');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permohonan');
    }
};