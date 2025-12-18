<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengirimanBerkas extends Model
{
    use HasFactory;

    // Sesuaikan nama tabel jika berbeda
    protected $table = 'pengiriman_berkas'; 

    // Kolom yang boleh diisi (sesuai dengan kolom yang akan Anda insert)
    protected $fillable = [
        'no_pengiriman', 
        'tanggal_pengiriman', 
        'jumlah_berkas', 
        'user_id',
        'status'
        // ... kolom lain yang relevan ...
    ];

    // Jika Anda mencatat detail berkas, Anda mungkin butuh relasi 'hasMany' ke tabel detail
}