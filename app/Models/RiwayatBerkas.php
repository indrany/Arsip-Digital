<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatBerkas extends Model
{
    use HasFactory;
    
    // Opsional: Jika nama tabel Anda bukan 'riwayat_berkas' (plural)
    // protected $table = 'nama_tabel_anda'; 

    // Opsional: Jika Anda menggunakan mass assignment
    // protected $fillable = ['no_pengirim', 'tanggal_pengirim', 'tanggal_diterima', 'jumlah_berkas']; 
}