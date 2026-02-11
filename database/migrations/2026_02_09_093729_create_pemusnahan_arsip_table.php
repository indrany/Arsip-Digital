<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        Schema::create('pemusnahan_arsip', function (Blueprint $table) {
            $table->id();
            $table->string('no_berita_acara')->unique(); 
            $table->date('tgl_pemusnahan');             // Tanggal input/kejadian
            $table->date('filter_mulai');               // Range tanggal awal dokumen yang dipilih
            $table->date('filter_selesai');             // Range tanggal akhir dokumen yang dipilih
            $table->integer('jumlah_dokumen');          // Hasil kalkulasi dokumen
            $table->string('file_pdf');                 // Path file Berita Acara yang diupload
            
            // Status alur (TIKIM Mengajukan -> ADMIN Menyetujui)
            $table->enum('status', ['Diajukan', 'Disetujui'])->default('Diajukan');
            
            // Menyimpan ID permohonan yang dimusnahkan dalam format JSON/LongText
            // agar bisa dipanggil saat cetak detail BA
            $table->longText('daftar_id_permohonan'); 
            
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('pemusnahan_arsip');
    }
};