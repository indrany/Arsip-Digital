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
        Schema::create('pengiriman_batch', function (Blueprint $table) {
            $table->id();
            $table->string('no_pengirim')->unique(); // Contoh: 0234
            $table->date('tgl_pengirim');
            $table->date('tgl_diterima')->nullable();
            $table->integer('jumlah_berkas')->default(0);
            $table->string('status')->default('Diajukan'); // Diajukan atau Disetujui
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengiriman_batch');
    }
};
