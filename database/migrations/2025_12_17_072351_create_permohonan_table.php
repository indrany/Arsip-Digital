<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Permohonan', function (Blueprint $table) {
            // no_permohonan sebagai Primary Key (String)
            $table->string('no_permohonan')->primary(); 
            $table->date('tanggal_permohonan');
            $table->string('nama');
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            
            // Kolom status untuk alur Scan HP dan Penerimaan
            // Default: SIAP_DITERIMA
            $table->string('status_berkas')->default('SIAP_DITERIMA'); 
            
            $table->timestamp('tanggal_diterima')->nullable();
            $table->timestamps(); // Opsional: untuk created_at & updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Permohonan');
    }
};